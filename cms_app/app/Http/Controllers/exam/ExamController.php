<?php

namespace App\Http\Controllers\exam;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\User;
use App\Model\Admin\daily_schedule;
use App\Model\Admin\exam_list;
use App\Model\Admin\question;
use App\Model\Admin\question_tag;
use App\Model\Admin\online_exam_subject;
use App\Model\Admin\question_option;
use App\Model\Admin\exam_question;
use App\Model\Admin\exam_question_type;
use App\Model\Admin\exam_question_list;
use App\Model\Admin\student_exam_mark;
use App\Model\Admin\student;
use App\Model\Admin\combine_subject;
use App\Model\Admin\combine_subject_rule;
use App\Model\Admin\schedule_teacher_attendance;
use Carbon\Carbon;
use Purifier;
use DB;
use Cache;
use App\Http\Controllers\sms\sms_trait;

// Contains
// User Login Success Home Page
// User Dashboard

class ExamController extends Controller
{
    
    use sms_trait;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }
    
    public function get_online_exam($date = null)
    {                        
        if ($date == null) {
            $date = Carbon::today();
        }
        $exam_list = exam_list::with('schedules.echelons', 'batches', 'scripters', 'invigilators', 'schedules.subjects')->where('status', '!=', 'done')->whereHas('schedules', function($query){
            $query->where('schedule_type', 'online_exam');
        })->get();
        return response()->json(['exam_list' => $exam_list]);
    }

    public function get_exam_question_list($date = null)
    {                        
        if ($date == null) {
            $date = Carbon::today();
        }
        $question_list = exam_question_list::with('echelons', 'subjects', 'assign_exams')->get();
        return response()->json(['question_list' => $question_list]);
    }

    public function get_exam_question_type($id)
    {                        
        $question_list = exam_question_type::where('id', $id)->with('questions')->first();
        return response()->json(['question_list' => $question_list]);
    }

    public function get_question_exam_list($date = null)
    {                        
        if (!$date) {
            $date = Carbon::today();
        } else {
            Carbon::parse($date);
        }

        $exam_list = exam_list::with('batches', 'schedules.subjects')->where('status', '!=', 'done')->whereDate('date', '>=', $date)->whereDate('date', '<=', Carbon::parse($date)->addDays(7))->get();
        return response()->json(['exam_list' => $exam_list]);
    }

    public function exam_question_add(Request $request)
    {
        // return response()->json($request->all(), 422);
        $this->validate($request, [
           'question_text'=> 'required',
           'branch_id' => 'required',           
           'language' => 'required',
           'question_length' => 'required',
           'question_mark' => 'required',
           'question_tag' => 'required'
        ]);
        DB::beginTransaction();
        try {
            $subtags = $request->question_subtag ? $request->question_subtag : [];
            $question_tag = isset($request->question_tag['text']) ? $request->question_tag['text'] : $request->question_tag;
            $tag = question_tag::firstOrCreate(['name' => $question_tag], ['name' => $question_tag]);
            foreach ($request->question_text as $key => $q) {
                $question = new question;
                $question->question_type = 'mcq';
                $question->question_mark = $request->question_mark;
                $question->question_length = $request->question_length;
                $question->subtag = $subtags;
                $question->subject_id = $request->subject_id;
                $question->question_tag_id = $tag->id;
                $question->topic = $request->topic;
                $question->language = $request->language;
                $question->question_text = Purifier::clean($q['question_part']);        
                $question->save();
                foreach ($q['options'] as $key1 => $option) {
                    $opt = question_option::create(['question_id' => $question->id, 'option_text' => $option, 'answer' => $request->short_answer[$key]['text'] == $key1 + 1 ? 1 : 0]);
                    if ($request->short_answer[$key]['text'] == $key1 + 1) {
                        $answer_id = $opt->id;
                    }
                }
                question::where('id', $question->id)->update(['answer_option_id' => $answer_id]);
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
        }
    }

    public function exam_question_edit(Request $request, $id)
    {
        // return response()->json($request->all(), 422);
        $this->validate($request, [
           'question_text'=> 'required', 
           'question_length' => 'required',
           'question_mark' => 'required',
           'question_tag' => 'required',
           'answer' => 'required'
        ]);
        DB::beginTransaction();
        try {
            $subtags = $request->question_subtag ? $request->question_subtag : [];
            $question_tag = isset($request->question_tag['text']) ? $request->question_tag['text'] : $request->question_tag;
            $tag = question_tag::firstOrCreate(['name' => $question_tag], ['name' => $question_tag]);
            foreach ($request->question_text as $key => $q) {
                $question = question::find($id);
                $question->question_type = 'mcq';
                $question->question_mark = $request->question_mark;
                $question->question_length = $request->question_length;
                $question->subtag = $subtags;
                $question->subject_id = $request->subject_id;
                $question->question_tag_id = $tag->id;
                $question->topic = $request->topic;
                $question->language = $request->language;
                $question->question_text = Purifier::clean($q['question_part']);
                $question->save();
                question_option::where('question_id', $id)->delete();
                foreach ($q['options'] as $key1 => $option) {
                    $opt = question_option::Create(['question_id' => $question->id, 'option_text' => $option, 'answer' => $request->answer == $key1 + 1 ? 1 : 0]);
                    if ($request->answer == $key1 + 1) {
                        $answer_id = $opt->id;
                    }
                }
                question::where('id', $id)->update(['answer_option_id' => $answer_id]);
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
        }
    }

    public function scheduled_exam_edit(Request $request, $id)
    {                        
        if (!$request->exam_lists['id']) {
            $exam_list = new exam_list;
        } else {
            $exam_list = exam_list::find($request->exam_lists->id);
        }
        $exam_list->sub_full_mark = $request->sub_full_mark;
        $exam_list->ob_full_mark = $request->ob_full_mark;
        // $exam_list->script_quantity = $request->script_quantity;
        $exam_list->invigilator_id = $request->invigilator_id;
        $exam_list->scripter_id = $request->scripter_id;
        // 
        $start_time = Carbon::parse($exam_list->start_time);
        $full_time = $exam_list->full_time;
        // 
        $exam_list->start_time = $request->start_time;
        $exam_list->full_time = $request->full_time;
        $exam_list->save();

        // if ($exam_list->invigilator_id && ($start_time != Carbon::parse($request->start_time) || $full_time != $request->full_time)) {            
        //     $this->change_invigilator_attendance($exam_list->invigilator_id, $exam_list->date);
        // }

        return response()->json(['exam_list' => daily_schedule::with('subjects', 'teachers', 'batches', 'echelons', 'exam_lists')->where('schedule_type', 'exam')->where('id', $request->id)->first()]);
    }

    public function get_exam_list($date = null)
    {                        
        $exam_list = exam_list::with('schedules.echelons', 'batches', 'scripters', 'invigilators', 'schedules.subjects')->where('status', '!=', 'done')->get();
        return response()->json(['exam_list' => $exam_list]);
    }

    public function get_exam_edit_list($date_from, $date_to)
    {        
        $exam_list = daily_schedule::with('batches.echelons', 'subjects', 'exam_lists.scripters', 'exam_lists.invigilators')->WhereDate('date', '>=', $date_from)->WhereDate('date', '<=', $date_to)->where('schedule_type', 'exam')->orderBy('date', 'desc')->get();
        return response()->json(['exam_list' => $exam_list]);
    }

    public function exam_list_edit(Request $request, $id)
    {                        
        $exam_list = exam_list::where('schedule_id', $request->id)->first();
        if ($exam_list && $exam_list->status != 'finish') {
            $exam_list->status = 'setup';
        }
        if (!$exam_list) {
            $exam_list = new exam_list;
            $exam_list->status = 'setup';
        }
        $exam_list->batch_id = $request->batch_id;
        $exam_list->schedule_id = $request->id;
        $exam_list->sub_full_mark = $request->sub_full_mark;
        $exam_list->ob_full_mark = $request->ob_full_mark;
        $exam_list->invigilator_id = $request->invigilator_id;
        $exam_list->scripter_id = $request->scripter_id;
        $exam_list->date = $request->date;
        $exam_list->start_time = $request->start_time;
        $exam_list->full_time = $request->full_time;
        $exam_list->exam_type_id = $request->exam_type_id;
        $exam_list->save();

        $student_list = student::where('batch_id', $exam_list->batch_id)->where('status', 'present')->get();
        // return $student_list;
        foreach ($student_list as $key => $student) {
            $std = student_exam_mark::firstOrCreate(
                ['student_id' =>  $student->id, 'date' => $exam_list->date, 'subject_id' => $request->subject_id],
                ['student_id' =>  $student->id, 'date' => $exam_list->date, 'subject_id' => $request->subject_id]
            );
        }
        return response()->json(['exam_list' => daily_schedule::with('batches.echelons', 'subjects', 'exam_lists.scripters', 'exam_lists.invigilators')->where('id', $request->id)->first()]);
    }

    protected function change_invigilator_attendance($user_id, $date)
    {                
        $teacher_exam_list = exam_list::whereDate('date', $date)->where('invigilator_id', $user_id)->with('batches')->orderBy('start_time')->get();

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
            $attendance = schedule_teacher_attendance::where('schedule_id', $list->schedule_id)->where('teacher_id', $user_id)->where('batch_id', $list->batch_id)->first();
            if (!$attendance) {
                $attendance = new schedule_teacher_attendance;
                $attendance->teacher_id = $user_id;
                $attendance->schedule_id = $list->schedule_id;
                $attendance->batch_id = $list->batch_id;
                $attendance->exam_list_id = $list->id;
                $attendance->type = 'invigilator';
            }
            $attendance->invigilator_minute = $minutes;
            $attendance->save();            
        }
    }

    public function save_teacher_singature($id, $signature_type)
    {                        
        $exam_list = exam_list::find($id);
        if ($signature_type == 'invigilator') {                        
            $this->change_invigilator_attendance($exam_list->invigilator_id, $exam_list->date);
        }
        else {
            $attendance = schedule_teacher_attendance::where('schedule_id', $exam_list->schedule_id)->where('teacher_id', $exam_list->scripter_id)->where('batch_id', $exam_list->batch_id)->where('type', 'scripter')->first();
            if (!$attendance) {
                $attendance = new schedule_teacher_attendance;
                $attendance->teacher_id = $exam_list->scripter_id;
                $attendance->schedule_id = $exam_list->schedule_id;
                $attendance->batch_id = $exam_list->batch_id;
                $attendance->exam_list_id = $exam_list->id;
                $attendance->type = 'scripter';
            }
            $attendance->script_quantity = $exam_list->script_quantity;
            $attendance->save();
            $exam_list->status = 'done';
            $exam_list->save();
        }            
        
    }

    public function exam_list_delete($id)
    {                        
        $exam_list = exam_list::find($id);
        $exam_list->delete();
    }
    
    public function save_exam_setup_list(Request $request)
    {                
        DB::beginTransaction();
        try {
            foreach ($request->batch as $key => $batch) {                
                $new = 'false';    

                $exam_list = exam_list::where('schedule_id', $request->id)->where('batch_id', $batch['value'])->first();
                if (!$exam_list) {
                    $exam_list = new exam_list;
                    $new = 'true';
                }
                $exam_list->sub_full_mark = $request->sub_full_mark;
                $exam_list->ob_full_mark = $request->ob_full_mark;
                $exam_list->full_time = $request->full_time;
                $exam_list->start_time = $request->start_time;
                if (strpos($request->script_quantity, ',') == false) {
                    $exam_list->script_quantity = $request->script_quantity;
                } else {
                    $exam_list->script_quantity = explode(',', $request->script_quantity)[$key];
                }
                $exam_list->schedule_id = $request->id;
                $exam_list->batch_id = $batch['value'];
                $exam_list->date = $request->date;
                $exam_list->date_limit = Carbon::parse($request->date)->addDays(8)->format('Y-m-d');
                $exam_list->status = !$exam_list || !$exam_list->status ? 'setup' : $exam_list->status;
                $exam_list->save();

                if ($new == 'true') {            
                    $student_list = student::where('batch_id', $exam_list->batch_id)->where('status', 'present')->get();
                    foreach ($student_list as $key => $student) {
                        $std = new student_exam_mark;
                        $std->student_id = $student->id;
                        $std->date = $exam_list->date;
                        $std->subject_id = $exam_list->schedules->subject_id;
                        // $std->exam_list_id = $exam_list->id;
                        $std->save();
                    }
                }
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
        }
    }

    public function get_student_exam_entry_list($schedule_id, $batch_id){
        $exam_list = exam_list::where('batch_id', $batch_id)->where('schedule_id', $schedule_id)->with('schedules')->first();
        if (!$exam_list) {
            return response()->json(['student_list' => []]);
        }
        $students = student::where('batch_id', $batch_id)->where('status', 'present')->select('id', 'reg_no', 'name', 'batch_id')->whereHas('exams', function($query) use ($exam_list){
            $query->where('exam_list_id', $exam_list->id);
        })->with(array('exams' => function($query) use ($exam_list){
                $query->where('exam_list_id', $exam_list->id);
        }))->orderBy('reg_no')->get();

        return response()->json(['student_list' => $students]);
    }

    public function get_all_student_exam_entry_list($id){
        $exam_list = exam_list::where('id', $id)->with('schedules')->first();
        if (!$exam_list) {
            return response()->json(['student_list' => []]);
        }
        $students = student::where('batch_id', $exam_list->batch_id)->where('status', 'present')->select('id', 'reg_no', 'name', 'batch_id')->whereHas('exams', function($query) use ($exam_list){
            $query->whereDate('date', $exam_list->date)->where('subject_id', $exam_list->schedules->subject_id);
        })->with(array('exams' => function($query) use ($exam_list){
                $query->whereDate('date', $exam_list->date)->where('subject_id', $exam_list->schedules->subject_id);
        }))->orderBy('reg_no')->get();
        return response()->json(['student_list' => $students, 'exam_list' => $exam_list]);
    }

    public function save_exam_mark(Request $request){
        // return $request->all();
        $ids = [];
        foreach ($request->student_mark_list as $key => $mark) {
            $std = student_exam_mark::find($mark['id']);
            $std->sub_mark = $mark['sub_mark'];
            $std->ob_mark = $mark['ob_mark'];
            $std->save();
            array_push($ids, $mark['student_id']);
        }
        if ($request->status) {
            $exam_list = exam_list::where('id', $request->exam_list_id)->update(['status' => 'finish']);
        }
        // $exam_list->save();
        return response()->json(['ids' => $ids]);
    }

    public function finish_mark_entry($id){
        exam_list::where('id', $id)->update(['status' => 'finish']);
    }

    public function edit_list_exam_mark(Request $request){
        $ids = [];
        $exam_list = exam_list::where('batch_id', $request->student_mark_list[0]['batch_id'])->where('schedule_id', $request->schedule_id)->first();
        foreach ($request->student_mark_list as $key => $mark) {
            $std = student_exam_mark::where('exam_list_id', $exam_list->id)->where('student_id', $mark['id'])->first();
            $std->sub_mark = $mark['sub_mark'];
            $std->ob_mark = $mark['ob_mark'];
            $std->save();
            array_push($ids, $mark['id']);
        }
        return response()->json(['ids' => $ids]);
    }

    public function done_exam_list($id){        
        $exam_list = exam_list::find($id);
        $exam_list->status = 'done';
        $exam_list->save();        
    }

    public function send_exam_sms($id, $destination_number)
    {
        // return response()->json(['sms_report' => [], 'balance' => "Exam SMS Server no ready yet"]);
        $exam_list = exam_list::where('schedule_id', $id)->with('schedules', 'exam_types')->first();
        if ($exam_list->sms) {
            return response()->json('false', 422);
        }
        $exam_mark = student_exam_mark::where('subject_id', $exam_list->schedules->subject_id)->whereDate('date', $exam_list->date)->whereHas('students.batches', function($query) use($exam_list){
            $query->where('batch_id', $exam_list->batch_id);
        })
        ->with('students', 'subjects')->where(function($query){
            $query->WhereNotNull('sub_mark')->orWhereNotNull('ob_mark');
        })->get();

        $exam_mark_list = [];
        foreach ($exam_mark as $mark) {
            array_push($exam_mark_list, $mark);
        }
        usort($exam_mark_list, function($a, $b) {
            return ($b->sub_mark + $b->ob_mark) - ($a->sub_mark + $a->ob_mark);
        }); 
        // create sms json array
        // $json_array = [];
        $student = [];
        // $highest = 0;
        foreach ($exam_mark_list as $key => $mark) {

            // Towhidul Islam obtains (Marks) out of (Total Marks). CQ: 00, MCQ: 00. BT-GEC
            $obtain_mark = "";
            if ($exam_list->sub_full_mark>0 && $exam_list->ob_full_mark>0) {
                $obtain_mark = $obtain_mark . ' CQ: ' . $mark->sub_mark . ', MCQ: ' . $mark->ob_mark;
            }

            $sms_text = $mark->students->name . ' obtains ' . ($mark->sub_mark + $mark->ob_mark) . ' out of ' . ($exam_list->sub_full_mark + $exam_list->ob_full_mark) . ' in ' . $mark->subjects->name . ' (' . Carbon::parse($mark->date)->format('d M') . ').' . $obtain_mark . '. BT-GEC.';

            $mark->students->message = $sms_text;
            array_push($student, $mark->students);
        }

        
        // return response()->json(['sms_report' => $student], 422);
        $sms_report = $this->gw_many_to_many($student, 'exam_sms', $destination_number);
        // $balance = $this->onnorokom_balance_for_list();
        exam_list::where('schedule_id', $id)->update(['sms' => 1]);
        return response()->json(['sms_report' => $sms_report['sms_report'], 'balance' => $sms_report['balance'], 'sms' => 'sms']);
        // return response()->json(['sms_report' => $sms_report, 'balance' => $balance, 'sms' => 'sms']);
    }

    public function edit_exam_mark(Request $request){
        $ids = [];
        $error_reg = [];
        $not_found = [];
        foreach ($request->student_mark_list as $key => $mark) {
            $student = student::where('reg_no', $mark['reg_no'])->where('status', 'present')->first();
            if (!$student) {
                array_push($not_found, $mark['reg_no']);
            }
            else {                
                $exam_list = exam_list::where('schedule_id', $mark['schedule_id'])->with('schedules')->first();                
                if (!$exam_list) {
                    array_push($not_found, $mark['reg_no']);
                }
                else {
                    $std = student_exam_mark::where('subject_id', $exam_list->schedules->subject_id)->where('student_id', $student->id)->whereDate('date', $exam_list->date)->first();
                    if (!$std) {
                        array_push($not_found, $mark['reg_no']);
                    }
                    if ($exam_list->sub_full_mark < $mark['sub_mark'] || $exam_list->ob_full_mark < $mark['ob_mark']) {
                        array_push($error_reg, $mark['reg_no']);
                    }
                    else {                
                        if ($exam_list->sub_full_mark) {                
                            $std->sub_mark = $mark['sub_mark'];
                        }
                        if ($exam_list->ob_full_mark) { 
                            $std->ob_mark = $mark['ob_mark'];
                        }
                        $std->save();
                    }
                }
            }
            array_push($ids, $mark['schedule_id']);
        }                
        return response()->json(['ids' => $ids, 'error_reg' => $error_reg, 'not_found' => $not_found]);
    }

    // =================================== merit list =============================================

    public function get_mark_list1(Request $request){
        $exam_list = exam_list::whereDate('date', '>=' , $request->date_from)->whereDate('date', '<=' , $request->date_to)->where('status', 'finish')->with('schedules');
        if ($request->exam_type) {
            $exam_list = $exam_list->where('exam_type_id', $request->exam_type);
        }
        if ($request->batches) {
            $batch = [];
            foreach ($request->batches as $key => $bt) {
                array_push($batch, $bt['value']);
            }
            $exam_list = $exam_list->whereIn('batch_id', $batch);
        }
        $exam_list = $exam_list->get();
        
        $subject = [];
        $subject_by_exam_list = [];
        foreach ($exam_list as $key => $exam) {
            if (!in_array($exam->schedules->subject_id, $subject)) {
                array_push($subject, $exam->schedules->subject_id);
                array_push($subject_by_exam_list, $exam);
            }
        }

        $mark_list = student_exam_mark::whereDate('date', '>=' , $request->date_from)->whereDate('date', '<=' , $request->date_to)->whereIn('subject_id', $subject)->whereHas('students', function($query) use ($request, $batch){
            $query->whereIn('batch_id', $batch);
        })->with('students.batches')->get();

        if (sizeof($mark_list)==0) {
            return response()->json(['mark_list' => [], 'mark_item' => new \stdClass()]);
        }

        if ($request->combine_subject_rule_id) {
            // make a subject list accroding to combination
            // 
        }

        // mark arrangement
        $mark_object = new \stdClass();
        $mark_object->subject_size = sizeof($subject_by_exam_list);
        $mark_object->exam_types = $subject_by_exam_list[0]->exam_types;

        $subjects = [];
        foreach ($subject_by_exam_list as $key => $list) {
            $ob = new \stdClass();
            $ob->subject_id = $list->schedules->subject_id;
            $ob->subject_name = $list->schedules->subjects->name;
            $ob->exam_list_id = $list->id;
            $ob->batch_id = $list->batch_id;
            $ob->date = $list->date;
            array_push($subjects, $ob);          
        }
        

        $mark_object->subjects = $subjects;

        $pass_mark = 0.33;
        $new_mark_list = [];
        $fail_condition = 'combine';
        
        foreach ($mark_list as $key => $mark) {
            $mark_total = (int) $mark->sub_mark + (int) $mark->ob_mark;   

            // create subjects mark array object for remain same with subjects sequence, array name subjects
            $sub_marks = [];
            foreach ($subject_by_exam_list as $key => $list) {
                if ($mark_object->subject_size>3) {
                    $sub_object = new \stdClass();
                    $sub_object->subject_id = $list->schedules->subject_id; 
                    $sub_object->mark = Null;
                    $sub_object->date = $list->date;
                    $sub_object->index = -1;        
                    array_push($sub_marks, $sub_object);
                }
                else {
                    $sub_object = new \stdClass();
                    $sub_object->subject_id = $list->schedules->subject_id; 
                    $sub_object->sub_mark = Null;
                    $sub_object->ob_mark = Null;
                    $sub_object->date = $list->date;
                    array_push($sub_marks, $sub_object);
                }            
            }
            foreach ($subject_by_exam_list as $s_list) {
                if ($s_list->schedules->subject_id == $mark->subject_id) {
                    $related_exam_list = $s_list;
                }
            }
            // return response()->json($related_exam_list, 422);
            
            // create object for mark list
            $object = new \stdClass();
            $object->student_id = $mark->student_id;
            $object->reg_no = $mark->students->reg_no;
            $object->student_name = $mark->students->name;
            $object->batch_name = $mark->students->batches->name;
            $object->photo = $mark->students->photo;
            $object->rank_status = Null;
            if (($related_exam_list->sub_full_mark && !$mark->sub_mark) ||($related_exam_list->ob_full_mark && !$mark->ob_mark)) {
                $object->rank_status = 'Absent';
            }
            
            $full_mark_total = (int) $related_exam_list->sub_full_mark + (int) $related_exam_list->ob_full_mark; 
            // calculate pass or fail
            if ($mark->sub_mark && $mark->sub_mark / $related_exam_list->sub_full_mark < $pass_mark) {
                $object->rank_status = 'Fail(S)';
            }
            if ($mark->ob_mark && $mark->ob_mark / $related_exam_list->ob_full_mark < $pass_mark) {
                $object->rank_status = 'Fail(O)';               
            }

            if($fail_condition == 'combine'){
                $mark_sub = $mark->sub_mark ? $mark->sub_mark : 0;
                $mark_ob = $mark->ob_mark ? $mark->ob_mark : 0;
                if(($mark_sub + $mark_ob) / ($related_exam_list->sub_full_mark + $related_exam_list->ob_full_mark) < $pass_mark ){
                    $object->rank_status = 'Fail';
                } else {
                    $object->rank_status = Null;
                }
            }

            foreach ($sub_marks as $m => $list) {
                if($list->subject_id == $related_exam_list->schedules->subject_id) {
                    $mark_index = $m;
                }; 
            }
            // Criteria for above 3 subjects && below or equal 3 subjects
            if ($mark_object->subject_size>3) {
                $sub_marks[$mark_index]->mark = $mark_total;
            }
            else {
                $sub_marks[$mark_index]->sub_mark = $mark->sub_mark;
                $sub_marks[$mark_index]->ob_mark = $mark->ob_mark;
            }
            $object->subjects = $sub_marks;            
            $object->total = $mark_total;
            $object->gpa = $this->get_gpa($mark_total, $full_mark_total);

            // find index of student_id, if -1 push object, else assign value
            $index = -1;
            foreach($new_mark_list as $i => $list) {
                if($list->student_id == $mark->student_id) {
                    $index = $i;
                };
            }
            if ($index == -1) {
                array_push($new_mark_list, $object);
            }            
            else {
                
                // Criteria for above 3 subjects && below or equal 3 subjects
                if ($mark_object->subject_size>3) {
                    $new_mark_list[$index]->subjects[$mark_index]->mark = $mark_total;
                    $new_mark_list[$index]->total = $new_mark_list[$index]->total + $mark_total;
                    $new_mark_list[$index]->gpa = $new_mark_list[$index]->gpa + $this->get_gpa($mark_total, $full_mark_total);
                    $new_mark_list[$index]->subjects[$mark_index]->index = $mark;
                }
                else {
                    $new_mark_list[$index]->subjects[$mark_index]->sub_mark =  $mark->sub_mark;
                    $new_mark_list[$index]->subjects[$mark_index]->ob_mark =  $mark->ob_mark;
                    $new_mark_list[$index]->total = $new_mark_list[$index]->total + $mark->sub_mark + $mark->ob_mark;
                    $new_mark_list[$index]->gpa = $new_mark_list[$index]->gpa + $this->get_gpa($mark_total, $full_mark_total);
                }
                // calculate pass or fail
                

                if ($mark->sub_mark && $mark->sub_mark / $related_exam_list->sub_full_mark < $pass_mark) {
                    $new_mark_list[$index]->rank_status = 'Fail(S)';
                }
                if ($mark->ob_mark && $mark->ob_mark / $related_exam_list->ob_full_mark < $pass_mark) {
                    $new_mark_list[$index]->rank_status = 'Fail(O)';             
                }

                if($fail_condition == 'combine'){
                    $mark_sub = $mark->sub_mark ? $mark->sub_mark : 0;
                    $mark_ob = $mark->ob_mark ? $mark->ob_mark : 0;
                    if(($mark_sub + $mark_ob) / ($related_exam_list->sub_full_mark + $related_exam_list->ob_full_mark) < $pass_mark ){
                        $new_mark_list[$index]->rank_status = 'Fail';
                    } else {
                        $new_mark_list[$index]->rank_status = Null;
                    }
                }
            }
        } 

        usort($new_mark_list, function($a, $b) {
            return $b->total - $a->total;
        }); 

        $last_total = 0;
        $last_rank = 0;
        foreach ($new_mark_list as $key => $list) {
            if (!$list->rank_status && $list->total == $last_total) {
                $list->rank = $last_rank;
                $last_rank = $list->rank;
                $last_total = $list->total;
            }
            if (!$list->rank_status && $list->total != $last_total) {
                $list->rank = $last_rank + 1;
                $last_rank = $list->rank;
                $last_total = $list->total;
            }
        }  

        // usort($new_mark_list, function($a, $b) {
        //     return $a->reg_no - $b->reg_no;
        // }); 
        // return response()->json($new_mark_list, 422);
        return response()->json(['mark_list' => $new_mark_list, 'mark_item' => $mark_object]);
    }

    public function get_mark_list(Request $request){
        $exam_list = exam_list::whereDate('date', '>=' , $request->date_from)->whereDate('date', '<=' , $request->date_to)->where('status', 'finish')->with('schedules');
        if ($request->exam_type) {
            $exam_list = $exam_list->where('exam_type_id', $request->exam_type);
        }
        if ($request->batches) {
            $batch = [];
            foreach ($request->batches as $key => $bt) {
                array_push($batch, $bt['value']);
            }
            $exam_list = $exam_list->where('batch_id', $batch[0]);
        }
        $exam_list = $exam_list->get();

        // return response()->json(['mark_list' => $exam_list], 422);
        
        $subject = [];
        $subject_by_exam_list = [];
        $subject_list = [];
        $subject_combine_array = [];

        if ($request->combine_subject_rule_id) {
            $combine_subject_rule = combine_subject_rule::where('id', $request->combine_subject_rule_id)->first();
            foreach ($combine_subject_rule->combine_subjects as $key => $combine_subject) {
                $first_subject_id = Null;
                $second_subject_id = Null;
                foreach($exam_list as $key1 => $list){
                    if($list->schedules->subject_id == $combine_subject->first_subject_id){
                        $first_subject_id = $list->schedules->subject_id;
                    }
                    if($list->schedules->subject_id == $combine_subject->second_subject_id){
                        $second_subject_id = $list->schedules->subject_id;
                    }
                }
                if($first_subject_id && $second_subject_id){
                    $sub_object = new \StdClass;
                    $sub_object->subject_name = $combine_subject->subject_name;
                    $sub_object->first_subject_id = $combine_subject->first_subject_id;
                    $sub_object->second_subject_id = $combine_subject->second_subject_id;
                    $sub_object->first_sub_mark = 0;
                    $sub_object->second_sub_mark = 0;
                    $sub_object->full_mark = $combine_subject->full_mark;
                    $sub_object->combine_subject_no = 2;
                    array_push($subject_list, $sub_object);
                    array_push($subject_combine_array, $combine_subject->first_subject_id);
                    array_push($subject_combine_array, $combine_subject->second_subject_id);
                    array_push($subject, $combine_subject->first_subject_id);
                    array_push($subject, $combine_subject->second_subject_id);
                }
            }
        }

        foreach($exam_list as $key => $list){
            if(!in_array($list->schedules->subject_id, $subject_combine_array)){
                $sub_object = new \StdClass;
                $sub_object->subject_name = $list->schedules->subjects->name;
                $sub_object->first_subject_id = $list->schedules->subject_id;
                $sub_object->second_subject_id = Null;
                $sub_object->first_sub_mark = 0;
                $sub_object->second_sub_mark = Null;
                $sub_object->combine_subject_no = 1;
                $sub_object->full_mark = $list->sub_full_mark + $list->ob_full_mark;
                array_push($subject_list, $sub_object);
                array_push($subject, $list->schedules->subject_id);
            }
        }
        // return response()->json(['mark_list' => $subject_list], 422);

        $student_with_mark = student::whereIn('batch_id', $batch)->whereHas('exams', function($query) use($subject, $request){
            $query->whereDate('date', '>=' , $request->date_from)->whereDate('date', '<=' , $request->date_to)->whereIn('subject_id', $subject);
        })->with(array('exams' => function($query) use ($subject, $request){
                $query->whereDate('date', '>=' , $request->date_from)->whereDate('date', '<=' , $request->date_to)->whereIn('subject_id', $subject);
        }))->select('id', 'name', 'reg_no', 'photo', 'optional_id')->with('batches')->get();

        

        if (sizeof($student_with_mark)==0) {
            return response()->json(['mark_list' => [], 'mark_item' => new \stdClass()]);
        }

        $pass_mark = 0.33;
        $new_mark_list = [];
        $fail_condition = 'combine';

        foreach ($student_with_mark as $key1 => $student) {
            
            $subjects = [];
            foreach($subject_list as $key => $list){
                $sub_object = new \StdClass;
                $sub_object->subject_name = $list->subject_name;
                $sub_object->first_subject_id = $list->first_subject_id;
                $sub_object->second_subject_id = $list->second_subject_id;
                $sub_object->first_sub_mark = 0;
                $sub_object->second_sub_mark = Null;
                $sub_object->full_mark = $list->full_mark;
                $sub_object->combine_subject_no = $list->combine_subject_no;
                array_push($subjects, $sub_object);
            }
            $student->subjects = $subjects;

            $total_gpa = 0;
            $rank_status = Null;
            $total_mark = 0;
            $has_optional = 0;
            foreach ($student->subjects as $key2 => $student_subject) {
                $subject_total = 0;
                $optional = 0;
                foreach ($student->exams as $key3 => $student_exam_mark) {
                    if ($student_subject->first_subject_id == $student_exam_mark->subject_id) {
                        $student_subject->first_sub_mark = (int) $student_exam_mark->sub_mark + (int) $student_exam_mark->ob_mark;
                        $subject_total = $subject_total + (int) $student_exam_mark->sub_mark + (int) $student_exam_mark->ob_mark;
                        if($student_subject->first_subject_id == $student->optional_id){
                            $optional = 1;
                            $has_optional = 1;
                        }
                    }
                    if ($student_subject->second_subject_id == $student_exam_mark->subject_id) {
                        $student_subject->second_sub_mark = (int) $student_exam_mark->sub_mark + (int) $student_exam_mark->ob_mark;
                        $subject_total = $subject_total + (int) $student_exam_mark->sub_mark + (int) $student_exam_mark->ob_mark;
                        if($student_subject->first_subject_id == $student->optional_id){
                            $optional = 1;
                            $has_optional = 1;
                        }
                    }
                }
                $student_subject->total = $subject_total;
                $total_mark = $total_mark + $subject_total;
                $student_subject->gpa = $this->get_gpa($subject_total, $student_subject->full_mark, $optional);

                if($student_subject->gpa == 0){
                    $rank_status = 'Fail';
                }
                $total_gpa = $total_gpa + $student_subject->gpa;
            }
            $final_gpa = $combine_subject_rule && $combine_subject_rule->optional_type == 'has_optional' && $has_optional == 1 ? $total_gpa / (sizeof($subject_list) - 1) : $total_gpa / sizeof($subject_list);
            $final_gpa = $final_gpa > 5 ? 5 : $final_gpa;
            $student->gpa = $final_gpa >= 1 ? $final_gpa : 0;
            $student->gpa = $rank_status == 'Fail' ? 0 : $student->gpa;
            $student->gpa_format = number_format((float) $student->gpa, 2, '.', '');
            $student->rank_status = $rank_status;
            $student->total = $total_mark;
            array_push($new_mark_list, $student);
        } //student foreach ends

        if ($request->ranking_system == 'gpa') {
            usort($new_mark_list, function($a, $b) {
                return strnatcmp($b->gpa, $a->gpa);
            }); 
        } else { 
            usort($new_mark_list, function($a, $b) {
                return $b->total - $a->total;
            }); 
        }
        // return response()->json(['mark_list' => $new_mark_list], 422);

        $last_total = 0;
        $last_rank = 0;
        $showcase_student = [];
        foreach ($new_mark_list as $key => $list) {
            if ($request->ranking_system == 'gpa') {$list->rank_total = $list->gpa;} else {$list->rank_total = $list->total;}
            if (!$list->rank_status && $list->rank_total == $last_total) {
                $list->rank = $last_rank;
                $last_rank = $list->rank;
                $last_total = $list->rank_total;
            }
            if (!$list->rank_status && $list->rank_total != $last_total) {
                $list->rank = $last_rank + 1;
                $last_rank = $list->rank;
                $last_total = $list->rank_total;
            }
            if($list->rank && $list->rank < 4){
                array_push($showcase_student, $list);
            }
        }  
        return response()->json(['mark_list' => $new_mark_list, 'showcase_student' => $showcase_student]);
    }

    protected function get_gpa($mark, $full_mark, $optional = null){
        $gpa = 0;
        if ($mark/$full_mark >= .8) {
            $gpa = 5;
        } elseif ($mark/$full_mark >= .7) {
            $gpa = 4;
        } elseif ($mark/$full_mark >= .6) {
            $gpa = 3.5;
        } elseif ($mark/$full_mark >= .5) {
            $gpa = 3;
        } elseif ($mark/$full_mark >= .4) {
            $gpa = 2;
        } elseif ($mark/$full_mark >= .33) {
            $gpa = 1;
        }

        if($optional == 1 && $gpa <= 2){
            $gpa = 0;
        }
        if($optional == 1 && $gpa > 2){
            $gpa = $gpa - 2;
        }
        return $gpa;
    }

    public function get_individual_mark1(Request $request){
        $exam_list = exam_list::whereDate('date', '>=' , $request->date_from)->whereDate('date', '<=' , $request->date_to)->where('status', 'finish')->with('schedules');
        if ($request->exam_type) {
            $exam_list = $exam_list->where('exam_type_id', $request->exam_type);
        }
        
        $exam_list = $exam_list->get();

        // $exam = [];
        // foreach ($exam_list as $key => $list) {
        //     $l = ['date' => $list->date, 'subject_id' => $list->schedules->subject_id, 'sub_full' => $list->sub_full_mark, 'ob_full' => $list->ob_full_mark];
        //     array_push($exam, $l);
        // }

        $student = student::where('reg_no', $request->reg_no)->where('status', 'present')->first();
        if (!$student) {
            return response()->json('No student found', 422);
        }
        $mark_list = student_exam_mark::whereDate('date', '>=' , $request->date_from)->whereDate('date', '<=' , $request->date_to)->where('student_id', $student->id)->with('students.batches', 'students.institutions', 'subjects')->get();
        // return response()->json(['mark_list' => $mark_list]);
        if (sizeof($mark_list)==0) {
            return response()->json(['mark_list' => [], 'mark_item' => new \stdClass()]);
        }

        $pass_mark = .33;
        $new_mark_list = [];
        foreach ($mark_list as $key => $list) {
            $check_exam = [];
            foreach ($exam_list as $exam) {
                if ($exam->date == $list->date && $exam->schedules->subject_id == $list->subject_id) {
                    $list->sub_full_mark = $exam->sub_full_mark;
                    $list->ob_full_mark = $exam->ob_full_mark;
                    array_push($check_exam, $exam->id);
                }
            }

            if (!sizeof($check_exam)) {
                unset($mark_list[$key]);
                continue 1;
            }

            // initialize
            $list->rank_status = '';

            if ($list->sub_full_mark && $list->ob_full_mark) {                
                $list->highest = student_exam_mark::whereDate('date', $list->date)->where('subject_id', $list->subject_id)->max(DB::raw('sub_mark + ob_mark'));
                $list->total = $list->sub_mark + $list->ob_mark;
                $list->full_mark = $list->sub_full_mark + $list->ob_full_mark;
            }
            if ($list->sub_full_mark && !$list->ob_full_mark) {                
                $list->highest = student_exam_mark::whereDate('date', $list->date)->where('subject_id', $list->subject_id)->max('sub_mark');
                $list->total = $list->sub_mark;
                $list->full_mark = $list->sub_full_mark;
            }
            if (!$list->sub_full_mark && $list->ob_full_mark) {             
                $list->highest = student_exam_mark::whereDate('date', $list->date)->where('subject_id', $list->subject_id)->max('ob_mark');
                $list->total = $list->ob_mark;
                $list->full_mark = $list->ob_full_mark;
            }
            if ($list->sub_full_mark && $list->sub_mark/$list->sub_full_mark < $pass_mark) {
                $list->rank_status = 'Fail(S)';
            }
            if ($list->ob_full_mark && $list->ob_mark/$list->ob_full_mark < $pass_mark) {
                if ($list->rank_status == 'Fail(S)') {
                    $list->rank_status = 'Fail';
                } else {
                    $list->rank_status = 'Fail(O)';
                }
            }
            array_push($new_mark_list, $list);
        }
        return response()->json(['mark_list' => $new_mark_list]);
    }

    public function get_individual_mark(Request $request){
        
        $student= student::where('reg_no', $request->reg_no)->where('status', 'present')->first();
        if (!$student) {
            return response()->json('No student found', 422);
        }
        $exam_list = exam_list::whereDate('date', '>=' , $request->date_from)->whereDate('date', '<=' , $request->date_to)->where('status', 'finish')->with('schedules')->where('batch_id', $student->batch_id);
        if ($request->exam_type) {
            $exam_list = $exam_list->where('exam_type_id', $request->exam_type);
        }
        $exam_list = $exam_list->get();

        // return response()->json(['mark_list' => $exam_list], 422);
        
        $subject = [];
        $subject_by_exam_list = [];
        $subject_list = [];
        $subject_combine_array = [];

        if ($request->combine_subject_rule_id) {
            $combine_subject_rule = combine_subject_rule::where('id', $request->combine_subject_rule_id)->first();
            foreach ($combine_subject_rule->combine_subjects as $key => $combine_subject) {
                $first_subject_id = Null;
                $second_subject_id = Null;
                $date = "";
                foreach($exam_list as $key1 => $list){
                    if($list->schedules->subject_id == $combine_subject->first_subject_id){
                        $first_subject_id = $list->schedules->subject_id;
                        $date = $list->date;
                    }
                    if($list->schedules->subject_id == $combine_subject->second_subject_id){
                        $second_subject_id = $list->schedules->subject_id;
                        $date = $list->date;
                    }
                }
                if($first_subject_id && $second_subject_id){
                    $sub_object = new \StdClass;
                    $sub_object->subject_name = $combine_subject->subject_name;
                    $sub_object->date = $date;
                    $sub_object->first_subject_id = $combine_subject->first_subject_id;
                    $sub_object->second_subject_id = $combine_subject->second_subject_id;
                    $sub_object->first_sub_mark = 0;
                    $sub_object->second_sub_mark = 0;
                    $sub_object->full_mark = $combine_subject->full_mark;
                    $sub_object->combine_subject_no = 2;
                    array_push($subject_list, $sub_object);
                    array_push($subject_combine_array, $combine_subject->first_subject_id);
                    array_push($subject_combine_array, $combine_subject->second_subject_id);
                    array_push($subject, $combine_subject->first_subject_id);
                    array_push($subject, $combine_subject->second_subject_id);
                }
            }
        }

        foreach($exam_list as $key => $list){
            if(!in_array($list->schedules->subject_id, $subject_combine_array)){
                $sub_object = new \StdClass;
                $sub_object->subject_name = $list->schedules->subjects->name;
                $sub_object->first_subject_id = $list->schedules->subject_id;
                $sub_object->date = $list->date;
                $sub_object->second_subject_id = Null;
                $sub_object->first_sub_mark = 0;
                $sub_object->second_sub_mark = Null;
                $sub_object->combine_subject_no = 1;
                $sub_object->full_mark = $list->sub_full_mark + $list->ob_full_mark;
                array_push($subject_list, $sub_object);
                array_push($subject, $list->schedules->subject_id);
            }
        }
        // return response()->json(['mark_list' => $subject_list], 422);

        $student = student::where('reg_no', $request->reg_no)->where('status', 'present')->whereHas('exams', function($query) use($subject, $request){
            $query->whereDate('date', '>=' , $request->date_from)->whereDate('date', '<=' , $request->date_to)->whereIn('subject_id', $subject);
        })->with(array('exams' => function($query) use ($subject, $request){
                $query->whereDate('date', '>=' , $request->date_from)->whereDate('date', '<=' , $request->date_to)->whereIn('subject_id', $subject)->with('subjects');
        }))->select('id', 'name', 'reg_no', 'photo', 'optional_id', 'batch_id', 'institution_id')->with('batches', 'institutions')->first();

        if (!$student) {
            return response()->json(['mark_list' => [], 'mark_item' => new \stdClass()]);
        }

        // return response()->json(['mark_list' => $student_with_mark], 422);

        $pass_mark = 0.33;
        $new_mark_list = [];
        $fail_condition = 'combine';
            
        $subjects = [];
        foreach($subject_list as $key => $list){
            $sub_object = new \StdClass;
            $sub_object->subject_name = $list->subject_name;
            $sub_object->date = $list->date;
            $sub_object->first_subject_id = $list->first_subject_id;
            $sub_object->second_subject_id = $list->second_subject_id;
            $sub_object->first_sub_mark = 0;
            $sub_object->second_sub_mark = Null;
            $sub_object->full_mark = $list->full_mark;
            $sub_object->combine_subject_no = $list->combine_subject_no;
            array_push($subjects, $sub_object);
        }
        $student->subjects = $subjects;

        $total_gpa = 0;
        $rank_status = Null;
        $total_mark = 0;
        $has_optional = 0;
        foreach ($student->subjects as $key2 => $student_subject) {
            $subject_total = 0;
            $optional = 0;
            foreach ($student->exams as $key3 => $student_exam_mark) {
                if ($student_subject->first_subject_id == $student_exam_mark->subject_id) {
                    $student_subject->first_sub_mark = (int) $student_exam_mark->sub_mark + (int) $student_exam_mark->ob_mark;
                    $subject_total = $subject_total + (int) $student_exam_mark->sub_mark + (int) $student_exam_mark->ob_mark;
                    if($student_subject->first_subject_id == $student->optional_id){
                        $optional = 1;
                        $has_optional = 1;
                    }
                    if($student_subject->combine_subject_no < 2){
                        $student_subject->sub_mark = (int) $student_exam_mark->sub_mark;
                        $student_subject->ob_mark = (int) $student_exam_mark->ob_mark;
                    }
                }
                if ($student_subject->second_subject_id == $student_exam_mark->subject_id) {
                    $student_subject->second_sub_mark = (int) $student_exam_mark->sub_mark + (int) $student_exam_mark->ob_mark;
                    $subject_total = $subject_total + (int) $student_exam_mark->sub_mark + (int) $student_exam_mark->ob_mark;
                    if($student_subject->first_subject_id == $student->optional_id){
                        $optional = 1;
                        $has_optional = 1;
                        $student_subject->optional = 1;
                    }
                }
            }
            $student_subject->total = $subject_total;
            $total_mark = $total_mark + $subject_total;
            // if($student_subject->first_subject_id == 28){return response()->json(['mark_list' => $optional], 422);}
            $student_subject->gpa = $this->get_gpa($subject_total, $student_subject->full_mark, $optional);
            $student_subject->original_gpa = $this->get_gpa($subject_total, $student_subject->full_mark);
            if($student_subject->gpa == 0){
                $rank_status = 'Fail';
            }
            $total_gpa = $total_gpa + $student_subject->gpa;
        }
        $final_gpa = isset($combine_subject_rule) && $combine_subject_rule->optional_type == 'has_optional' && $has_optional == 1 ? $total_gpa / (sizeof($subject_list) - 1) : $total_gpa / sizeof($subject_list);
        $final_gpa = $final_gpa > 5 ? 5 : $final_gpa;
        $student->gpa = $final_gpa >= 1 ? $final_gpa : 0;
        $student->gpa = $rank_status == 'Fail' ? 0 : $student->gpa;
        $student->gpa_format = number_format((float) $student->gpa, 2, '.', '');
        $student->rank_status = $rank_status;
        $student->total = $total_mark;

        return response()->json(['mark_list' => $student]);
    }

    public function get_combine_subject_rule($branch_id){
        $combine_subject_rule = combine_subject_rule::where('branch_id', $branch_id)->with('combine_subjects', 'echelons')->get();
        return response()->json(['combine_subject_rule' => $combine_subject_rule]);
    }

    public function add_combine_subject_rule(Request $request){
        $combine_subject_rule = new combine_subject_rule;
        $combine_subject_rule->echelon_id = $request->echelon_id;
        $combine_subject_rule->name = $request->name;
        $combine_subject_rule->branch_id = $request->branch_id;
        $combine_subject_rule->optional_type = $request->optional_type;
        $combine_subject_rule->save();
        combine_subject::where('combine_subject_rule_id', $combine_subject_rule->id)->delete();
        foreach ($request->subject_combinations as $key => $combination) {
            combine_subject::create(['subject_name' => $combination['subject_name'], 'first_subject_id' => $combination['first_subject_id'], 'second_subject_id' => $combination['second_subject_id'], 'combine_subject_rule_id' => $combine_subject_rule->id, 'full_mark' => $combination['full_mark']]);
        }

        return response()->json(['combine_subject_rule' => $combine_subject_rule]);
    }

    public function edit_combine_subject_rule(Request $request, $id){
        $combine_subject_rule = combine_subject_rule::where('id', $id)->first();
        $combine_subject_rule->echelon_id = $request->echelon_id;
        $combine_subject_rule->name = $request->name;
        $combine_subject_rule->branch_id = $request->branch_id;
        $combine_subject_rule->optional_type = $request->optional_type;
        $combine_subject_rule->save();
        combine_subject::where('combine_subject_rule_id', $combine_subject_rule->id)->delete();
        foreach ($request->subject_combinations as $key => $combination) {
            combine_subject::create(['subject_name' => $combination['subject_name'], 'first_subject_id' => $combination['first_subject_id'], 'second_subject_id' => $combination['second_subject_id'], 'combine_subject_rule_id' => $combine_subject_rule->id, 'full_mark' => $combination['full_mark']]);
        }
    }

    public function delete_combine_subject_rule($id){
        combine_subject_rule::where('id', $id)->delete();
    }


}
