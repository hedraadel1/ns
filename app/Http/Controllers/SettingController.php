<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SettingCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * SettingController
 *
 * Handles CRUD operations for application settings.
 * Supports grouped retrieval, type filtering, and cache invalidation.
 */
class SettingController extends Controller
{
    /**
     * The cache service instance.
     *
     * @var SettingCacheService
     */
    protected SettingCacheService $cacheService;

    /**
     * Create a new controller instance.
     *
     * @param SettingCacheService $cacheService
     * @return void
     */
    public function __construct(SettingCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Display a listing of settings.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Setting::query();

        if ($request->has('group')) {
            $query->byGroup($request->input('group'));
        }

        if ($request->has('type')) {
            $query->byType($request->input('type'));
        }

        if ($request->has('is_public')) {
            $isPublic = filter_var($request->input('is_public'), FILTER_VALIDATE_BOOLEAN);
            if ($isPublic) {
                $query->public();
            } else {
                $query->private();
            }
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('key', 'like', "%{$search}%");
        }

        $settings = $query->orderBy('group')->orderBy('key')->get();

        return response()->json([
            'success' => true,
            'data' => $settings,
            'count' => $settings->count(),
        ]);
    }

    /**
     * Store a newly created setting.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'key' => ['required', 'string', 'max:255', 'unique:settings,key'],
            'value' => ['required'],
            'type' => ['required', 'string', 'in:string,integer,boolean,json,text'],
            'group' => ['required', 'string', 'max:255'],
            'is_public' => ['boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $setting = Setting::create($validator->validated());
        $this->cacheService->forget($setting->key);

        return response()->json([
            'success' => true,
            'data' => $setting,
            'message' => 'Setting created successfully.',
        ], 201);
    }

    /**
     * Display the specified setting.
     *
     * @param string $key
     * @return JsonResponse
     */
    public function show(string $key): JsonResponse
    {
        $setting = Setting::where('key', $key)->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $setting,
        ]);
    }

    /**
     * Update the specified setting.
     *
     * @param Request $request
     * @param string $key
     * @return JsonResponse
     */
    public function update(Request $request, string $key): JsonResponse
    {
        $setting = Setting::where('key', $key)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'value' => ['required'],
            'type' => ['sometimes', 'string', 'in:string,integer,boolean,json,text'],
            'group' => ['sometimes', 'string', 'max:255'],
            'is_public' => ['boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $setting->update($validator->validated());
        $this->cacheService->forget($key);

        return response()->json([
            'success' => true,
            'data' => $setting,
            'message' => 'Setting updated successfully.',
        ]);
    }

    /**
     * Remove the specified setting.
     *
     * @param string $key
     * @return JsonResponse
     */
    public function destroy(string $key): JsonResponse
    {
        $setting = Setting::where('key', $key)->firstOrFail();
        $setting->delete();
        $this->cacheService->forget($key);

        return response()->json([
            'success' => true,
            'message' => 'Setting deleted successfully.',
        ]);
    }

    /**
     * Get all settings grouped by their group.
     *
     * @return JsonResponse
     */
    public function grouped(): JsonResponse
    {
        $settings = Setting::orderBy('group')->orderBy('key')->get();
        $grouped = $settings->groupBy('group');

        return response()->json([
            'success' => true,
            'data' => $grouped,
        ]);
    }

    /**
     * Get all public settings.
     *
     * @return JsonResponse
     */
    public function publicSettings(): JsonResponse
    {
        $settings = Setting::public()->orderBy('key')->get();

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Bulk update settings.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'settings' => ['required', 'array'],
            'settings.*.key' => ['required', 'string'],
            'settings.*.value' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $updated = [];
        foreach ($request->input('settings') as $item) {
            $setting = Setting::where('key', $item['key'])->first();
            if ($setting) {
                $setting->update(['value' => $item['value']]);
                $this->cacheService->forget($setting->key);
                $updated[] = $setting;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $updated,
            'message' => count($updated) . ' settings updated.',
        ]);
    }
}
