<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * LogController
 *
 * Handles log search, filtering, and retrieval.
 * Supports filtering by level, category, source, user, and date range.
 */
class LogController extends Controller
{
    /**
     * Display a listing of logs with filters.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'level' => ['nullable', 'string'],
            'category' => ['nullable', 'string'],
            'source' => ['nullable', 'string'],
            'user_id' => ['nullable', 'integer'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = Log::query();

        // Filter by level
        if ($request->has('level')) {
            $query->byLevel($request->input('level'));
        }

        // Filter by category
        if ($request->has('category')) {
            $query->byCategory($request->input('category'));
        }

        // Filter by source
        if ($request->has('source')) {
            $query->bySource($request->input('source'));
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->byUser((int) $request->input('user_id'));
        }

        // Filter by date range
        if ($request->has('from')) {
            $to = $request->input('to');
            $query->dateRange($request->input('from'), $to);
        }

        // Search by message
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('message', 'like', "%{$search}%");
        }

        // Pagination
        $perPage = (int) ($request->input('per_page', 50));
        $logs = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $logs->items(),
            'pagination' => [
                'total' => $logs->total(),
                'per_page' => $logs->perPage(),
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
            ],
        ]);
    }

    /**
     * Display the specified log.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
       {
             $log = Log::findOrFail((int) $id);

        return response()->json([
            'success' => true,
            'data' => $log,
        ]);
    }

    /**
     * Get error-level logs.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function errors(Request $request): JsonResponse
    {
        $perPage = (int) ($request->input('per_page', 50));
        $logs = Log::errors()->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $logs->items(),
            'pagination' => [
                'total' => $logs->total(),
                'per_page' => $logs->perPage(),
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
            ],
        ]);
    }

    /**
     * Get available log levels.
     *
     * @return JsonResponse
     */
    public function levels(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                ['value' => Log::LEVEL_DEBUG, 'label' => 'Debug'],
                ['value' => Log::LEVEL_INFO, 'label' => 'Info'],
                ['value' => Log::LEVEL_NOTICE, 'label' => 'Notice'],
                ['value' => Log::LEVEL_WARNING, 'label' => 'Warning'],
                ['value' => Log::LEVEL_ERROR, 'label' => 'Error'],
                ['value' => Log::LEVEL_CRITICAL, 'label' => 'Critical'],
                ['value' => Log::LEVEL_ALERT, 'label' => 'Alert'],
                ['value' => Log::LEVEL_EMERGENCY, 'label' => 'Emergency'],
            ],
        ]);
    }

    /**
     * Get available log categories.
     *
     * @return JsonResponse
     */
    public function categories(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                ['value' => Log::CATEGORY_AUTH, 'label' => 'Authentication'],
                ['value' => Log::CATEGORY_SECURITY, 'label' => 'Security'],
                ['value' => Log::CATEGORY_API, 'label' => 'API'],
                ['value' => Log::CATEGORY_WORKFLOW, 'label' => 'Workflow'],
                ['value' => Log::CATEGORY_AGENT, 'label' => 'Agent'],
                ['value' => Log::CATEGORY_AI, 'label' => 'AI'],
                ['value' => Log::CATEGORY_SYSTEM, 'label' => 'System'],
                ['value' => Log::CATEGORY_DATABASE, 'label' => 'Database'],
                ['value' => Log::CATEGORY_CACHE, 'label' => 'Cache'],
                ['value' => Log::CATEGORY_QUEUE, 'label' => 'Queue'],
            ],
        ]);
    }

    /**
     * Get log statistics.
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total' => Log::count(),
            'by_level' => Log::selectRaw('level, count(*) as count')
                ->groupBy('level')
                ->pluck('count', 'level')
                ->toArray(),
            'by_category' => Log::selectRaw('category, count(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray(),
            'today' => Log::whereDate('created_at', today())->count(),
            'errors_today' => Log::whereDate('created_at', today())
                ->errors()
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
       * Remove the specified log.
       *
       * @param int $id
       * @return JsonResponse
       */
    public function destroy($id): JsonResponse
       {
          $log = Log::findOrFail((int) $id);
       $log->delete();

       return response()->json([
             'success' => true,
             'message' => 'Log deleted successfully.',
       ]);
    }

    /**
       * Clear all logs.
       *
       * @return JsonResponse
       */
    public function clear(): JsonResponse
    {
       Log::truncate();

       return response()->json([
             'success' => true,
             'message' => 'All logs cleared successfully.',
       ]);
    }
 }

