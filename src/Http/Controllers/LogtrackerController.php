<?php

namespace Obd\Logtracker\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Obd\Logtracker\Models\Logtracker;

class LogtrackerController extends Controller
{
    
    /**
     * Display the audit panel UI.
     */
    public function index(Request $request)
    {
        $allowedIds = config('obd_tracker.allowed_user_ids', []);
        if (!empty($allowedIds) && !in_array((string)auth()->id(), array_map('trim', $allowedIds))) {
            abort(403, 'Unauthorized access to Audit Panel.');
        }

        if ($request->has('locale')) {
            session(['logtracker_locale' => $request->get('locale')]);
            app()->setLocale($request->get('locale'));
        }

        if (session()->has('logtracker_locale')) {
            app()->setLocale(session('logtracker_locale'));
        }

        return view('logtracker::auditpanel.index');
    }

    public function logApidata(Request $request)
    {
        $allowedIds = config('obd_tracker.allowed_user_ids', []);
        if (!empty($allowedIds) && !in_array((string)auth()->id(), array_map('trim', $allowedIds))) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

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

        $filterQuery = clone $query;
        $filterQuery->getQuery()->orders = null;
        $logTables = $filterQuery->select('table_name')->distinct()->pluck('table_name')->filter()->values();
        $logUsers = $filterQuery->select('users')->distinct()->pluck('users')->filter()->values();
        $logTypes = $filterQuery->select('log_type')->distinct()->pluck('log_type')->filter()->values();

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
        $allowedIds = config('obd_tracker.allowed_user_ids', []);
        if (!empty($allowedIds) && !in_array((string)auth()->id(), array_map('trim', $allowedIds))) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

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
}
