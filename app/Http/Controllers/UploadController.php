<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'file' => 'required|file|max:10240', // Max 10MB, accept any file type
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first('file')
                ], 422);
            }

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = $file->hashName();
                
                // Store file in local storage
                $path = $file->storeAs('uploads', $fileName, 'local');
                
                return response()->json([
                    'success' => true,
                    'message' => 'File uploaded successfully',
                    'file' => [
                        'name' => $fileName,
                        'size' => $file->getSize(),
                        'type' => $file->getClientMimeType()
                    ]
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No file uploaded'
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading file: ' . $e->getMessage()
            ], 500);
        }
    }

    public function listFiles()
    {
        $files = Storage::disk('local')->files('uploads');
        
        $filesData = collect($files)->map(function($file) {
            $size = Storage::disk('local')->size($file);
            $sizeText = $size > 1024 * 1024 
                ? number_format($size / (1024 * 1024), 2) . ' MB'
                : number_format($size / 1024, 2) . ' KB';

            // Get the current request's host and scheme
            $baseUrl = URL::to('/');
            
            return [
                'name' => basename($file),
                'size' => $size,
                'size_text' => $sizeText,
                'url' => $baseUrl . '/storage/' . $file,
                'created_at' => Storage::disk('local')->lastModified($file),
                'extension' => pathinfo($file, PATHINFO_EXTENSION)
            ];
        })->sortByDesc('created_at');

        return view('files.list', compact('filesData'));
    }

    public function viewFile($filename)
    {
        $path = 'uploads/' . $filename;
        if (Storage::disk('local')->exists($path)) {
            $fullPath = Storage::disk('local')->path($path);
            return response()->file($fullPath);
        }
        abort(404);
    }

    public function deleteFile($filename)
    {
        try {
            $path = 'uploads/' . $filename;
            if (Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
                return response()->json([
                    'success' => true,
                    'message' => 'File deleted successfully'
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => 'File not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting file: ' . $e->getMessage()
            ], 500);
        }
    }
}
