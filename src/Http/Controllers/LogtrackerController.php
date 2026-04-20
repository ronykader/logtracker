<?php

namespace Obd\Logtracker\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Obd\Logtracker\Models\Logtracker;

class LogtrackerController extends Controller
{
    
    public function index(Request $request)
    {
        $this->checkAuthorization();
        $this->handleLocale($request);
        return view('logtracker::shell', ['config' => $this->buildConfig('audit')]);
    }

    public function logApidata(Request $request)
    {
        $this->checkAuthorization();

        $query = Logtracker::orderBy('id', 'desc');

        if ($request->filled('table')) {
            $query->where('table_name', $request->query('table'));
        }

        if ($request->filled('type')) {
            $query->where('log_type', $request->query('type'));
        }

        if ($request->filled('user')) {
            $query->where('users', 'like', '%' . $request->query('user') . '%');
        }

        if ($request->filled('start_date')) {
            $query->whereDate('log_date', '>=', $request->query('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('log_date', '<=', $request->query('end_date'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('new_data', 'like', '%' . $search . '%')
                    ->orWhere('data', 'like', '%' . $search . '%')
                    ->orWhere('users', 'like', '%' . $search . '%')
                    ->orWhere('table_name', 'like', '%' . $search . '%')
                    ->orWhere('ip_address', 'like', '%' . $search . '%')
                    ->orWhere('url', 'like', '%' . $search . '%')
                    ->orWhere('route_name', 'like', '%' . $search . '%');
            });
        }

        $perPage = (int) $request->query('per_page', 10);
        if ($perPage < 1) {
            $perPage = 10;
        }

        // Fetch filters using a fresh query to avoid ORDER BY conflicts with DISTINCT
        $filterBase = Logtracker::query();
        if ($request->filled('table')) $filterBase->where('table_name', $request->query('table'));
        if ($request->filled('type')) $filterBase->where('log_type', $request->query('type'));
        if ($request->filled('user')) $filterBase->where('users', 'like', '%' . $request->query('user') . '%');
        if ($request->filled('start_date')) $filterBase->whereDate('log_date', '>=', $request->query('start_date'));
        if ($request->filled('end_date')) $filterBase->whereDate('log_date', '<=', $request->query('end_date'));
        if ($request->filled('search')) {
            $s = $request->query('search');
            $filterBase->where(function ($q) use ($s) {
                $q->where('new_data', 'like', "%$s%")->orWhere('data', 'like', "%$s%")->orWhere('users', 'like', "%$s%")
                  ->orWhere('table_name', 'like', "%$s%")->orWhere('ip_address', 'like', "%$s%")
                  ->orWhere('url', 'like', "%$s%")->orWhere('route_name', 'like', "%$s%");
            });
        }

        $logTables = (clone $filterBase)->select('table_name')->distinct()->pluck('table_name')->filter()->values();
        $logUsers = (clone $filterBase)->select('users')->distinct()->pluck('users')->filter()->values();
        $logTypes = (clone $filterBase)->select('log_type')->distinct()->pluck('log_type')->filter()->values();

        $pagination = $query->select([
            'id', 'users', 'user_id', 'log_date', 'table_name', 'log_type', 'new_data', 'data', 'ip_address', 'user_agent', 'url', 'route_name'
        ])->paginate($perPage)->appends($request->query());

        $data = $pagination->getCollection()->map(function ($log) {
            $logDate = $log->log_date instanceof Carbon ? $log->log_date : Carbon::parse($log->log_date);
            return [
                'id' => $log->id,
                'users' => $log->users,
                'user_id' => $log->user_id,
                'log_date' => $logDate->format('Y-m-d'),
                'log_time' => $logDate->format('H:i:s a'),
                'human_date' => $log->dateHumanize,
                'table_name' => $log->table_name,
                'log_type' => $log->log_type,
                'data' => $log->data,
                'new_data' => $log->new_data,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'url' => $log->url,
                'route_name' => $log->route_name,
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $pagination->currentPage(),
                'last_page' => $pagination->lastPage(),
                'per_page' => $pagination->perPage(),
                'from' => $pagination->firstItem(),
                'to' => $pagination->lastItem(),
                'total' => $pagination->total(),
            ],
            'filters' => [
                'tables' => $logTables,
                'users' => $logUsers,
                'types' => $logTypes,
            ],
        ], 200);
    }

