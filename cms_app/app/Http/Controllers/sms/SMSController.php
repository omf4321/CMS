<?php

namespace App\Http\Controllers\sms;

use App\Http\Controllers\Controller;
use App\Http\Controllers\sms\sms_trait;
use App\Model\Admin\sms_balance;
use App\Model\Admin\sms_report;
use App\Model\Admin\student;
use App\Model\Admin\student_payment;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use stdClass;

class SMSController extends Controller
{
    use sms_trait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function send_sms(Request $request)
    {        
        // return response()->json(['sms_report' => [], 'balance' => number_format((float)1000, 2, '.', '') . " (SMS Server no ready yet)"]);
        // echelon exist
        if ($request->echelons) {
            $echelons = [];
            foreach ($request->echelons as $key => $echelon) {
                array_push($echelons, $echelon['value']);
            }
            $student = student::whereHas('batches', function($query) use ($echelons){
                $query->whereIn('echelon_id', $echelons);
            });
        }
        // batch exist
        if ($request->batches) {
            $batches = [];
            foreach ($request->batches as $key => $batch) {
                array_push($batches, $batch['value']);
            }
            $student = $student->whereHas('batches', function($query) use ($batches){
                $query->whereIn('id', $batches);
            });
        }        
        // perform sms
        if ($request->sms_type != 'list_sms') {
            // return response()->json(['error' => 'not allowed this type'], 422);
        }

        if ($request->sms_type == 'one_to_one') {
            if ($request->reg_no) {
                $student = student::where('reg_no', $request->reg_no)->where('status', $request->student_status)->get();
            }
            if ($request->mobile) {
                $sms_report = $this->gw_sent_to_number($request);
                $balance = sms_balance::where('id', 1)->first()->balance;
                $balance = number_format((float)$balance, 2, '.', '');
                return response()->json(['sms_report' => $sms_report, 'balance' => $balance]);
            }
        }

        if ($request->student_status != 'all') {
            $student = $student->where('status', $request->student_status);
        }
        
        if ($request->sms_type == 'list_sms') {
            if ($request->year) {
                $student = $student->whereYear('running_month', $request->year);
            }
            if ($request->month) {
                $student = $student->whereMonth('running_month', '>=', $request->month);
            }
            $student = $student->get();
        }
                 
        foreach ($student as $key => $std) {
           $std->message = str_replace(array('[student_name]', '[reg_no]'), array($std->name, $std->reg_no), $request->sms);
        }

        // return response()->json($student, 422);
        $sms_report = $this->gw_many_to_many($student, $request->sms_language, $request->destination_number);
        return response()->json($sms_report);        
        
    }

    public function get_sms_report(Request $request)
    {
        $sms_reports = sms_report::whereDate('created_at', '>=', $request->from_date)->whereDate('created_at', '<=', $request->to_date)->with('students.batches.echelons');

        if ($request->batch_id) {            
            $sms_reports = $sms_reports->whereHas('students', function($query) use ($request){
                $query->where('batch_id', $request->batch_id);
            });
        }


        return response()->json(['sms_reports' => $sms_reports->get()]);

    }

} //end class
