<?php

namespace App\Models;

use App\Scopes\WithoutDeleteScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    use HasFactory;

    protected $table = 'terms';

    const CREATED_AT = 'regist_time';
    const UPDATED_AT = 'update_time';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    //  更新可能カラムのホワイトリスト
    protected $fillable = [
        'version',
        'title',
        'content',
        'apply_date',
        'delete_flg',
        'delete_time',
    ];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = [
        'id',
        'version',
        'title',
        'content',
        'apply_date',
    ];

    /**
     * Scope a query to filter by WithoutDeleted.
     */
    public function scopeWithoutDeleted($query, $value)
    {
        // Add your query logic here
        return $query;
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new WithoutDeleteScope());
    }
}