    public function getInsights(Request $request)
    {
        $this->checkAuthorization();

        $query = Logtracker::query();

        if ($request->filled('start_date')) {
            $query->whereDate('log_date', '>=', $request->query('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('log_date', '<=', $request->query('end_date'));
        }

        // Top 5 Modified Tables
        $topTables = (clone $query)->select('table_name', \DB::raw('count(*) as count'))
            ->groupBy('table_name')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        // Top 5 Active Users
        $topUsers = (clone $query)->select(\DB::raw('MAX(users) as users'), 'user_id', \DB::raw('count(*) as count'))
            ->groupBy('user_id')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get()
            ->map(function($log) {
                $userData = json_decode($log->users, true) ?? [];
                return [
                    'name' => $userData['name'] ?? 'User #'.$log->user_id,
                    'count' => $log->count
                ];
            });

        // Activity Timeline (Last 14 Days OR Range)
        $timelineQuery = (clone $query)->select(\DB::raw('DATE(log_date) as date'), \DB::raw('count(*) as count'));
        
        if (!$request->filled('start_date')) {
            $timelineQuery->where('log_date', '>=', now()->subDays(14));
        }

        $activity = $timelineQuery->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json([
            'top_tables' => $topTables,
            'top_users' => $topUsers,
            'activity' => $activity
        ]);
    }

    public function insights(Request $request)
    {
        $this->checkAuthorization();
        $this->handleLocale($request);
        return view('logtracker::shell', ['config' => $this->buildConfig('insights')]);
    }

    public function systemLogs(Request $request)
    {
        $this->checkAuthorization();
        $this->handleLocale($request);
        return view('logtracker::shell', ['config' => $this->buildConfig('system')]);
    }

    /**
     * Parse laravel.log and return human-readable JSON data.
     */
    public function getSystemLogData(Request $request)
    {
        $this->checkAuthorization();
        
        $logFile = storage_path('logs/laravel.log');
        if (!file_exists($logFile)) {
            return response()->json(['data' => [], 'message' => 'Log file not found.']);
        }

        $lines = [];
        $fp = fopen($logFile, 'r');
        
        // Read last 500 lines for performance
        $pos = -2;
        $count = 0;
        $maxLines = 500;
        
        fseek($fp, $pos, SEEK_END);
        while ($count < $maxLines && fseek($fp, $pos, SEEK_END) !== -1) {
            $char = fgetc($fp);
            if ($char === "\n") {
                $count++;
            }
            $pos--;
        }
        
        $logEntries = [];
        $currentEntry = null;

        while ($line = fgets($fp)) {
            // Regex for standard Laravel log: [2026-04-20 20:29:21] local.ERROR: Message
            preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.*)/', $line, $matches);

            if (!empty($matches)) {
                if ($currentEntry) {
                    $logEntries[] = $currentEntry;
                }
                $currentEntry = [
                    'timestamp' => $matches[1],
                    'env' => $matches[2],
                    'level' => strtoupper($matches[3]),
                    'message' => trim($matches[4]),
                    'stack' => '',
                ];
            } elseif ($currentEntry) {
                // If it doesn't match, it's likely a stack trace or multiline message
                $currentEntry['stack'] .= $line;
            }
        }
        
        if ($currentEntry) {
            $logEntries[] = $currentEntry;
        }

        fclose($fp);

        return response()->json([
            'data' => array_reverse($logEntries),
            'file' => basename($logFile)
        ]);
    }

    public function clearSystemLog(Request $request)
    {
        $this->checkAuthorization();
        $logFile = storage_path('logs/laravel.log');

        if (!file_exists($logFile)) {
            return response()->json(['success' => false, 'message' => 'Log file not found at: ' . $logFile], 404);
        }

        if (!is_writable($logFile)) {
            return response()->json(['success' => false, 'message' => 'Not writable: ' . $logFile . ' — Run: chmod 664 ' . $logFile], 403);
        }

        file_put_contents($logFile, '', LOCK_EX);

        return response()->json(['success' => true, 'message' => 'Log file cleared.']);
    }

    public function deleteSystemLogEntries(Request $request)
    {
        $this->checkAuthorization();

        // Explicitly decode the raw JSON body — most reliable across all Laravel versions
        $body       = json_decode($request->getContent(), true) ?? [];
        $timestamps = $body['timestamps'] ?? [];

        if (empty($timestamps)) {
            return response()->json([
                'success' => false,
                'message' => 'No timestamps received.',
                'debug'   => ['body' => $request->getContent(), 'content_type' => $request->header('Content-Type')],
            ], 422);
        }

        $logFile = storage_path('logs/laravel.log');

        if (!file_exists($logFile)) {
            return response()->json(['success' => false, 'message' => 'Log file not found.'], 404);
        }

        $content  = file_get_contents($logFile);
        $lines    = preg_split('/\r?\n/', $content);
        $toDelete = array_flip($timestamps);
        $filtered = [];
        $skip     = false;

        foreach ($lines as $line) {
            preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $line, $m);
            if (!empty($m)) {
                $skip = isset($toDelete[$m[1]]);
            }
            if (!$skip) {
                $filtered[] = $line;
            }
        }

        file_put_contents($logFile, implode("\n", $filtered));

        $count = count($timestamps);
        return response()->json([
            'success' => true,
            'message' => $count . ' entr' . ($count === 1 ? 'y' : 'ies') . ' deleted.',
        ]);
    }

