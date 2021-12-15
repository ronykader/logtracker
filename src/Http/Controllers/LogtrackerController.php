<?php

namespace Obd\Logtracker\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Obd\Logtracker\Models\Logtracker;

class LogtrackerController extends Controller
{
    
    /**
     * @TODO: 
     *  - RETURN LOGS INFORMATION IN DETAILS
     *  - RETURN TABLE LIST OF THIS PROJECTS
     *  - RETURN USERS LIST OF THIS PROJECTS
     *  - RETURN SERVICE LIST ONLY FOR MY GOV PROJECTS
     *
     * @param Request $request
     * @return JSON
     */
    public function logApidata(Request $request)
    {
        // Fetch all table from the database
        $allTable = array_map('current', DB::select('SHOW TABLES'));

        // Exclude unnecessary table from the table list
        $exclude = ['failed_jobs', 'password_resets', 'migrations', 'logtrackers','personal_access_tokens'];
        
        // Prepare Executable table list 
        $tables = array_diff($allTable, $exclude);

        /**
         * Get all service List
         */
        // $services = DB::table('my_gov_service')->select(['id','name_en','name','sector'])->get();
        
        // Fetch logable data
        $data = Logtracker::orderBy('id', 'desc')->select([
            'id','users','user_id','log_date','table_name','log_type','new_data','data'
        ])
        ->get()->map(function($data) {
            // It will remove from here later and handle it from core project
            return [
                'id' => $data->id,
                'users' => $data->users,
                'user_id' => $data->user_id,
                'username' => $data->user_id,
                'log_date' => $data->log_date->format('Y-m-d'),
                'log_time' => $data->log_date->format('H:i:s a'),
                'human_date' => $data->log_date->diffForHumans(),
                'table_name' => $data->table_name,
                'log_type' => $data->log_type,
                // 'service_id' => $data->service_id,
                'new_log_details' => $data->new_data,
                'log_details' => json_encode($data->data),
                'details' => $data->data,
            ];
        });
        
        return response()->json(['data' => $data, 'tables' => $tables, 'services' => ''],200);
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
