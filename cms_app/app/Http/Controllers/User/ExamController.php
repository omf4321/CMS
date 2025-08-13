<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Imports\ExamImport;
use App\Model\Admin\daily_schedule;
use App\Model\Admin\exam_list;
use App\Model\Admin\exam_question_list;
use App\Model\Admin\random_number;
use App\Model\Admin\schedule_teacher_attendance;
use App\Model\Admin\student;
use App\Model\Admin\student_exam_mark;
use App\User;
use Auth;
use Cache;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Excel;

// Contains
// User Login Success Home Page
// User Dashboard


class ExamController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:web');
        // $this->middleware('admin');
    }

    
    public function get_invigilator_dashboard_exam()
    {
        $user = Auth::guard('web')->user();
        $daily_schedule = daily_schedule::where('schedule_type', 'exam')->with('subjects', 'exam_lists', 'batches.echelons')->WhereDate('date', Carbon::today())
        ->with(array('batches.attendance_lists' => function($query){
            $query->whereDate('date', Carbon::today());
        }))->get();
        return response()->json(['daily_schedule' => $daily_schedule]);
    }
    
    public function save_setup_exam(Request $request)
    {        
        $user = Auth::guard('web')->user();
        $new = 'false';
        $exam_list = exam_list::where('schedule_id', $request->schedule_id)->first();
        if ($exam_list && $exam_list->invigilator_id && $exam_list->invigilator_id != $user->teachers->id) {
            return response()->json('Already set by other teacher', 422);
        }
        if (!$exam_list) {            
            $exam_list = new exam_list;
            $new = 'true';
        }
        
        $exam_list->batch_id = $request->batch_id;
        $exam_list->schedule_id = $request->id;
        $exam_list->sub_full_mark = $request->sub_full_mark;
        $exam_list->ob_full_mark = $request->ob_full_mark;
        $exam_list->date = Carbon::today()->format('Y-m-d');
        $exam_list->start_time = $request->start_time;
        $exam_list->full_time = $request->full_time;
        $exam_list->schedule_id = $request->schedule_id;
        $exam_list->script_quantity = $request->script_quantity;

        $start_time = Carbon::parse($request->start_time);
        if ($start_time < Carbon::now()) {            
            $exam_list->invigilator_id = !$exam_list->invigilator_id ? $user->teachers->id : $exam_list->invigilator_id;
            $exam_list->status = $new == 'true' || $exam_list->status == 'setup' || !$exam_list->status ? 'setup' : $exam_list->status;
        } else {
            $exam_list->status = !$exam_list->status ? 'setup' : $exam_list->status;
        }
        // if (!$request->ob_full_mark) {
        //     $exam_list->status = 'completed';
        // }
        $exam_list->save();
                 
        $student_list = student::where('batch_id', $exam_list->batch_id)->where('status', 'present')->get();
        foreach ($student_list as $key => $student) {
            $std = student_exam_mark::firstOrCreate(
                ['student_id' =>  $student->id, 'date' => $exam_list->date, 'subject_id' => $request->subject_id],
                ['student_id' =>  $student->id, 'date' => $exam_list->date, 'subject_id' => $request->subject_id]
            );
        }
        
        return response()->json(['exam_list' => $exam_list]);
    }

    public function get_student_exam_entry_list($schedule_id, $batch_id, $date = Null){
        if (!$date) {
            $exam_list = exam_list::where('schedule_id', $schedule_id)->first();
        }
        else {
            $exam_list = exam_list::where('schedule_id', $schedule_id)->first();
        }
        if (!$exam_list) {
            return response()->json(['student_list' => []]);
        }
        $students = student::where('batch_id', $batch_id)->where('status', 'present')->select('id', 'reg_no', 'name', 'batch_id')->whereHas('exams', function($query) use ($exam_list){
            $query->whereDate('date', $exam_list->date)->where('subject_id', $exam_list->schedules->subject_id);
        })->with(array('exams' => function($query) use ($exam_list){
                $query->whereDate('date', $exam_list->date)->where('subject_id', $exam_list->schedules->subject_id);
        }))->orderBy('reg_no');

        if (!$date) {
            $students = $students->whereHas('todays_attendance', function($query){
                $query->where('attendance', true);
            })->get();
        } else {
            $students = $students->get();
        }

        return response()->json(['student_list' => $students]);
    }
    public function save_exam_mark(Request $request){
        $ids = [];
        $exam_list = exam_list::where('schedule_id', $request->student_mark_list[0]['schedule_id'])->first();
        if ($request->mark_type == 'mcq' && !$exam_list->ob_full_mark) {
            return response()->json(['ids' => []]);
        }
        if ($request->mark_type == 'cq' && !$exam_list->sub_full_mark) {
            return response()->json(['ids' => []]);
        }
        foreach ($request->student_mark_list as $key => $mark) {
            $std = student_exam_mark::whereDate('date', $exam_list->date)->where('subject_id', $exam_list->schedules->subject_id)->where('student_id', $mark['id'])->first();
            if ($request->mark_type == 'cq') {
                $std->sub_mark = $mark['mark'];
            }
            else {
                $std->ob_mark = $mark['mark'];
            }
            $std->save();
            array_push($ids, $mark['id']);
        }
        if (!$exam_list->sub_full_mark && $request->mark_type == 'mcq' && $request->entry_completed) {
            $exam_list->status = 'finish';
            $exam_list->save();
        }
        if (($request->mark_type == 'cq' || $request->mark_type == 'both')  && $request->entry_completed) {
            $exam_list->status = 'finish';
            $exam_list->save();
        }
        return response()->json(['ids' => $ids]);
    }

    public function save_invigilator_attendance(Request $request)
    {
        
        $random = random_number::first();
        if(!$random){
            $random = new random_number;
            $random->random_number = rand(1000, 10000);
            $random->save();
        }
        if ($random && $random->updated_at < Carbon::now()->subHours(2)->toDateTimeString()) {
            $random->random_number = rand(1000, 10000);
            $random->save();
        }

        if ($random->random_number != $request->authorise_code) {
            return response()->json('false', 422);
        }

        $user = Auth::guard('web')->user();
        // $batch_id = 17;
        $teacher_exam_list = exam_list::whereDate('date', Carbon::today())->where('invigilator_id', $user->teachers->id)->with('batches')->orderBy('start_time')->get();

        $start_time = Null;
        $end_time = Null;
        $batches = [];
        // return $teacher_exam_list;
        foreach ($teacher_exam_list as $key => $list) {
            $minutes = 0;
            $this_end_time = Carbon::parse($list->start_time)->addMinutes($list->full_time);
            $this_start_time = Carbon::parse($list->start_time);

            if (!$start_time) {
                $minutes = $list->full_time;                
            }
            // if this time start between last listed time and greater, will add eclapse time
            if ($start_time && $end_time && $this_start_time <= $start_time && $this_end_time > $end_time) {
                $diff = $this_end_time->diffInMinutes($end_time);
                $minutes = $diff;
            }

            // if start time is greater than last end time, it will separate time, will add whole time
            if ($start_time && $end_time && $this_start_time >= $end_time) {                
                $minutes = $list->full_time;
            }
            $start_time = Carbon::parse($this_start_time);
            $end_time = $this_end_time;
            $attendance = schedule_teacher_attendance::where('schedule_id', $list->schedule_id)->where('teacher_id', $user->teachers->id)->where('batch_id', $list->batch_id)->first();
            if (!$attendance) {
                $attendance = new schedule_teacher_attendance;
                $attendance->teacher_id = $user->teachers->id;
                $attendance->schedule_id = $list->schedule_id;
                $attendance->batch_id = $list->batch_id;
                $attendance->exam_list_id = $list->id;
                $attendance->type = 'invigilator';
            }
            $attendance->invigilator_minute = $minutes;
            $attendance->save();
        }
        $teacher_exam_list = exam_list::whereDate('date', Carbon::today())->where('invigilator_id', $user->teachers->id)->where('status', '!=', 'scripter_taken')->update(['status' => 'invigilator_done']);
    }

    public function get_scripter_dashboard_exam()
    {
        $user = Auth::guard('web')->user();
        $exam_list = exam_list::where('scripter_id', $user->teachers->id)->where('status', '!=', 'done')->with('batches.echelons', 'schedules.subjects')->whereDate('date', '>', '2020-12-31')
        ->with(array('schedules.questions.exam_question_types' => function($query){
                $query->where('question_type', 'cq')->with('questions');
        }))->get();
        return response()->json(['exam_list' => $exam_list]);
    }

    public function take_script(Request $request)
    {
        if (!$request->schedule_id) {
            return 'false';
        }        

        $user = Auth::guard('web')->user();
        $exam_list = exam_list::where('schedule_id', $request->schedule_id)->first();
        if ($exam_list) {
            $exam_list->status = 'scripter_taken';
            $exam_list->scripter_id = $user->teachers->id;
            $exam_list->save();
        }
    }

    public function submit_script($id, $code)
    {
        $random = random_number::first();
        if(!$random){
            $random = new random_number;
            $random->random_number = rand(1000, 10000);
            $random->save();
        }
        if ($random && $random->updated_at < Carbon::now()->subHours(2)->toDateTimeString()) {
            $random->random_number = rand(1000, 10000);
            $random->save();
        }

        if ($random->random_number != $code) {
            return response()->json('false', 422);
        }

        $user = Auth::guard('web')->user();
        $exam_list = exam_list::where('id', $id)->first();
        $exam_list->status = 'done';
        $exam_list->save();

        $attendance = schedule_teacher_attendance::where('schedule_id', $exam_list->schedule_id)->where('teacher_id', $user->teachers->id)->where('batch_id', $exam_list->batch_id)->where('type', 'scripter')->first();
        if (!$attendance) {
            $attendance = new schedule_teacher_attendance;
            $attendance->teacher_id = $user->teachers->id;
            $attendance->schedule_id = $exam_list->schedule_id;
            $attendance->batch_id = $exam_list->batch_id;
            $attendance->exam_list_id = $exam_list->id;
            $attendance->type = 'scripter';
        }
        $attendance->script_quantity = $exam_list->script_quantity;
        $attendance->save();
    }

    public function get_invigilator_minutes_summary($date_from=Null, $date_to = Null)
    {
        if (!$date_to) {
            $date_to = '2050-01-01';
        }
        if (!$date_from) {
            $date_from = Carbon::today();
        }
        $user = Auth::guard('web')->user();
        $attendance_list = schedule_teacher_attendance::whereHas('schedules', function($query) use ($date_from, $date_to){
            $query->whereDate('date', '>=', $date_from)->whereDate('date', '<=', $date_to);
        })->with('schedules.subjects', 'batches')->where('type', 'invigilator')->where('teacher_id', $user->teachers->id)->get();

        return response()->json(['attendance_list' => $attendance_list]);
    }

    public function get_script_summary($date_from, $date_to = Null)
    {
        $user = Auth::guard('web')->user();
        if (!$date_to) {
            $date_to = '2050-01-01';
        }
        $attendance_list = schedule_teacher_attendance::whereHas('schedules', function($query) use ($date_from, $date_to){
            $query->whereDate('date', '>=', $date_from)->whereDate('date', '<=', $date_to);
        })->with('schedules.subjects', 'batches')->where('type', 'scripter')->where('teacher_id', $user->teachers->id)->get();

        return response()->json(['attendance_list' => $attendance_list]);
    }

    // question
    public function question_makable_list_by_date($date){
        $user = Auth::guard('web')->user();
        $question_list = exam_question_list::whereHas('schedules', function($query) use ($date, $user){
            $query->whereDate('date', '>=', $date)->where('teacher_id', $user->teachers->id)->orderBy('date');
        })->with('schedules.batches', 'subjects.echelons', 'chapters')->orderBy('created_at', 'asc')->get();
        
        $exam_alert = daily_schedule::whereHas('teachers', function($query) use ($user){
            $query->where('teacher_id', $user->teachers->id)->whereDate('date', '>=', Carbon::today())->whereDate('date', '<=', Carbon::today()->addDays(10))->whereNull('exam_question_list_id');
        })->with('subjects.echelons')->orderBy('date')->get();

        return response()->json(['question_list' => $question_list, 'exam_alert' => $exam_alert]);
    }

    public function exam_schedule_by_class_date($echelon_id) {
        $schedule_list = daily_schedule::whereHas('batches', function($query) use ($echelon_id){
            $query->where('echelon_id', $echelon_id);
        })->whereDate('date', '>=', Carbon::today())->where('schedule_type', 'exam')->with('subjects', 'batches')->get();
        return response()->json(['schedule_list' => $schedule_list]);
    }

    public function save_question_list(Request $request){
        // return response()->json($request->all(), 422);
        $question_list = new exam_question_list;
        $question_list->subject_id = $request->subject_id;
        $question_list->save();

        foreach ($request->schedule_ids as $key => $schdeule) {
            $daily = daily_schedule::where('id', $request->schedule_ids)->first();
            $daily->exam_question_list_id = $question_list->id;
            $daily->save();
        }
        return response()->json(['question_list' => exam_question_list::where('id', $question_list->id)->with('schedules.batches', 'subjects.echelons')->first()]);
    }

    public function edit_question_list(Request $request, $id){
        $question_list = exam_question_list::find($id);
        foreach ($request->schedule_ids as $key => $schdeule) {
            $daily = daily_schedule::where('id', $request->schedule_ids)->first();
            $daily->exam_question_list_id = $question_list->id;
            $daily->save();
        }
        return response()->json(['question_list' => exam_question_list::where('id', $question_list->id)->with('schedules.batches', 'subjects.echelons')->first()]);
    }

    public function question_delete($id)
    {
        exam_question_list::find($id)->delete();
    }

    public function import_exam_mark(Request $request){
        if ($request->hasFile('exam_excel_file')) {
            // return 'true';
            $import = new ExamImport($request->schedule_data);
            Excel::import($import, $request->file('exam_excel_file'));
            return response()->json(['unfound' => $import->getRowCount()]);
        }
        return response()->json(['error' => 'No file found'], 422);
    }


}
