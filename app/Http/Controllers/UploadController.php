<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadBase64ImageRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/upload",
     *     summary="Upload a file",
     *     tags={"Files"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="File to upload (max 10MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="File uploaded successfully"),
     *             @OA\Property(
     *                 property="file",
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="abc123.pdf"),
     *                 @OA\Property(property="size", type="integer", example=1024),
     *                 @OA\Property(property="type", type="string", example="application/pdf")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The file field is required.")
     *         )
     *     )
     * )
     */
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
                        'type' => $file->getClientMimeType(),
                        'path' => $path
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

    /**
     * @OA\Get(
     *     path="/api/files",
     *     summary="List all uploaded files",
     *     tags={"Files"},
     *     @OA\Response(
     *         response=200,
     *         description="List of files",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="name", type="string", example="document.pdf"),
     *                 @OA\Property(property="size", type="integer", example=1024),
     *                 @OA\Property(property="size_text", type="string", example="1.00 KB"),
     *                 @OA\Property(property="url", type="string", example="http://example.com/storage/files/document.pdf"),
     *                 @OA\Property(property="created_at", type="integer", example=1623456789),
     *                 @OA\Property(property="extension", type="string", example="pdf")
     *             )
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/files/{filename}",
     *     summary="View/download a file",
     *     tags={"Files"},
     *     @OA\Parameter(
     *         name="filename",
     *         in="path",
     *         required=true,
     *         description="Name of the file to view/download",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File content",
     *         @OA\MediaType(
     *             mediaType="application/octet-stream",
     *             @OA\Schema(type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="File not found"
     *     )
     * )
     */
    public function viewFile($filename)
    {
        $path = 'uploads/' . $filename;
        if (Storage::disk('local')->exists($path)) {
            $fullPath = Storage::disk('local')->path($path);
            return response()->file($fullPath);
        }
        abort(404);
    }

    /**
     * @OA\Delete(
     *     path="/api/files/{filename}",
     *     summary="Delete a file",
     *     tags={"Files"},
     *     @OA\Parameter(
     *         name="filename",
     *         in="path",
     *         required=true,
     *         description="Name of the file to delete",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="File deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="File not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="File not found")
     *         )
     *     )
     * )
     */
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

    public function uploadBase64(UploadBase64ImageRequest $request)
    {
        $base64Image = $request->input('image');

        // Lấy type (jpg, png,...)
        preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches);
        $type = strtolower($matches[1]);

        $imageData = base64_decode(substr($base64Image, strpos($base64Image, ',') + 1));
        if ($imageData === false) {
            return response()->json(['error' => 'Không thể decode ảnh'], 400);
        }

        $fileName = Str::uuid() . '.' . $type;
        $filePath = 'images/' . $fileName;

        Storage::disk('public')->put($filePath, $imageData);

        return response()->json([
            'message' => 'Upload thành công',
            'path' => 'storage/' . $filePath,
        ]);
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
