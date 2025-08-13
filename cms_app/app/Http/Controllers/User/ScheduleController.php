<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\User;
use App\Model\Admin\daily_schedule;
use App\Model\Admin\schedule_list;
use App\Model\Admin\subject;
use App\Model\Admin\chapter;
use App\Model\Admin\weekly_schedule;
use App\Model\Admin\weekly_schedule_list;
use App\Model\Admin\schedule_planner;
use App\Model\Admin\schedule_list_content;
use App\Model\Admin\schedule_batch;
use App\Model\Admin\schedule_teacher_attendance;
use App\Model\Admin\attendance_list;
use App\Model\Admin\student;
use App\Model\Admin\batch;
use App\Model\Admin\student_attendance;
use App\Model\Admin\user_message;
use App\Model\Admin\exam_list;
use Carbon\Carbon;
use App\Events\MessageSent;
use DB;
use App\Http\Controllers\attendance\AttendanceTrait;

// Contains
// User Login Success Home Page
// User Dashboard


class ScheduleController extends Controller
{
    use AttendanceTrait;

    public function __construct()
    {
        $this->middleware('auth:web');
        // $this->middleware('admin');
    }

    public function get_user_message()
    {
        $user = Auth::guard('web')->user();
        // where user_id current login, topic is null, chapter is none (0)
        $user_message = user_message::whereHas('teachers', function($query) use ($user){
            $query->where('user_id', $user->id);
        })->whereNull('status')->whereDate('due_date', '>=', Carbon::today())->get();
        return response()->json(['user_message' => $user_message]);
    }

    public function read_user_message($id)
    {
        $message = user_message::find($id);
        $message->status = 'read';
        $message->save();
    }

    public function get_discontented_schedule()
    {
        $user = Auth::guard('web')->user();
        // where user_id current login, topic is null, chapter is none (0)
        $daily_schedule = daily_schedule::whereHas('teachers', function($query) use ($user){
            $query->where('user_id', $user->id);
        })->whereDate('date', '>=', Carbon::today())->whereNull('chapter_text')->whereNull('topic')->with('subjects', 'batches.echelons')->select('subject_id', 'batch_id', DB::raw('min(id) as id'), DB::raw('min(date) as date'))->groupBy('subject_id', 'batch_id')->get();        
        return response()->json(['daily_schedule' => $daily_schedule]);
    }

    public function get_user_alert()
    {
        $user = Auth::guard('web')->user();
        // where user_id current login, topic is null, chapter is none (0)
        $schedule_alert = daily_schedule::whereHas('teachers', function($query) use ($user){
            $query->where('teacher_id', $user->teachers->id);
        })->whereNull('chapter_text')->whereNull('topic')->whereDate('date', '>=', Carbon::today())->orderBy('date')->first();

        // $exam_alert = daily_schedule::whereHas('teachers', function($query) use ($user){
        //     $query->where('teacher_id', $user->teachers->id)->whereDate('date', '>=', Carbon::today())->whereDate('date', '<=', Carbon::today()->addDays(10));
        // })->whereHas('questions', function($query){
        //     $query->whereNull('status');
        // })->with('subjects.echelons')->orderBy('date')->get();

        return response()->json(['schedule_alert' => $schedule_alert, 'exam_alert' => []]);
    }

    public function get_exam_alert()
    {
        $user = Auth::guard('web')->user();
        // where user_id current login, topic is null, chapter is none (0)
        
        return response()->json(['daily_schedule' => $daily_schedule]);
    }

    public function get_discontented_subject_schedule($batch_id, $subject_id)
    {
        $user = Auth::guard('web')->user();
        // where user_id current login, topic is null, chapter is none (0)
        $daily_schedule = daily_schedule::whereHas('teachers', function($query) use ($user){
            $query->where('user_id', $user->id);
        })->whereNull('status')->with('subjects', 'batches.echelons')->where('batch_id', $batch_id)->where('subject_id', $subject_id)->whereDate('date', '>=', Carbon::today())->orderBy('date')->get();
        return response()->json(['daily_schedule' => $daily_schedule]);
    }

