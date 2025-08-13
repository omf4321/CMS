<?php

namespace App\Http\Controllers\attendance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\sms\sms_trait;
use App\Model\Admin\attendance_list;
use App\Model\Admin\batch;
use App\Model\Admin\daily_schedule;
use App\Model\Admin\student;
use App\Model\Admin\student_attendance;
use Auth;
use Cache;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;



class AttendanceController extends Controller
{
    use sms_trait;
    public function __construct()
    {
        $this->middleware('admin');
    }
   
    public function get_attendance_schedule($date)
    {
        $schedule_of_batch = batch::whereHas('schedules', function($query) use ($date){
            $query->whereDate('date', $date);
        })->with(array('students' => function($query){
            $query->select('id', 'reg_no', 'name', 'batch_id')->with('batches', 'todays_attendance');
        }))->with('echelons', 'attendance_lists')->get();
        return response()->json(['schedule_of_batch' => $schedule_of_batch]);
    }

    public function get_batch_attendance(Request $request, $batch_id){
        // return response()->json('a', 419);
        $attendance_list = attendance_list::where('batch_id', $batch_id)->whereDate('date', Carbon::today())->first();
        if (!$attendance_list) {
            $attendance_list = new attendance_list;
            $attendance_list->batch_id = $batch_id;
            $attendance_list->date = Carbon::today()->format('Y-m-d');
            $attendance_list->save();

            $student_list = student::where('batch_id', $batch_id)->where('status', 'present')->where('running_month', '<=', Carbon::today())->get();
            foreach ($student_list as $key => $student) {
                $att = student_attendance::firstOrCreate(
                    ['student_id' =>  $student->id, 'date' => Carbon::today()->format('Y-m-d')],
                    ['student_id' =>  $student->id, 'date' => Carbon::today()->format('Y-m-d'), 'attendance' =>  0]
                );
            }
        }

        $students = student::where('batch_id', $batch_id)->where('status', 'present')->where('running_month', '<=', Carbon::today())->select('id', 'reg_no', 'name', 'batch_id')->with('todays_attendance')->has('todays_attendance')->orderBy('reg_no')->get();

        return response()->json(['student_list' => $students]);
    }

    public function save_batch_attendance(Request $request)
    {
        // return response()->json($request->all(), 422);
        $count = 0;
        foreach ($request->student_attendance as $key => $attendance) {
            $std = student_attendance::find($attendance['attendance_id']);
            $std->attendance = $attendance['attendance'];
            if ($attendance['attendance']) {
                $count = $count + 1;
            }
            $std->save();           
        }
        $attendance_list = attendance_list::where('batch_id', $request->batch_id)->whereDate('date', Carbon::today())->first();
        $attendance_list->status = 'completed';
        $attendance_list->present = $count;
        $attendance_list->save();
        daily_schedule::where('id', $attendance_list->schedule_id)->update(['attendance_taken' => 1]);
    }

    public function delete_batch_attendance($batch_id)
    {        
        $attendance_list = attendance_list::where('batch_id', $batch_id)->whereDate('date', Carbon::today())->first();
        if ($attendance_list->status == 'done') {
            return response()->json('Teacher already done.', 422);
        }
        daily_schedule::where('batch_id', $attendance_list->batch_id)->whereDate('date', Carbon::today())->update(['attendance_taken' => Null]);
        $attendance_list->delete();
        $students = student_attendance::whereHas('students', function($query) use($batch_id){
            $query->where('batch_id', $batch_id);
        })->whereDate('date', Carbon::today())->delete();
    }

