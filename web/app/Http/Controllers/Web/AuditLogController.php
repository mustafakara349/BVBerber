<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    /**
     * Display a listing of audit logs with tabbed filters, search, and metrics.
     */
    public function index(Request $request): View
    {
        // 1. High-level metric calculations (optimized single queries)
        $now = now();
        $metrics = [
            'total' => AuditLog::count(),
            'today' => AuditLog::where('created_at', '>=', $now->copy()->startOfDay())->count(),
            'errors' => AuditLog::where(function ($q) {
                $q->where('status', 'error')
                  ->orWhere('status', 'failed')
                  ->orWhere('model_type', 'exception')
                  ->orWhere('action', 'system.exception');
            })->where('created_at', '>=', $now->copy()->subHours(24))->count(),
            'security' => AuditLog::where('action', 'like', 'auth.%')
                ->where('created_at', '>=', $now->copy()->subDays(7))->count(),
        ];

        // 2. Query builder with eager loading to prevent N+1
        $query = AuditLog::with(['user.role'])->latest('id');

        // Tab filter (all, models, errors, security)
        $activeTab = $request->get('tab', 'all');
        $query->forTab($activeTab);

        // Free-text search
        if ($request->filled('search')) {
            $query->search($request->get('search'));
        }

        // User filter
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Date range filter: default to last 60 days unless custom date or all-time is requested
        $defaultDays = config('bv.audit_default_days', 60);
        $isAllTime = $request->boolean('all_time');
        $hasCustomDates = $request->filled('start_date') || $request->filled('end_date');
        $isDefaultRange = !$hasCustomDates && !$isAllTime;

        if ($hasCustomDates) {
            $query->dateRange($request->get('start_date'), $request->get('end_date'));
        } elseif (!$isAllTime) {
            $query->where('created_at', '>=', $now->copy()->subDays($defaultDays)->startOfDay());
        }

        // Standardized corporate pagination
        $logs = $query->paginate(20)->withQueryString();

        // Unique users list for dropdown filter
        $filterUsers = User::whereIn('id', function ($sub) {
            $sub->select('user_id')
                ->from('audit_logs')
                ->whereNotNull('user_id')
                ->distinct();
        })->select('id', 'first_name', 'last_name', 'email')->orderBy('first_name')->get();

        return view('audit_logs.index', compact(
            'logs',
            'metrics',
            'activeTab',
            'filterUsers',
            'defaultDays',
            'isDefaultRange',
            'isAllTime',
            'hasCustomDates'
        ));
    }

    /**
     * Return detailed JSON for the visual diff / exception inspection modal.
     */
    public function show(AuditLog $auditLog): JsonResponse
    {
        $auditLog->loadMissing('user.role');

        $parsedNew = $auditLog->parsed_new_values;
        $parsedOld = $auditLog->parsed_old_values;

        // Exception detection
        $isException = $auditLog->model_type === 'exception'
            || $auditLog->action === 'system.exception'
            || isset($parsedNew['data']['exception_class'])
            || isset($parsedNew['exception_class']);

        $exceptionDetails = null;
        if ($isException) {
            $payload = $parsedNew['data'] ?? $parsedNew;
            $exceptionDetails = [
                'reference_id' => $payload['reference_id'] ?? null,
                'class' => $payload['exception_class'] ?? 'Exception',
                'message' => $payload['message'] ?? $auditLog->action,
                'file' => $payload['file'] ?? '-',
                'line' => $payload['line'] ?? '-',
                'status_code' => $payload['status_code'] ?? 500,
                'url' => $payload['url'] ?? null,
                'method' => $payload['method'] ?? null,
                'trace' => $payload['trace'] ?? [],
            ];
        }

        return response()->json([
            'id' => $auditLog->id,
            'action' => $auditLog->action,
            'action_label' => $auditLog->action_label_tr,
            'action_badge' => $auditLog->action_badge,
            'human_description' => $auditLog->human_description,
            'model_type' => $auditLog->model_type,
            'model_name' => $auditLog->model_name_tr,
            'model_id' => $auditLog->model_id,
            'target_url' => $auditLog->target_url,
            'status' => $auditLog->status,
            'created_at' => $auditLog->created_at ? $auditLog->created_at->format('d.m.Y H:i:s') : '-',
            'created_at_human' => $auditLog->created_at ? $auditLog->created_at->diffForHumans() : '-',
            'ip_address' => $auditLog->ip_address ?? '127.0.0.1',
            'user_agent' => $auditLog->user_agent ?? '-',
            'user' => $auditLog->user ? [
                'id' => $auditLog->user->id,
                'name' => $auditLog->user->full_name,
                'email' => $auditLog->user->email,
                'role' => $auditLog->user->role?->name ?? 'Kullanıcı',
                'photo_url' => $auditLog->user->profile_photo_url,
            ] : null,
            'old_values' => $parsedOld,
            'new_values' => $parsedNew,
            'is_exception' => $isException,
            'exception_details' => $exceptionDetails,
            'correlation_id' => $parsedNew['correlation_id'] ?? null,
        ]);
    }

    /**
     * Export filtered audit logs to CSV with Excel UTF-8 BOM support.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $query = AuditLog::with(['user'])->latest('id');

        if ($request->filled('tab')) {
            $query->forTab($request->get('tab'));
        }
        if ($request->filled('search')) {
            $query->search($request->get('search'));
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        // Date range filter
        $defaultDays = config('bv.audit_default_days', 60);
        $isAllTime = $request->boolean('all_time');
        $hasCustomDates = $request->filled('start_date') || $request->filled('end_date');

        if ($hasCustomDates) {
            $query->dateRange($request->get('start_date'), $request->get('end_date'));
        } elseif (!$isAllTime) {
            $query->where('created_at', '>=', now()->subDays($defaultDays)->startOfDay());
        }

        $fileName = 'bv-audit-logs-' . date('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Microsoft Excel Turkish Character Support
            fputs($handle, "\xEF\xBB\xBF");

            // CSV Header Row
            fputcsv($handle, [
                'Log ID',
                'Tarih & Saat',
                'Kullanıcı ID',
                'Kullanıcı Adı',
                'Kullanıcı E-posta',
                'IP Adresi',
                'Eylem (Action)',
                'Eylem Tipi (TR)',
                'Model Türü',
                'Model ID',
                'Durum',
                'Açıklama / Detay',
            ]);

            $query->chunk(500, function ($logs) use ($handle) {
                foreach ($logs as $log) {
                    $new = $log->parsed_new_values;
                    $detailText = $new['details'] ?? ($new['data']['message'] ?? $log->action);

                    fputcsv($handle, [
                        $log->id,
                        $log->created_at ? $log->created_at->format('d.m.Y H:i:s') : '-',
                        $log->user_id ?? '-',
                        $log->user?->full_name ?? 'Sistem / Anonim',
                        $log->user?->email ?? '-',
                        $log->ip_address ?? '-',
                        $log->action,
                        $log->action_label_tr,
                        $log->model_name_tr,
                        $log->model_id ?: '-',
                        $log->status,
                        is_string($detailText) ? $detailText : json_encode($detailText, JSON_UNESCAPED_UNICODE),
                    ]);
                }
            });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }
}
