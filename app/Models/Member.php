<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


use Illuminate\Support\Facades\DB;

class Member extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'member';

    const CREATED_AT = 'regist_time';
    const UPDATED_AT = 'update_time';

    //  更新可能カラムのホワイトリスト
    //protected $fillable = [
    //];
    protected $guarded = [
        'id',
    ];

    protected $hidden = [
    ];

    protected $casts = [
    ];
	/**
	* 端末
	*
	* @var string
	*/
	const DEVICE_NONE    = 0;
	const DEVICE_IOS     = 1;
	const DEVICE_ANDROID = 2;

	public $devices = array(
		self::DEVICE_NONE    => '不明',
		self::DEVICE_IOS     => 'iOS',
		self::DEVICE_ANDROID => 'Android',
	);

	/**
	* 会員区分
	*
	* @var string
	*/
	const DIVISION_KANTAN = 1;
	const DIVISION_BNID   = 2;

	public static $divisions = array(
		self::DIVISION_KANTAN => '仮会員',
		self::DIVISION_BNID   => '本会員',
	);

	/**
	* 会員ステータス
	*
	* @var string
	*/
	const RANK_01 = 1;
	const RANK_02 = 2;
	const RANK_03 = 3;
	const RANK_04 = 4;
	const RANK_05 = 5;

	public static $ranks = array(
		self::RANK_01 => 'レギュラー',
		self::RANK_02 => 'ブロンズ',
		self::RANK_03 => 'シルバー',
		self::RANK_04 => 'ゴールド',
		self::RANK_05 => 'プラチナ',
	);

	public static $rankImages = array(
		self::RANK_01 => 'status_01.png',
		self::RANK_02 => 'status_02.png',
		self::RANK_03 => 'status_03.png',
		self::RANK_04 => 'status_04.png',
		self::RANK_05 => 'status_05.png',
	);

    public static $rankBorderPoints = array(
        self::RANK_01 => 0,
        self::RANK_02 => 4000,
        self::RANK_03 => 10000,
        self::RANK_04 => 20000,
        self::RANK_05 => 50000,
    );

    public function getPrefNameAttribute()
    {
        $prefName = '';
        if (isset($this->pref) && !empty($this->pref)) {
            $prefs = Pref::getList();
            $prefName = $prefs[$this->pref];
        }
        return $prefName;
    }

    public function getMunicipalityNameAttribute()
    {
        $municipalityName = '';
        if (isset($this->municipality) && !empty($this->municipality)) {
            $trMunicipality = Municipality::find($this->municipality);
            if (!empty($trMunicipality)) {
                $municipalityName = $trMunicipality->government_name;
            }
        }
        return $municipalityName;
    }

	public static function getExpirePoint($memberId, $targetDate = null)
	{
		//$db = $this->getAdapter();

		if (empty($targetDate)) {
			$targetDate = date("Y-m-01");
		}
		else {
			$targetDate = date("Y-m-01", strtotime($targetDate));
		}
		//$targetStartDate = new Zend_Date($targetDate, Zend_Date::ISO_8601);
		//$targetStartDate->subYear(1);
		//$targetEndDate = clone $targetStartDate;
		//$targetEndDate->addMonth(1);
		//$year = date("Y",strtotime($targetDate));
		//$month = date("m",strtotime($targetDate));

		//$targetStartDate = date("Y-m-d",mktime(0, 0, 0, $month, 0, $year-1));
		//$targetEndDate = date("Y-m-d",mktime(0, 0, 0, $month+1, 0, $year-1));

		$targetStartDate = date("Y-m-d",strtotime("-1 year", strtotime($targetDate)));
		$targetEndDate = date("Y-m-d",strtotime("+1 month", strtotime($targetStartDate)));

		//$select = $db->select();
		//$select->from('point_history', array('SUM(usable_point)'))
		//       ->where('member_id = ?', $memberId)
		//       ->where('regist_time >= ?', $targetStartDate->toString("yyyy-MM-dd"))
		//       ->where('regist_time < ?', $targetEndDate->toString("yyyy-MM-dd"))
		//       ;

		$point = 0;
		$select = DB::table('point_history')
			->selectRaw('SUM(usable_point) AS sum')
			->where('member_id',$memberId)
			//->where('regist_time','>=',$targetStartDate)
			->where('regist_time','<',$targetEndDate)
			->first();
		if(!is_null($select)){
			$point = $select->sum;
		}

		//return (int)$db->fetchOne($select);
		return (int)$point;
	}

    public function titles(): BelongsToMany
    {
        return $this->belongsToMany(Title::class);
    }

    public static function boot()
    {
        parent::boot();
        self::updating(function ($data) {
            // ポイント操作
            if ($data->getOriginal('usable_point') > $data->usable_point) {
                $diffPoint = $data->getOriginal('usable_point') - $data->usable_point;
/*
                $db = $this->getTable()->getAdapter();
                $tbPointHistory = new Db_Table_PointHistory(array('db' => $db));
                $where = array();
                $where[] = $db->quoteInto('member_id = ?', $this->id);
                $where[] = 'usable_point > 0';
                $trsPointHitory = $tbPointHistory->fetchAll($where, array("regist_time ASC"));
*/
                $trsPointHitory = PointHistory::where('member_id', $data->id)
                    ->where('usable_point', '>=', 0)
                    ->orderBy('point_division', 'asc')
                    ->orderBy('regist_time', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();

                foreach ($trsPointHitory as $trPointHitory) {
                    if ($trPointHitory->usable_point >= $diffPoint) {
                        $trPointHitory->usable_point -= $diffPoint;
                        $diffPoint = 0;
                    }
                    else {
                        $diffPoint -= $trPointHitory->usable_point;
                        $trPointHitory->usable_point = 0;
                    }
                    $trPointHitory->save();

                    if ($diffPoint <= 0) {
                        break;
                    }
                }
            }
        });
    }
}