    public function save_random_attendance(Request $request)
    {
        // return response()->json($request->all(), 422);
        $ids = [];
        foreach ($request->random_attendance as $key => $attendance) {
            $attendance_list = attendance_list::where('batch_id', $attendance['batch_id'])->whereDate('date', Carbon::today())->first();
            if (!$attendance_list) {
                $attendance_list = new attendance_list;
                $attendance_list->batch_id = $attendance['batch_id'];
                $attendance_list->date = Carbon::today()->format('Y-m-d');
                $attendance_list->save();
                $student_list = student::where('batch_id', $attendance['batch_id'])->where('status', 'present')->get();
                foreach ($student_list as $key => $student) {
                    $att = student_attendance::firstOrCreate(
                        ['student_id' =>  $student->id, 'date' => Carbon::today()->format('Y-m-d')],
                        ['student_id' =>  $student->id, 'date' => Carbon::today()->format('Y-m-d'), 'attendance' =>  0]
                    );
                }
            }
            // save student attendance
            $std = student_attendance::where('student_id', $attendance['student_id'])->whereDate('date', Carbon::today())->first();
            if (!$std) {
                $std = new student_attendance;
            }
            $std->student_id = $attendance['student_id'];
            $std->attendance = $attendance['attendance'];
            $std->date = Carbon::today()->format('Y-m-d');
            $std->save();
            array_push($ids, $attendance['student_id']);          
        }
        return response()->json(['ids' => $ids]);
    }

    public function get_student_absent_list($days){
        $students = Cache::rememberForever("absent_list_" . $days, function () use ($days)
        {
            $result = student::with(array('attendances'=>function($query) use ($days){
                $query->orderBy('date', 'desc');
            }))->where('status', 'present')->where(function($query){
                $query->whereDate('assume_absent_date', '<', Carbon::today())->orwhereNull('assume_absent_date');
            })->where('running_month', '<=', Carbon::today())->with('batches.echelons')->orderBy('batch_id')->orderBy('reg_no')->get();
            return $result;
        });
        foreach ($students as $s_i => $student) {
            if (sizeof($student->attendances)==0) {
               unset($students[$s_i]);
               continue;
            }
            if (sizeof($student->attendances)<$days) {
               unset($students[$s_i]);
               continue;
            }
            for ($i=0; $i < $days ; $i++) { 
                if ($student->attendances[$i]->attendance==1) {
                    unset($students[$s_i]);
                }
            }            
        }
        $student_list = [];
        foreach ($students as $s_i => $student) {
            array_push($student_list, $student);
        }
        return response()->json(['students' => $student_list]);
    }

    public function get_student_attendance_list($date){
        $batches = batch::with(array('students' => function($query) use ($date){
            $query->where('status', 'present')->with(array('attendances'=>function($query) use ($date){
                $query->whereDate('date', $date);
            }))->orderBy('reg_no');
        }))->whereHas('students', function($query) use($date){
            $query->where('status', 'present')->whereHas('attendances', function($query) use ($date){
                $query->whereDate('date', $date);
            });
        })->with('echelons')->get();

        return response()->json(['batches' => $batches]);
    }

    public function save_attendance_status(Request $request)
    {
        $student = student::find($request->student_id);
        $student->attendance_status = $request->attendance_status;
        $student->assume_absent_date = $request->assume_absent_date;
        if ($request->drop_reason) {
            $student->drop_reason = $request->drop_reason;
            $running_month = $request->year . '-' . $request->month . '-01';
            $student->inactive_month = $running_month;
            $student->status = 'droped';
        }
        $student->save();
        for ($i=1; $i < 7 ; $i++) { 
            Cache::forget('absent_list_' . $i);
        } 
    }

