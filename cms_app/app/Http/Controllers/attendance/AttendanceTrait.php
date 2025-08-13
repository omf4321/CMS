<?php

namespace App\Http\Controllers\attendance;

use App\Model\Admin\attendance_list;
use App\Model\Admin\batch;
use App\Model\Admin\daily_schedule;
use App\Model\Admin\student;
use App\Model\Admin\student_attendance;
use Auth;
use Cache;
use Carbon\Carbon;
use Illuminate\Http\Request;

trait AttendanceTrait
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function create_batch_attendance($batch_id){
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
    }

    public function save_single_student_attendance($student, $attendance){
        // return response()->json('a', 419);
        $student_attendance = student_attendance::where('student_id', $student->id)->whereDate('date', Carbon::today())->update(['attendance' => $attendance]);
    }
    
}