    public function get_contented_schedule()
    {
        $user = Auth::guard('web')->user();
        // where user_id current login, topic is null, chapter is none (0)
        $daily_schedule = daily_schedule::whereHas('teachers', function($query) use ($user){
            $query->where('user_id', $user->id);
        })->whereNotNull('status')->with('subjects', 'batches.echelons', 'teacher_batches')->WhereDate('date', '>=', Carbon::today())->orderBy('date')->orderBy('subject_id')->get();
        return response()->json(['daily_schedule' => $daily_schedule]);
    }

    public function get_dashboard_contented_schedule()
    {
        $user = Auth::guard('web')->user();
        $daily_schedule = daily_schedule::whereHas('teachers', function($query) use ($user){
            $query->where('user_id', $user->id);
        })->whereNotNull('status')->with('subjects', 'batches.attendance_lists', 'teacher_batches', 'batches.echelons')->WhereDate('date', '>=', Carbon::today())->whereDate('date', '<=', Carbon::today()->addDays(7))->where('schedule_type', 'like', '%class%')->orderBy('date')->get();

        foreach ($daily_schedule as $key => $schedule) {
            $combine_batch = $this->protecting_batch($schedule);
            if (in_array($schedule->batch_id, $combine_batch)) {
                $schedule->protect = 'true';
            } else {
                $schedule->protect = 'false';
            }
        }
        return response()->json(['daily_schedule' => $daily_schedule]);
    }
    public function get_contented_schedule_by_date($date)
    {
        $user = Auth::guard('web')->user();
        $daily_schedule = daily_schedule::whereHas('teachers', function($query) use ($user){
            $query->where('user_id', $user->id);
        })->whereNotNull('status')->with('echelons', 'subjects', 'batches', 'chapters')->orderBy('date')->orderBy('echelon_id')->whereDate('date', '>=', $date)->get();
        return response()->json(['daily_schedule' => $daily_schedule]);
    }

    public function get_teacher_schedule_summary($date_from, $date_to = null)
    {
        if (!$date_to || $date_to>Carbon::today()) {
            $date_to = Carbon::today();
        }
        $user = Auth::guard('web')->user();
        $teacher_attendance = schedule_teacher_attendance::where('teacher_id', $user->teachers->id)->where('type', 'teacher')->whereHas('schedules', function($query) use ($date_from, $date_to){
            $query->whereDate('date', '>=', $date_from)->whereDate('date', '<=', $date_to);
        })->orderBy('id')->with('batches.echelons', 'schedules')->get();

        return response()->json(['teacher_attendance' => $teacher_attendance]);
    }

    public function get_single_contented_schedule($id)
    {                
        $daily_schedule = daily_schedule::where('id', $id)->first();
        return response()->json(['daily_schedule' => $daily_schedule]);
    }