    // for attendance analysis
    public function send_sms(Request $request)
    {
        if (!$request->sms_text) {
            return response()->json('none', 422);
        }
        $students = student::where('status', 'present')->orderBy('reg_no');

        if ($request->sms_option == 'all_present') {
            $students = $students->whereHas('attendances', function($query) use ($request) {
                $query->where('attendance', 1)->whereDate('date', $request->date);
            });
        }

        if ($request->sms_option == 'all_absent') {
            $students = $students->whereHas('attendances', function($query) use ($request) {
                $query->where('attendance', 0)->whereDate('date', $request->date);
            });
        }

        if ($request->sms_option == 'all_unpaid') {
            $students = $students->whereHas('latest_payments', function($query) {
                $query->whereDate('month', '<', Carbon::today()->startOfMonth());
            });
        }

        if ($request->sms_option == 'present_and_unpaid') {
            $students = $students->whereHas('latest_payments', function($query) {
                $query->whereDate('month', '<', Carbon::today()->startOfMonth());
            })->whereHas('attendances', function($query) use ($request){
                $query->where('attendance', 1)->whereDate('date', $request->date);
            });
        }

        if ($request->sms_option == 'absent_and_unpaid') {
            $month = Carbon::today()->startOfMonth()->format('Y-m-d');
            // return $month;
            $students = $students->whereHas('latest_payments', function($query) use ($month) {
                $query->whereDate('month', '<', $month);
            })->whereHas('attendances', function($query) use ($request){
                $query->where('attendance', 0)->whereDate('date', $request->date);
            });
        }

        $student = $students->get();

        // create sms json array
        $json_array = [];
        foreach ($student as $key => $std) {
            $text = $request->sms_text;
            $json_data = new \stdClass;
            $json_data->MobileNumber =  $std->mobile;
            $json_data->SmsText = $text;
            $json_data->Type = "TEXT";
            array_push($json_array, $json_data);
        }
        if (sizeof($json_array)==0) {
            return response()->json(['sms' => 'no_sms']);
        }
        return response()->json(['sms_report' => [], 'balance' => number_format((float)1000, 2, '.', '') . " (SMS Server no ready yet)"]);
        $sms_report = $this->onnorokom_list_sms($student, $request, $json_array);
        $balance = $this->onnorokom_balance_for_list();
        return response()->json(['sms_report' => $sms_report, 'balance' => $balance, 'sms' => 'sms']);
    }



    public function get_dashboard_attendance_list($date)
    {        
        $date = Carbon::today();
        $dashboard_attendance = batch::whereHas('schedules', function($query) use ($date){
            $query->whereDate('date', $date);
        })->withCount(array('students' => function($query){
                $query->whereHas('todays_present');
        }))->with('echelons', 'attendance_lists')->get();
        return response()->json(['dashboard_attendance' => $dashboard_attendance]);
    }

    public function get_individual_student_attendance(Request $request)
    {        
        $student = student::where('reg_no', $request->reg_no)->where('status', 'present')->first();
        if ($student) {
            $attendances = student_attendance::where('student_id', $student->id)->whereDate('date', '>=', $request->date_from)->whereDate('date', '<=', $request->date_to)->OrderBy('date', 'desc')->get();
            return response()->json(['attendances' => $attendances, 'student' => $student]);
        }
        return response()->json(['attendances' => [], 'student' => $student]);
    }

    public function send_attendance_sms(Request $request)
    {        
        $batches_ids = []; 
        foreach ($request->attendance as $key => $batch) {
            array_push($batches_ids, $batch['id']);
        }
        if ($request->attendance_sms_type != 'all') {            
            $attendance = $request->attendance_sms_type == 'absent' ? 0 : 1;        
            $student = student::whereIn('batch_id', $batches_ids)->WhereHas('todays_attendance', function($query) use($attendance){
                $query->where('attendance', $attendance);
            })->with('todays_attendance')->get();
        }
        if ($request->attendance_sms_type == 'all') {
            $student = student::whereIn('batch_id', $batches_ids)->where('status', 'present')->with('todays_attendance')
            ->get();
        }
        // return response()->json($student, 422);
        $sms_report = $this->gw_many_to_many($student, 'attendance_sms', $request->destination_number);

        if (isset($sms_report['sms_report']) && sizeof($sms_report['sms_report'])) {            
            attendance_list::whereIn('batch_id', $batches_ids)->whereDate('date', Carbon::today())->update(['status' => 'sms', 'sms_status'=> 1]);   
            Cache::forget('dashboard_attendance_' . Carbon::today()->format('Y-m-d'));
        }
        // clear cache

        return response()->json(['sms_report' => $sms_report['sms_report'], 'balance' => $sms_report['balance'], 'sms' => 'sms']);
    }
    
}
