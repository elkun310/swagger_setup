<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Term;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\Validator;

class TermController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/terms",
     *     summary="Get list of terms with pagination",
     *     tags={"Terms"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The page number",
     *         required=false,
     *
     *         @OA\Schema(
     *             type="integer",
     *             default=1
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *
     *         @OA\Schema(
     *             type="integer",
     *             default=20
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="message", type="string", example="OK", description="Response message"),
     *             @OA\Property(property="total", type="integer", description="Total number of terms"),
     *             @OA\Property(
     *                 property="terms",
     *                 type="array",
     *                 description="List of terms",
     *
     *                 @OA\Items(
     *                     type="object",
     *
     *                     @OA\Property(property="version", type="string", description="Version of the term"),
     *                     @OA\Property(property="title", type="string", description="Title of the term"),
     *                     @OA\Property(property="content", type="string", description="Content of the term"),
     *                     @OA\Property(property="apply_date", type="string", format="date", description="Apply date of the term"),
     *                 )
     *             ),
     *             @OA\Property(property="current_page", type="integer", description="Current page"),
     *             @OA\Property(property="last_page", type="integer", description="Last page"),
     *             @OA\Property(property="per_page", type="integer", description="Number of items per page")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        // Lấy tham số 'page' và 'limit' từ request (mặc định 'page' = 1 và 'limit' = 20 nếu không có giá trị)
        $page = $request->get('page', 1); // Trang mặc định là 1
        $limit = $request->get('limit', 20); // Giới hạn mặc định là 20 bản ghi mỗi trang

        // Phân trang theo tham số 'limit', sử dụng 'page' và 'limit' từ request
        $terms = Term::query()->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'message' => 'OK',
            'terms' => $terms->items(), // Danh sách bản ghi của trang hiện tại
            'total' => $terms->total(), // Tổng số bản ghi
            'current_page' => $terms->currentPage(), // Trang hiện tại
            'last_page' => $terms->lastPage(), // Trang cuối cùng
            'per_page' => $terms->perPage(), // Số lượng bản ghi mỗi trang
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/terms/{id}",
     *     summary="Get a single term by ID",
     *     tags={"Terms"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the term",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *
     *         @OA\JsonContent(
     *             type="array",
     *             description="List of terms matching the ID",
     *
     *             @OA\Items(
     *                 type="object",
     *
     *                 @OA\Property(property="version", type="string", description="Version of the term"),
     *                 @OA\Property(property="title", type="string", description="Title of the term"),
     *                 @OA\Property(property="content", type="string", description="Content of the term"),
     *                 @OA\Property(property="apply_date", type="string", format="date", description="Apply date of the term"),
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Term not found",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="message", type="string", example="Term not found")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $term = Term::where('id', $id)->first();

        if (! $term) {
            return response()->json(['message' => 'Term not found'], 404);
        }

        return response()->json($term);
    }

    /**
     * @OA\Post(
     *     path="/api/terms",
     *     summary="Create a new term",
     *     description="Creates a new term with the provided details.",
     *     operationId="storeTerm",
     *     tags={"Terms"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"version", "title", "apply_date"},
     *
     *             @OA\Property(property="version", type="string", example="1.0.0"),
     *             @OA\Property(property="title", type="string", example="Terms Title"),
     *             @OA\Property(property="apply_date", type="string", format="date", example="2025-03-20"),
     *             @OA\Property(property="content", type="string", nullable=true, example="Optional content")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Term created successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="version", type="string", example="1.0.0"),
     *             @OA\Property(property="title", type="string", example="Terms Title"),
     *             @OA\Property(property="apply_date", type="string", format="date", example="2025-03-20"),
     *             @OA\Property(property="content", type="string", nullable=true, example="Optional content"),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={
     *                 "version": {"The version field is required."},
     *                 "title": {"The title field is required."}
     *             })
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'version' => 'required|string',
                'title' => 'required|string',
                'apply_date' => 'required|date',
                'content' => [
                    'sometimes',
                    function ($attribute, $value, $fail) use ($request) {
                        $applyDate = $request->input('apply_date');
                        if ($applyDate && strtotime($applyDate) > strtotime('2025-01-01') && empty($value)) {
                            $fail('Trường nội dung (content) là bắt buộc khi ngày áp dụng sau 01/01/2025.');
                        }
                    },
                ],
            ]);

            $validated = $validator->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json($e->errors(), 422);
        }
        $term = Term::create($validated);

        return response()->json($term, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/terms/{id}",
     *     summary="Update an existing term",
     *     description="Updates the details of an existing term by its ID.",
     *     operationId="updateTerm",
     *     tags={"Terms"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the term to update",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"version", "title", "apply_date"},
     *
     *             @OA\Property(property="version", type="string", example="2.0.0"),
     *             @OA\Property(property="title", type="string", example="Updated Terms Title"),
     *             @OA\Property(property="apply_date", type="string", format="date", example="2025-04-01"),
     *             @OA\Property(property="content", type="string", nullable=true, example="Updated optional content")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Term updated successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="version", type="string", example="2.0.0"),
     *             @OA\Property(property="title", type="string", example="Updated Terms Title"),
     *             @OA\Property(property="apply_date", type="string", format="date", example="2025-04-01"),
     *             @OA\Property(property="content", type="string", nullable=true, example="Updated optional content"),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Term not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Term not found")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={
     *                 "version": {"The version field is required."},
     *                 "title": {"The title field is required."}
     *             })
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $term = Term::query()->find($id);
        if (! $term) {
            return response()->json(['message' => 'Term not found'], 404);
        }

        try {
            $validated = $request->validate([
                'version' => 'required|string',
                'title' => 'required|string',
                'apply_date' => 'required|date',
                'content' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json($e->errors(), 422);
        }

        $term->update($validated);

        return response()->json($term, 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/terms/{id}",
     *     summary="Delete a term",
     *     description="Deletes a term by its ID.",
     *     operationId="deleteTerm",
     *     tags={"Terms"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the term to delete",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=204,
     *         description="Term deleted successfully",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Term not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Term not found")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        $term = Term::query()->find($id);
        if (! $term) {
            return response()->json(['message' => 'Term not found'], 404);
        }
        $term->delete();

        return response()->json(null, 204);
    }
}