    public function save_discontented_schedule(Request $request)
    {        
        // return $request->all();
        DB::beginTransaction();
        try {
            $chapter_id = '';
            $last_chapter_id = '';
            $class_no = 0;
            $hello = [];
            $contented = true;
            foreach ($request->schedule as $key => $schedule) {
                $chapters = [];
                $daily_schedule = daily_schedule::find($schedule['id']);
                $daily_schedule->topic = $schedule['topic'];
                $daily_schedule->chapter_text = $schedule['chapter_text'];
                if ($schedule['topic'] || $schedule['chapter_text']) {
                    $daily_schedule->status = 'contented';
                }
                $daily_schedule->save();
                // save to planner
                if (sizeof($request->batch)) {
                    $daily_schedule = daily_schedule::whereDate('date', $schedule['date'])->where('subject_id', $request->subject_id)->whereIn('batch_id', $request->batch)->update(['topic' => $schedule['topic'], 'chapter_text' => $schedule['chapter_text'], 'status' => 'contented']);
                }
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
        }
    }

    protected function save_planner($subject_id, $chapter_id, $topic, $class_no)
    {
        $planner = new schedule_planner;
        $planner->chapter_id = $chapter_id;
        $planner->subject_id = $subject_id;
        $planner->course_id = 2;
        $planner->topic = $topic;
        $planner->class_no = $class_no;
        $planner->save();
    }

    public function save_contented_schedule(Request $request)
    {
        $user = Auth::guard('web')->user();
        $daily_schedule = daily_schedule::find($request->id);
        $daily_schedule->topic = $request->topic;
        $daily_schedule->chapter_text = $request->chapter_text;
        $daily_schedule->status = 'contented';
        $daily_schedule->lecture_sheet = $request->lecture_sheet;
        $daily_schedule->save();

        return response()->json(['daily_schedule' => $daily_schedule->where('id', $daily_schedule->id)->with('subjects', 'batches.echelons')->first()]);
    }

    public function get_planner_schedule($chapter_id)
    {
        $planner = schedule_planner::where('chapter_id', $chapter_id)->where('course_id', 2)->get();
        return response()->json(['planner' => $planner]);
    }

    public function save_teacher_attendance(Request $request)
    {
        $daily_schedule = daily_schedule::where('id', $request->schedule_id)->first();

        if (!$daily_schedule) {
            return response()->json('Class not started yet', 422);
        }
        
        // $combine_batch = $this->protecting_batch($daily_schedule);
        // if (in_array($daily_schedule->batch_id, $combine_batch)) {
        //     return response()->json('false', 422);
        // }

        DB::beginTransaction();
        try {
            //update_teacher_attendance
            $teacher_attendance = schedule_teacher_attendance::firstOrCreate(
                ['teacher_id' =>  $request->teacher_id, 'batch_id' => $request->batch_id, 'schedule_id' => $request->schedule_id],
                ['teacher_id' =>  $request->teacher_id, 'batch_id' => $request->batch_id, 'schedule_id' => $request->schedule_id, 'type' => 'teacher']
            );

            $attendance_list = attendance_list::where('batch_id', $request->batch_id)->whereDate('date', Carbon::today())->update(['status' => 'done']);
            $daily_schedule->teacher_attendance_status = 'done';
            $daily_schedule->save();
            // confirm tommorow class
            if ($request->status == 'confirmed') {
                $daily_schedule = daily_schedule::where('teacher_id', $request->teacher_id)->whereDate('date', Carbon::today()->addDays(1)->format('Y-m-d'))->update(['status' => 'confirmed']);
            }
            // update teacher attendance status to daily schedule
            daily_schedule::where('id', $request->schedule_id)->update(['teacher_attendance_status' => 'done']);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
        }
    }

    public function confirm_lecture_sheet($id){
        $daily_schedule = daily_schedule::find($id);
        $daily_schedule->lecture_sheet = $daily_schedule->lecture_sheet ? Null : 1;
        $daily_schedule->save();
        $user = Auth::guard('web')->user();
        $date = Carbon::today()->format('Y-m-d');
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

        return response()->json(['student_list' => $students, 'attendance_list_id' => $attendance_list->id]);
    }

    public function save_batch_attendance(Request $request)
    {
        DB::beginTransaction();
        try {
            $count = 0;
            $user = Auth::guard('web')->user()->teachers;
            
            foreach ($request->student_attendance as $key => $attendance) {
                $std = student_attendance::find($attendance['attendance_id']);
                $std->attendance = $attendance['attendance'];            
                $std->save();           
                if ($attendance['attendance']) {
                    $count = $count + 1;
                }
            }

            $attendance_list = attendance_list::where('batch_id', $request->batch_id)->whereDate('date', Carbon::today())->first();
            $attendance_list->status = 'completed';
            $attendance_list->teacher_id = $user->id;
            $attendance_list->present = $count;
            $attendance_list->save();

            $schedule = daily_schedule::where('batch_id', $request->batch_id)->whereDate('date', Carbon::today())->update(['attendance_taken' => $user->id]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
        }
    }

    public function save_online_class_url(Request $request)
    {
        // return $request->all();
        $teacher = Auth::guard('web')->user()->teachers;
        $daily_schedule = daily_schedule::where('id', $request->schedule_id)->first();
        $combine_batch = $this->combining($daily_schedule);
        
        $online_class_url = $request->online_class_url;
        if($request->online_class_url && substr($online_class_url, 0, 8) != 'https://'){
            $online_class_url = 'https://' . $request->online_class_url;
        }
        // return $combine_batch;
        if ($combine_batch) {
            $schedule = daily_schedule::whereIn('batch_id', $combine_batch)->where('subject_id', $daily_schedule->subject_id)->whereDate('date', Carbon::today())->update([
                'online_class_url' => $online_class_url,
                'link_creator_id' => $teacher->id,
                'link_creator_name' => $teacher->name
            ]);
        } else {
            $daily_schedule->online_class_url = $online_class_url;
            $daily_schedule->link_creator_id = $teacher->id;
            $daily_schedule->link_creator_name = $teacher->name;
            $daily_schedule->save();
        }

        $schedule = [];
        if ($combine_batch) {
            $schedule = daily_schedule::whereIn('batch_id', $combine_batch)->where('subject_id', $daily_schedule->subject_id)->whereDate('date', Carbon::today())->get();
        } else {
            array_push($schedule, $daily_schedule);
        }
        broadcast(new MessageSent($schedule));
    }

    public function change_online_class_status(Request $request)
    {
        // return $request->all();
        $daily_schedule = daily_schedule::where('id', $request->schedule_id)->first();
        if (Carbon::parse($daily_schedule->time)->gt(Carbon::now()->addMinutes(5))) {
            return response()->json(['error' => 'The class will start at ' . Carbon::parse($daily_schedule->time)->subMinutes(5)->format('H:i:s') . '.'], 422);
        }
        
        $combine_batch = $this->combining($daily_schedule);
        if ($combine_batch) {
            $schedule = daily_schedule::whereIn('batch_id', $combine_batch)->where('subject_id', $daily_schedule->subject_id)->whereDate('date', Carbon::today())->update(['online_class_status' => $request->status]);
        } else {
            $daily_schedule->online_class_status = $request->status;
            $daily_schedule->save();
        }
        // create_batch_attendance
        if ($request->status == 'begin') {
            $this->create_batch_attendance($daily_schedule->batch_id);
        }

        $schedule = [];
        if ($combine_batch) {
            $schedule = daily_schedule::whereIn('batch_id', $combine_batch)->where('subject_id', $daily_schedule->subject_id)->whereDate('date', Carbon::today())->get();
        } else {
            array_push($schedule, $daily_schedule);
        }
        broadcast(new MessageSent($schedule));
    }

    protected function combining($daily_schedule)
    {
        // class nine class id = 7
        // class ten class id = 8
        // combine subject class nine bangla, english, math id = 14 to 22
        // combine subject class ten bangla, english, math id = 14 to 22

        
        // 31  SM10C
        // 28  SE10C
        
        // 18  SE9C
        // 30  SM9C
        
        // 26  SM10S
        // 27  SE10S

        // 25  SM8
        // 24  SE8

        // 22  SM9S
        // 17  SE9S

        $combine_subject = 'false';
        $combine_subject_nine = [14,15,16,17,18,19,20,21,22];
        $combine_subject_ten = [56,57,59,80,83,84,94];

        if (in_array($daily_schedule->subject_id, $combine_subject_nine) || in_array($daily_schedule->subject_id, $combine_subject_ten)) {
            $combine_subject = 'true';
        }



        $combine_batch = [
            [28,31],
            [26,27],
            [24,25],
            [17,22]
        ];

        $combine_batch_for_subject = [
            [28,31,26,27],
            [18,17,22]
        ];

        $combine_key = -1;
        if ($combine_subject == 'true') {
            foreach ($combine_batch_for_subject as $key => $batch) {
                if (in_array($daily_schedule->batch_id, $batch)) {
                    $combine_key = $key;
                }
            }
        } else {            
            foreach ($combine_batch as $key => $batch) {
                if (in_array($daily_schedule->batch_id, $batch)) {
                    $combine_key = $key;
                }
            }
        }

        if ($combine_key>-1 && $combine_subject == 'true') {
            return $combine_batch_for_subject[$combine_key];
        }
        if ($combine_key>-1 && $combine_subject == 'false') {
            return $combine_batch[$combine_key];
        }

    }

    protected function protecting_batch($daily_schedule)
    {
        $combine_subject = 'false';
        $combine_subject_nine = [14,15,16,17,18,19,20,21,22];
        $combine_subject_ten = [56,57,59,80,83,84,94];

        if (in_array($daily_schedule->subject_id, $combine_subject_nine) || in_array($daily_schedule->subject_id, $combine_subject_ten)) {
            $combine_subject = 'true';
        }


        $null_array = [];
        $protect_batch = [28, 27, 24, 17];

        $protect_batch_for_subject = [
            [28,31,27],
            [18,17]
        ];

        if ($combine_subject == 'true') {
            foreach ($protect_batch_for_subject as $key => $batch) {
                if (in_array($daily_schedule->batch_id, $batch)) {
                    return $batch;
                }
            }
            return $null_array;
        } else {            
            return $protect_batch;
        }
    }


}
