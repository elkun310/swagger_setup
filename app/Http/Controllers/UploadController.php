<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|max:2048',
            ]);
            // Lưu file lên AWS S3
            // $path = $request->file('file')->store('uploads', 's3');

            // // Lấy URL của file đã upload
            // $finalUrl = Storage::disk('s3')->temporaryUrl(
            //     $path, now()->addMinutes(5) // Đặt thời gian hết hạn cho signed URL
            // );
            // dd($finalUrl);
            $path = $request->file('file')->store('uploads', 'public');
            $url = Storage::url($path);
            // dd(asset($url));

            return response()->json(['url' => asset($url)]);
        } catch (Exception $e) {
            report($e->getMessage());
            return response()->json(['error' => 'Upload failed!'], 500);
        }
    }
}