    private function handleLocale(Request $request): void
    {
        if ($request->has('locale')) {
            session(['logtracker_locale' => $request->get('locale')]);
            app()->setLocale($request->get('locale'));
        }
        if (session()->has('logtracker_locale')) {
            app()->setLocale(session('logtracker_locale'));
        }
    }

    private function buildConfig(string $page): array
    {
        return [
            'page'   => $page,
            'locale' => app()->getLocale(),
            'routes' => [
                'index'                => route('logtracker.index'),
                'insights'             => route('logtracker.insights'),
                'system-logs'          => route('logtracker.system-logs'),
                'api'                  => url(config('logtracker.api_prefix', 'api/audit-panel-data')),
                'system-log-data'      => route('logtracker.system-log-data'),
                'system-log-clear'     => route('logtracker.system-log-clear'),
                'system-log-delete'    => route('logtracker.system-log-delete'),
            ],
            'i18n' => [
                'audit_panel'              => __('logtracker::ui.audit_panel'),
                'date'                     => __('logtracker::ui.table_header_date'),
                'user'                     => __('logtracker::ui.table_header_user'),
                'table'                    => __('logtracker::ui.table_header_table'),
                'type'                     => __('logtracker::ui.table_header_type'),
                'action'                   => __('logtracker::ui.table_header_action'),
                'filter_search'            => __('logtracker::ui.filter_label_search'),
                'filter_search_placeholder'=> __('logtracker::ui.filter_placeholder_search'),
                'filter_table'             => __('logtracker::ui.filter_label_table'),
                'filter_type'             => __('logtracker::ui.filter_label_type'),
                'all_tables'               => __('logtracker::ui.all_tables'),
                'all_types'                => __('logtracker::ui.all_types'),
                'refresh'                  => __('logtracker::ui.filter_button_refresh'),
                'reset'                    => __('logtracker::ui.filter_button_reset'),
                'details_title'            => __('logtracker::ui.details_title'),
                'details_field'            => __('logtracker::ui.details_field_label'),
                'details_old'              => __('logtracker::ui.details_old_value'),
                'details_new'              => __('logtracker::ui.details_new_value'),
                'details_url'              => __('logtracker::ui.details_url'),
                'details_route'            => __('logtracker::ui.details_route'),
                'type_create'              => __('logtracker::ui.log_type_create'),
                'type_edit'                => __('logtracker::ui.log_type_edit'),
                'type_delete'              => __('logtracker::ui.log_type_delete'),
                'empty'                    => __('logtracker::ui.no_logs_found'),
                'loading'                  => __('logtracker::ui.loading_logs'),
            ],
        ];
    }

    private function checkAuthorization()
    {
        $allowedIds = config('obd_tracker.allowed_user_ids', []);
        if (!empty($allowedIds) && !in_array((string)auth()->id(), array_map('trim', $allowedIds))) {
            if (request()->expectsJson()) {
                abort(response()->json(['error' => 'Unauthorized'], 403));
            }
            abort(403, 'Unauthorized access.');
        }
    }
}
