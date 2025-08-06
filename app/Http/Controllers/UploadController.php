<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|max:2048',
            ]);

            // Lưu file lên AWS S3
            $path = $request->file('file')->store('uploads', 's3');

            // Lấy URL của file đã upload
            $finalUrl = Storage::disk('s3')->temporaryUrl(
                $path,
                now()->addMinutes(5) // Đặt thời gian hết hạn cho signed URL
            );
            return response()->json(['url' => $finalUrl]);
        } catch (Exception $e) {
            report($e->getMessage());
            return response()->json(['error' => 'Upload failed!'], 500);
        }
    }

    public function storeFilePond(Request $request)
    {
        if ($request->hasFile('file')) {
            // Nếu là upload nhiều file, Laravel sẽ nhận dưới dạng mảng
            $files = $request->file('file');

            $paths = [];
            foreach ((array) $files as $file) {
                $paths[] = [
                    'path' => $file->store('uploads', 'public'),
                    'name' => $file->getClientOriginalName()
                ];
            }

            return response()->json([
                'files' => $paths
            ]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }
}
