<?php

namespace App\Http\Controllers;

use App\Models\ManageTasks;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\ManageTasksRequest;
use App\Http\Resources\ManageTasksResource;

class ManageTasksController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Fetch paginated tasks (10 per page)
            $tasks = ManageTasks::orderByDesc('created_at')->paginate(10);
            return response()->json([
                'status' => true,
                'message' => __('Tasks retrieved successfully'),
                'data' => ManageTasksResource::collection($tasks),
                'pagination' => [
                    'total' => $tasks->total(),
                    'per_page' => $tasks->perPage(),
                    'current_page' => $tasks->currentPage(),
                    'last_page' => $tasks->lastPage(),
                    'next_page_url' => $tasks->nextPageUrl(),
                    'prev_page_url' => $tasks->previousPageUrl(),
                ]
            ], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => __('Something went wrong. Please try again.'),
                'error' => $e->getMessage(),
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ManageTasksRequest $request)
    {
        try {
            $validated = $request->validated();
            DB::beginTransaction();
            if ($request->hasFile('document')) {
                $file = $request->file('document');
                $timestamp = now()->format('d-m-Y-His'); // Format: DD-MM-YYYY-His
                $originalExtension = $file->getClientOriginalExtension();
                $newFileName = 'document-' . $timestamp . '.' . $originalExtension;
                $filePath = $file->storeAs('uploads/documents', $newFileName, 'public');
                $validated['document'] = $filePath; // Save path in DB
            }
            $created = ManageTasks::create($validated);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => __('Created successfully'),
                'data' => new ManageTasksResource($created),
            ], JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ManageTasks Store Error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e
            ]);
            return response()->json([
                'status' => false,
                'message' => __('Something went wrong. Please try again.'),
                'error' => $e->getMessage(),
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $show = ManageTasks::findOrFail($id);
            return response()->json([
                'status' => true,
                'message' => __('Task retrieved successfully'),
                'data' => new ManageTasksResource($show),
            ], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => __('Task not found'),
                'error' => $e->getMessage(),
            ], JsonResponse::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ManageTasksRequest $request, string $id)
    {
        try {
            $task = ManageTasks::findOrFail($id);
            $validated = $request->validated();
            DB::beginTransaction();
            if ($request->hasFile('document')) {
                $file = $request->file('document');
                $timestamp = now()->format('d-m-Y-His');
                $originalExtension = $file->getClientOriginalExtension();
                $newFileName = 'document-' . $timestamp . '.' . $originalExtension;
                $filePath = $file->storeAs('uploads/documents', $newFileName, 'public');
                // Delete old file if exists (optional)
                if ($task->document) {
                    Storage::disk('public')->delete($task->document);
                }
                $validated['document'] = $filePath; // Save new file path
            }
            $task->update($validated);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => __('Updated successfully'),
                'data' => new ManageTasksResource($task),
            ], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ManageTasks Update Error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e
            ]);
            return response()->json([
                'status' => false,
                'message' => __('Something went wrong. Please try again.'),
                'error' => $e->getMessage(),
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $task = ManageTasks::findOrFail($id);
            DB::beginTransaction();
            if ($task->document) {
                Storage::disk('public')->delete($task->document);
            }
            $task->delete();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => __('Deleted successfully'),
            ], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ManageTasks Delete Error: ' . $e->getMessage(), [
                'task_id' => $id,
                'exception' => $e
            ]);
            return response()->json([
                'status' => false,
                'message' => __('Something went wrong. Please try again.'),
                'error' => $e->getMessage(),
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
