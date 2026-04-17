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
    public function index()
    {
        return view('logtracker::auditpanel.index');
    }

    public function logApidata(Request $request)
    {
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

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('new_data', 'like', '%' . $search . '%')
                    ->orWhere('data', 'like', '%' . $search . '%')
                    ->orWhere('users', 'like', '%' . $search . '%')
                    ->orWhere('table_name', 'like', '%' . $search . '%');
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
            'id', 'users', 'user_id', 'log_date', 'table_name', 'log_type', 'new_data', 'data'
        ])->paginate($perPage)->appends($request->query());

        $data = $pagination->getCollection()->map(function ($log) {
            $logDate = $log->log_date instanceof Carbon ? $log->log_date : Carbon::parse($log->log_date);

            return [
                'id' => $log->id,
                'users' => $log->users,
                'user_id' => $log->user_id,
                'username' => $log->user_id,
                'log_date' => $logDate->format('Y-m-d'),
                'log_time' => $logDate->format('H:i:s a'),
                'human_date' => $logDate->diffForHumans(),
                'table_name' => $log->table_name,
                'log_type' => $log->log_type,
                'new_log_details' => $log->new_data,
                'log_details' => json_encode($log->data),
                'details' => $log->data,
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
            // 'tables' => $tables,
            'filters' => [
                'tables' => $logTables,
                'users' => $logUsers,
                'types' => $logTypes,
            ],
            'services' => '',
        ], 200);
    }

    
    /*************This two method only for Mongo Database************/

    /**
     * @ TODO
     * @ Return only unsynchronous data
     *
     * @return json
     */
    public function getUnsynchronousData()
    {
        $synchronous = Logtracker::where('synchronous',0)->get();
        return response()->json(['data' => $synchronous],200);
    }

    /**
     * @ TODO
     * @ Need to change synchronous field false to true
     *
     * @param Request $request
     * @return string
     */
    public function synchronousProcess(Request $request)
    {
        DB::table('logtrackers')->where('id',$request->id)->update([
            'synchronous' => $request->synchronous
        ]);
        return response()->json(['message' => 'success'],200);
    }





    /**************Only for Google Analytic Reports***************/

    public function googleAnalyticData()
    {
        $analyticsData = Analytics::fetchVisitorsAndPageViews(Period::days(30));
        
        $mostVisitedPage = Analytics::fetchMostVisitedPages(Period::days(7));
        
        $TopReferrers = Analytics::fetchTopReferrers(Period::days(7));
        
        $chart = Analytics::fetchUserTypes(Period::days(7));
        
        $chartData = [
            'NewVisitor' => $chart[0]['sessions'] ?? 1,
            'ReturningVisitor' => $chart[1]['sessions'] ?? 2
        ];

        return response()->json(['analyticsData' => $analyticsData, 'mostVisitedPage' => $mostVisitedPage, 'TopReferrers' => $TopReferrers, 'chartData' => $chartData],200);

        return view('auditpanel.analytic-dashboard.index', [
            'analyticsData' => $analyticsData,
            'chartData'=>json_encode($chartData),
            'mostVisitedPage' => $mostVisitedPage,
            'TopReferrers' => $TopReferrers,
        ]);
    }

}
