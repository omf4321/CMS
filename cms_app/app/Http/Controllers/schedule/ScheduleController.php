<?php

namespace App\Http\Controllers\schedule;

use App\Http\Controllers\Controller;
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
use App\Model\Admin\student;
use App\Model\Admin\exam_list;
use App\Events\MessageSent;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\sms\sms_trait;
use Spatie\ResponseCache\Facades\ResponseCache;
use Cache;
use App\Http\Controllers\attendance\AttendanceTrait;

class ScheduleController extends Controller
{
    use sms_trait, AttendanceTrait;

    public function __construct()
    {
        $this->middleware('admin');
    }

    public function weekly_schedule($id)
    {
        $weekly_schedule = weekly_schedule::with('subjects', 'batches.echelons', 'teachers')->where('batch_id', $id)->get();
        return response()->json(['weekly_schedule' => $weekly_schedule]);
    }

    public function weekly_schedule_add(Request $request)
    {
        // return response()->json($request->all(), 422);
        $this->validate($request, [
           'schedule_type'=> 'required',
           'batch_id'=> 'required',
           'period'=> 'required'
        ]);

        $weekly_schedule = new weekly_schedule;
        $weekly_schedule->batch_id = $request->batch_id;
        $weekly_schedule->subject_id = $request->subject_id;
        $weekly_schedule->teacher_id = $request->teacher_id;
        $weekly_schedule->schedule_type = strtolower($request->schedule_type);
        $weekly_schedule->day = $request->day;
        $weekly_schedule->period = $request->period;
        $weekly_schedule->time = $request->time;
        $weekly_schedule->save();
        return response()->json(['weekly_schedule' => $weekly_schedule->with('subjects', 'batches.echelons', 'teachers')->where('id', $weekly_schedule->id)->first()]);
    }

    public function weekly_schedule_edit(Request $request, $id)
    {
        // return response()->json($request->all(), 422);
        $weekly_schedule = weekly_schedule::find($id);
        $weekly_schedule->subject_id = $request->subject_id;
        $weekly_schedule->teacher_id = $request->teacher_id;
        $weekly_schedule->day = $request->day;
        $weekly_schedule->period = $request->period;
        $weekly_schedule->schedule_type = strtolower($request->schedule_type);
        $weekly_schedule->time = $request->time;
        $weekly_schedule->save();
    }

    public function weekly_schedule_delete($id)
    {
        weekly_schedule::where('id',$id)->delete();        
    }

    public function duplicate_weekly_schedule(Request $request)
    {
        if ($request->from_batch_id == $request->to_batch_id) {
            return response()->json(['error' => 'Please Select Different Batch'], 422);
        }
        $weekly_schedule_array = weekly_schedule::where('batch_id', $request->from_batch_id)->get();
        if (sizeof($weekly_schedule_array) == 0 ) {
            return response()->json(['error' => 'No Schedule Found'], 422);
        }
        foreach ($weekly_schedule_array as $key => $schedule) {
            $weekly_schedule = new weekly_schedule;
            $weekly_schedule->batch_id = $request->to_batch_id;
            $weekly_schedule->subject_id = $schedule->subject_id;
            $weekly_schedule->teacher_id = $schedule->teacher_id;
            $weekly_schedule->schedule_type = strtolower($schedule->schedule_type);
            $weekly_schedule->day = $schedule->day;
            $weekly_schedule->period = $schedule->period;
            $weekly_schedule->time = $schedule->time;

            if ($request->time1 && $schedule->period == 1) {
                $weekly_schedule->time = $request->time1;
            }
            if ($request->time2 && $schedule->period == 2) {
                $weekly_schedule->time = $request->time2;
            }
            if ($request->time3 && $schedule->period == 3) {
                $weekly_schedule->time = $request->time3;
            }
            $weekly_schedule->save();
        }
    }

    public function delete_weekly_schedule($batch_id)
    {
        $weekly_schedule = weekly_schedule::where('batch_id', $batch_id)->delete();
    }

    public function daily_schedule_list(){
        $schedule_list = schedule_list::all();
        return response()->json(['schedule_list' => $schedule_list]);
    }

    public function daily_schedule($from, $to = null)
    {
        if (!$to) {
            $to = $from;
        }
        $daily_schedule = daily_schedule::with('subjects', 'batches.echelons', 'teachers')->whereDate('date', '>=', $from)->WhereDate('date', '<=', $to)->get();
        return response()->json(['daily_schedule' => $daily_schedule]);
    }

    public function get_daily_schedule_by_id($id)
    {
        $daily_schedule = daily_schedule::with('subjects', 'batches', 'echelons', 'teachers', 'chapters','teacher_batches', 'exam_lists')->where('id', $id)->first();
        return response()->json(['daily_schedule' => $daily_schedule]);
    }

    public function daily_schedule_add(Request $request)
    {
        // return response()->json($request->all(), 422);
        $this->validate($request, [
           'batch_id'=> 'required',
           'subject_id'=> 'required',
           'teacher_id'=> 'required',
           'period'=> 'required'
        ]);
        DB::beginTransaction();
        try {
            $ids = []; //variable for which daily_schedule id are going to add
            $batches = [];
            if (sizeof($request->batches)>0) {
                foreach ($request->batches as $key => $batch) {
                    array_push($batches, $batch['value']);
                }
            } else {
                array_push($batches, $request->batch_id);
            }
            foreach ($batches as $key => $list) {
                $daily_schedule = new daily_schedule;
                $daily_schedule->batch_id = $list;
                $daily_schedule->subject_id = $request->subject_id;
                $daily_schedule->teacher_id = $request->teacher_id;
                $daily_schedule->schedule_type = $request->schedule_type;
                $daily_schedule->period = $request->period;
                $daily_schedule->chapter_text = $request->chapter_text;
                $daily_schedule->topic = $request->topic;
                $daily_schedule->time = $request->time;
                $daily_schedule->date = $request->date;
                if ($request->schedule_type == 'online_class' || $request->schedule_type == 'online_exam') {
                    $daily_schedule->online_class_url = $request->online_class_url;
                }
                $daily_schedule->save();
                array_push($ids, $daily_schedule->id);
            }
            // Commit
            DB::commit(); 
            return response()->json(['daily_schedule' => $daily_schedule->whereIn('id', $ids)->with('subjects', 'batches.echelons', 'teachers')->get()]);
        } catch (Exception $e) {
            DB::rollback();
        }
    }

    public function daily_schedule_edit(Request $request, $id)
    {
        // return response()->json($request->all(), 422); 
        DB::beginTransaction();
        try {
            $ids = []; //variable for which daily_schedule id are going to add
            $batches = [];
            $old_daily_schedule = daily_schedule::find($id);
            if ($request->batches) {
                foreach ($request->selected_batches as $key => $batch) {
                    array_push($batches, $batch['value']);
                }
                foreach ($batches as $key => $list) {
                    $daily_schedule = daily_schedule::where('batch_id', $list)->whereDate('date', $request->date)->where('subject_id', $old_daily_schedule->subject_id)->where('schedule_type', $old_daily_schedule->schedule_type)->first();
                    // return response()->json($old_daily_schedule, 422); 
                    if ($daily_schedule) {
                        $daily_schedule->subject_id = $request->subject_id;
                        $daily_schedule->teacher_id = $request->teacher_id;
                        $daily_schedule->schedule_type = $request->schedule_type;
                        $daily_schedule->chapter_text = $request->chapter_text;
                        $daily_schedule->topic = $request->topic;
                        $daily_schedule->date = $request->date;
                        if ($request->schedule_type == 'online_class' || $request->schedule_type == 'online_exam') {
                            if(strpos($request->online_class_url, "https://") == false){
                                $online_class_url = 'https://' . $request->online_class_url;
                                $daily_schedule->online_class_url = $online_class_url;
                            } else {
                                $daily_schedule->online_class_url = $request->online_class_url;
                            }
                            $daily_schedule->online_class_status = $request->online_class_status;
                            $daily_schedule->online_exam_status = $request->online_exam_status;
                            $daily_schedule->teacher_attendance_status = $request->teacher_attendance_status;
                        }
                        $daily_schedule->save();
                        array_push($ids, $daily_schedule->id);
                    }
                }
            } else {
                $old_daily_schedule->subject_id = $request->subject_id;
                $old_daily_schedule->teacher_id = $request->teacher_id;
                $old_daily_schedule->schedule_type = $request->schedule_type;
                $old_daily_schedule->period = $request->period;
                $old_daily_schedule->chapter_text = $request->chapter_text;
                $old_daily_schedule->topic = $request->topic;
                $old_daily_schedule->time = $request->time;
                $old_daily_schedule->date = $request->date;
                if ($request->schedule_type == 'online_class' || $request->schedule_type == 'online_exam') {
                    if(strpos($request->online_class_url, "https://") == false){
                        $online_class_url = 'https://' . $request->online_class_url;
                        $old_daily_schedule->online_class_url = $online_class_url;
                    } else {
                        $old_daily_schedule->online_class_url = $request->online_class_url;
                    }
                    $old_daily_schedule->online_class_status = $request->online_class_status;
                    $old_daily_schedule->online_exam_status = $request->online_exam_status;
                    $old_daily_schedule->teacher_attendance_status = $request->teacher_attendance_status;
                }
                $old_daily_schedule->save();
                array_push($ids, $old_daily_schedule->id);
            }
            
            DB::commit();
            return response()->json(['daily_schedule' => daily_schedule::whereIn('id', $ids)->with('subjects', 'batches.echelons', 'teachers')->get()]);
        } catch (Exception $e) {
            DB::rollback();
        }       
    }

    public function lecture_sheet_update($id)
    {
        // return response()->json($request->all());        

        $daily_schedule = daily_schedule::find($id);
        if ($daily_schedule->lecture_sheet==0) {
            $daily_schedule->lecture_sheet = 1;
        }
        else {
            $daily_schedule->lecture_sheet = 0;
        }
        $daily_schedule->save();
        Cache::forget('daily_schedule_' . $daily_schedule->date);
        if ($daily_schedule->teachers) {
            $user_id = $daily_schedule->teachers->user_id;
            Cache::forget('user_discontented_schedule_' . intval($user_id));
            Cache::forget('user_dashboard_contented_schedule_' . intval($user_id));
            Cache::forget('user_contented_schedule_' . intval($user_id));
        }
    }

    public function daily_schedule_delete($id)
    {
        $daily_schedule = daily_schedule::where('id',$id)->first();
        Cache::forget('daily_schedule_' . $daily_schedule->date);
        if ($daily_schedule->teachers) {
            $user_id = $daily_schedule->teachers->user_id;
            Cache::forget('user_discontented_schedule_' . intval($user_id));
            Cache::forget('user_dashboard_contented_schedule_' . intval($user_id));
            Cache::forget('user_contented_schedule_' . intval($user_id));
        }
        $daily_schedule->delete();
    }

    public function delete_by_date(Request $request)
    {
        // return $request->all();
        $daily_schedule = daily_schedule::whereDate('date', '>=', $request->delete_from)->whereDate('date', '<=', $request->delete_to)->whereIn('batch_id', $request->delete_batch)->delete();
        Cache::flush();
    }

    public function generate_schedule(Request $request)
    {
        $date_from = new Carbon($request->date_from);
        $date_to = new Carbon($request->date_to);
        DB::beginTransaction();
        try {
            // difference between date from and date to for looping
            $diff = $date_from->diffInDays($date_to);

            for ($i=0; $i <= $diff; $i++) { 
                if ($i==0) {
                    $date = $date_from;
                }
                else {
                    $date = $date_from->addDays(1);
                }
                // check if date in vacation dates
                $valid_date = 0;
                $close_day = 0;
                if (sizeof($request->vacation_range)>0) {
                    foreach ($request->vacation_range as $vacation_date) {
                        $v_date_from = new Carbon($vacation_date['date_from']);
                        $v_date_to = new Carbon($vacation_date['date_to']);
                        if ($date->between($v_date_from, $v_date_to)) {
                            $valid_date++;
                        }
                        if ($date->equalTo($v_date_from)) {
                            $vacation_text = $vacation_date['vacation_text'];
                            $close_day = 1;
                        }
                    }  
                }
                // create daily schedule with looping - if date is valid and not in vacation
                if ($valid_date == 0 || $close_day > 0) {
                    $timestamp = strtotime($date);
                    $day = strtolower(date('D', $timestamp));
                    $schedules = weekly_schedule::where('batch_id', $request->batch_id)->where('day', $day)->get();
                    // if it is close day only one row insert so get only one record
                    if ($close_day==1) {
                        $schedules = weekly_schedule::where('batch_id', $request->batch_id)->where('day', $day)->limit(1)->get();
                    }
                    foreach ($schedules as $key => $schedule) {
                        $daily_schedule = new daily_schedule;
                        $daily_schedule->batch_id = $schedule->batch_id;
                        $daily_schedule->subject_id = $schedule->subject_id;
                        $daily_schedule->teacher_id = $schedule->teacher_id;
                        $daily_schedule->schedule_type = strtolower($schedule->schedule_type);
                        $daily_schedule->date = $date;
                        $daily_schedule->period = $schedule->period;
                        $daily_schedule->time = $schedule->time;
                        // if it is a close day
                        if ($close_day == 1) {
                            $daily_schedule->subject_id = Null;
                            $daily_schedule->teacher_id = Null;
                            $daily_schedule->schedule_type = 'close';
                            $daily_schedule->topic = $vacation_text;
                        }
                        $daily_schedule->save();
                    }
                }          
            }
            DB::commit(); 
        } catch (Exception $e) {
            DB::rollback();
        }
    }

    public function check_schedule(Request $request){
        // this function check is schedule of given date is exist in schedule generate section
        $schedule = daily_schedule::where('batch_id', $request->batch_id)->where(function($query) use ($request){
            $query->whereBetween('date', [$request->date_from, $request->date_to])->orWhereBetween('date', [$request->date_from, $request->date_to]);
        })->first();
        if ($schedule) {
            return response()->json(['schedule' => $schedule], 422);
        }
    }
    public function daily_schedule_by_id($id)
    {
        $schedule_list = daily_schedule::with('subjects', 'chapters')->where('schedule_id', $id)->orderBy('date')->get();
        return response()->json(['daily_schedule_by_id' => $schedule_list]);
    }
    public function daily_schedule_by_date(Request $request)
    {
        // return $request->all();
        $schedule_list = daily_schedule::with('subjects.echelons')->where('batch_id', $request->batch_id)->WhereDate('date', '>=', $request->date_from)->whereDate('date', '<=', $request->date_to)->orderBy('date')->get();
        foreach ($schedule_list as $key => $schedule) {
            if (!$schedule->chapter_text && !$schedule->topic) {
                $schedule->chapter_text = 'ক্লাসে জানানো হবে';
            }
        }
        return response()->json(['daily_schedule' => $schedule_list]);
    }
    public function schedule_list($id)
    {
        $schedule_list = schedule_list::with('echelons', 'echelons.branches')
        ->whereHas('echelons', function($query) use ($id) {
            $query->where('branch_id', $id);
        })->get();
        return response()->json(['schedule_list' => $schedule_list]);
    }
    public function schedule_list_by_id($id)
    {
        $schedule_list = schedule_list::with('echelons', 'schedule_contents')->where('id', $id)->first();
        return response()->json(['schedule_list_by_id' => $schedule_list]);
    }
    public function schedule_list_delete($id)
    {
        schedule_list::where('id',$id)->delete();        
    }

    public function exchange_class(Request $request)
    {
        DB::beginTransaction();
        try {
            for ($i=0; $i < sizeof($request->exchange_from) ; $i++) { 
                $exchange_from_data = daily_schedule::find($request->exchange_from[$i]['id']);
                $exchange_from_data_reserve = daily_schedule::find($request->exchange_from[$i]['id']);
                $exchange_to_data = daily_schedule::find($request->exchange_to[$i]['id']);

                $exchange_from_data->date = $exchange_to_data->date;
                $exchange_from_data->save();

                $exchange_to_data->date = $exchange_from_data_reserve->date;
                $exchange_to_data->save();
            }
            DB::commit(); 
        } catch (Exception $e) {
            DB::rollback();
        }
    }

    public function update_schedule(Request $request)
    {
        $this->validate($request, [
           'course_id'=> 'required',
           'subject_data'=> 'required',
           'schedule_id' => 'required'
        ]);
        $custom_text = Null;
        $break_chapter_id = Null;
        $break_chapter_complete = Null;
        // this operation is for schedule_type 'class'
        schedule_list_content::where('schedule_list_id', $request->schedule_id)->delete();
        foreach ($request->subject_data as $s_i => $subject) {
            // empty topic for this subject if replace all enable
            daily_schedule::where('schedule_id', $request->schedule_id)->where('subject_id', $subject['subject_id'])->where('schedule_type', 'class')->update(['topic' => Null]);
            if ($subject['break_chapter_id']) {
                $break_chapter_id = $subject['break_chapter_id'];
                $break_chapter_complete = $subject['break_chapter_complete'];
                $planner_list = schedule_planner::where('chapter_id', $subject['break_chapter_id'])->where('course_id', $request->course_id)->where('class_no','>', $subject['break_chapter_complete'])->orderBy('class_no')->get();
                foreach ($planner_list as $key => $planner) {
                    $daily_schedule = daily_schedule::where('schedule_id', $request->schedule_id)->where('subject_id', $subject['subject_id'])->where('schedule_type', 'class')->whereNull('topic')->orderBy('date')->first();
                    if ($daily_schedule) {
                        $daily_schedule->chapter_id = $subject['break_chapter_id'];
                        $daily_schedule->topic = $planner->topic;
                        $daily_schedule->save();
                    }
                }
            }
            if ($subject['include_chapter']==1) {
                // if has break chapter
                foreach ($subject['chapters'] as $c_i => $chapter) {
                    // if has chapter
                    if (!$chapter['custom'] && $chapter['chapter_id']) {
                        // get planner list of chapter
                        $planner_list = schedule_planner::where('chapter_id', $chapter['chapter_id'])->where('course_id', $request->course_id)->orderBy('class_no')->get();
                        // foreach planner item update item to daily schedule where topic is blank is specific subject
                        foreach ($planner_list as $p_i => $planner) {
                            $daily_schedule = daily_schedule::where('schedule_id', $request->schedule_id)->where('subject_id', $subject['subject_id'])->where('schedule_type', 'class')->whereNull('topic')->orderBy('date')->first();
                            if ($daily_schedule) {
                                $daily_schedule->chapter_id = $chapter['chapter_id'];
                                $daily_schedule->topic = $planner->topic;
                                $daily_schedule->save();
                            }
                            $custom_text = Null;
                        }
                    }
                    if ($chapter['custom']) {
                        $daily_schedule = daily_schedule::where('schedule_id', $request->schedule_id)->where('subject_id', $subject['subject_id'])->where('schedule_type', 'class')->whereNull('topic')->orderBy('date')->first();
                        if ($daily_schedule) {
                            $daily_schedule->topic = $chapter['custom_text'];
                            $daily_schedule->save();
                        }
                        $custom_text = $chapter['custom_text'];
                    }
                    schedule_list_content::create([
                        'schedule_list_id'=> $request->schedule_id,
                        'course_id'=> $request->course_id,
                        'subject_id' => $subject['subject_id'],
                        'chapter_id' => $daily_schedule ? $daily_schedule->chapter_id : Null,
                        'custom_text' => $custom_text,
                        'include_chapter' => 1,
                        'break_chapter_id' => $break_chapter_id,
                        'break_chapter_complete' => $break_chapter_complete
                    ]);
                } //end of chapter foreacher
            }
            if ($subject['include_chapter']==0) {
                $planner_list = schedule_planner::where('subject_id', $subject['subject_id'])->where('course_id', $request->course_id)->orderBy('class_no')->get();
                foreach ($planner_list as $p_i => $planner) {
                    $daily_schedule = daily_schedule::where('schedule_id', $request->schedule_id)->where('subject_id', $subject['subject_id'])->where('schedule_type', 'class')->whereNull('topic')->orderBy('date')->first();
                    if ($daily_schedule) {
                        $daily_schedule->topic = $planner->topic;
                        $daily_schedule->save();
                    }
                }
            }
            if ($subject['include_chapter']==0 || $subject['break_chapter_id']) {
                schedule_list_content::create([
                    'schedule_list_id'=> $request->schedule_id,
                    'course_id'=> $request->course_id,
                    'subject_id' => $subject['subject_id'],
                    'chapter_id' => Null,
                    'custom_text' => Null,
                    'include_chapter' => $subject['break_chapter_id'] ? 1 : 0,
                    'break_chapter_id' => $break_chapter_id,
                    'break_chapter_complete' => $break_chapter_complete
                ]);
            }
        } //end of subject foreach
    }
    
    public function get_dashboard_schedule($date)
    {        
        $daily_schedule = daily_schedule::with('subjects', 'batches.attendance_lists', 'teachers', 'batches.echelons')->whereDate('date', $date)->where('schedule_type', '!=', 'close')->get();
        return response()->json(['daily_schedule' => $daily_schedule]);
    }

    public function get_dashboard_exchangable_schedule($id)
    {        
        $current_schedule = daily_schedule::find($id);
        $daily_schedule = daily_schedule::with('subjects')->where('schedule_type', 'class')->whereDate('date', '>', $current_schedule->date)->where('schedule_id', $current_schedule->schedule_id)->where('subject_id', '!=', $current_schedule->subject_id)->orderBy('date')->get();
        return response()->json(['daily_schedule' => $daily_schedule]);
    }

    public function get_dashboard_lecture_schedule()
    {
        $date = Carbon::today()->format('Y-m-d');
        $daily_schedule = daily_schedule::with('subjects', 'batches.echelons', 'teachers')->where('lecture_sheet', 1)->whereDate('date', '>', Carbon::today())->orderBy('date')->limit(12)->get();
        return response()->json(['daily_schedule' => $daily_schedule]);
    }

    public function get_dashboard_exam_schedule()
    {                
        $date = Carbon::today()->format('Y-m-d');
        $daily_schedule = daily_schedule::with('subjects', 'batches.attendance_lists', 'teachers', 'batches.echelons')->where('schedule_type', 'exam')->whereDate('date', '>=', Carbon::today())->WhereDate('date', '<=', Carbon::today()->addDays(8))->orderBy('date')->get();
        return response()->json(['daily_schedule' => $daily_schedule]);
    }

    public function edit_dashboard_schedule(Request $request)
    {
        // return response()->json($request->campaign_name, 422);

        $this->validate($request, [
           'echelon_id'=> 'required',
           'subject_id'=> 'required',
           'period'=> 'required',
           'batch_id' => 'required'
        ]);
        if ($request->save_type == 'new') {
            $daily_schedule = new daily_schedule;
            $daily_schedule->date = $request->date;
            $daily_schedule->batch_id = $request->batch_id;
        } else {
            $daily_schedule = daily_schedule::where('id', $request->id)->first();
        }
        if ($request->save_type == 'delete') {
            $daily_schedule->delete();
            return response()->json(['sms' => 'no_sms', 'daily_schedule' => []]);
        }
        // store previous for sms
        if (!$request->save_type == 'new') {
            $previous_subject = $daily_schedule->subjects ? $daily_schedule->subjects->name : '';
        }
        // save process
        $daily_schedule->subject_id = $request->subject_id;
        $daily_schedule->teacher_id = $request->teacher_id;
        $daily_schedule->schedule_type = strtolower($request->schedule_type);
        $daily_schedule->period = $request->period;
        $daily_schedule->lecture_sheet = $request->lecture_sheet;
        $daily_schedule->time = Carbon::parse($request->time)->format('H:i:s');
        $daily_schedule->chapter_text = $request->chapter_text;
        $daily_schedule->topic = $request->topic;
        if ($request->topic || $request->chapter_text) {
            $daily_schedule->status = 'contented';
        }
        if ($request->schedule_type == 'online_class' || $request->schedule_type == 'online_exam') {

            $online_class_url = $request->online_class_url;
            if($request->online_class_url && substr($online_class_url, 0, 8) != 'https://'){
                $online_class_url = 'https://' . $request->online_class_url;
            }
            $daily_schedule->online_class_url = $online_class_url;
            if($request->online_class_url && $request->hosting){
                $daily_schedule->link_creator_id = Auth::guard('admin')->user()->id;
                $daily_schedule->link_creator_name = Auth::guard('admin')->user()->name;
            }
            $daily_schedule->online_class_status = $request->online_class_status;
            $daily_schedule->online_exam_status = $request->online_exam_status;
            $daily_schedule->teacher_attendance_status = $request->teacher_attendance_status;
        }

        $daily_schedule->save();

        // on going class for pusher
        $daily_schedule->on_going_class = "false";
        // $start_time = Carbon::parse($daily_schedule->time)->subMinutes(5);
        $end_time = Carbon::parse($daily_schedule->time)->addMinutes(30);
        if ($daily_schedule->online_class_status == 'begin' && Carbon::now() <= $end_time) {
            $daily_schedule->on_going_class = "true";
            $daily_schedule->teacher = $daily_schedule->teachers->name;
            $daily_schedule->subject = $daily_schedule->subjects->name;
        }
        // pusher
        $schedule = [];
        array_push($schedule, $daily_schedule);
        // broadcast(new MessageSent($schedule));
        
        // sms part
        if ($request->sms && $daily_schedule->subjects->name != $previous_subject && !$request->save_type) {
            $student = student::whereIn('batch_id', $request->batch_id)->where('status', 'present')->get();
            // create sms json array
            $json_array = [];
            $daily_schedule = daily_schedule::where('id', $daily_schedule->id)->with('subjects')->first();
            foreach ($student as $key => $std) {
                $text = 'Dear ' . $std->name . ' ' .Carbon::parse($daily_schedule->date)->format('d-M') .' tomader ' . $previous_subject . ' er poriborte ' . $daily_schedule->subjects->name . ' class hobe. BT.';
                $json_data = new \stdClass;
                $json_data->MobileNumber =  $std->mobile;
                $json_data->SmsText = $text;
                $json_data->Type = "TEXT";
                array_push($json_array, $json_data);
            }
            $sms_report = $this->onnorokom_list_sms($student, $request, $json_array);
            $balance = $this->onnorokom_balance_for_list();
            return response()->json(['sms_report' => $sms_report, 'balance' => $balance, 'sms' => 'sms']);
        }

        $daily_schedule = daily_schedule::where('id', $request->id)->with('subjects', 'batches.attendance_lists', 'teachers', 'batches.echelons')->first();

        return response()->json(['sms' => 'no_sms', 'daily_schedule' => $daily_schedule]);
    }


    public function add_dashboard_schedule(Request $request)
    {
        // return response()->json($request->campaign_name, 422);
        $this->validate($request, [
           'echelon_id'=> 'required',
           'subject_id'=> 'required',
           'period'=> 'required',
        ]);
        DB::beginTransaction();
        try {
            $ids = []; //variable for which daily_schedule id are going to add
            $timestamp = strtotime($request->date);
            $day = strtolower(date('D', $timestamp));
            $daily_schedule = new daily_schedule;
            $daily_schedule->echelon_id = $request->echelon_id;
            $daily_schedule->subject_id = $request->subject_id;
            $daily_schedule->teacher_id = $request->teacher_id;
            $daily_schedule->topic = $request->topic;
            $daily_schedule->schedule_type = strtolower($request->schedule_type);
            $daily_schedule->schedule_id = $request->schedule_id;
            $daily_schedule->date = $request->date;        
            $daily_schedule->day = $day;
            $chapters = [];            
            if ($request->chapter) {
                foreach ($request->chapter as $chapter) {
                    array_push($chapters, $chapter['value']);
                }
            }
            // contented or disconted
            $daily_schedule->status = 'contented';
            $daily_schedule->save();
            if ($daily_schedule->teachers) {
                $user_id = intval($daily_schedule->teachers->user_id);
                Cache::forget('user_dashboard_contented_schedule_' . $user_id);
                Cache::forget('user_contented_schedule_' . $user_id);
            }
            // sync chapter
            $daily_schedule->chapters()->sync($chapters);
            // edit batch list for every schedule
            foreach ($request->batch as $b_key => $batch) {
                $schedule_batch = new schedule_batch;
                $schedule_batch->schedule_id = $daily_schedule->id;
                $schedule_batch->batch_id = $batch['value'];
                // class no will be seperated by comma for each batch, if not common class no will added
                if (strpos($request->period, ',') == false) {
                    $schedule_batch->class_no = $request->period;
                } else {
                    $schedule_batch->class_no = explode(',', $request->period)[$b_key];
                }
                $schedule_batch->save();
            }
            // forget cache
            Cache::forget('daily_schedule_' . $daily_schedule->date);
            // Commit
            $response = daily_schedule::where('id', $daily_schedule->id)->with('subjects', 'batches.attendance_lists', 'echelons', 'teachers', 'chapters','teacher_batches')->first();
            DB::commit();
            return response()->json(['daily_schedule' => $response]);
        } catch (Exception $e) {
            DB::rollback();
        }
    }
    public function save_dashboard_exchange_schedule(Request $request)
    {
        // return response()->json($request->all, 422);
        $exchange_from_data = daily_schedule::where('id', $request->exchange_from_id)->with('subjects')->first();
        $exchange_from_data_reserve = daily_schedule::where('id', $request->exchange_from_id)->with('subjects')->first();
        $exchange_to_data = daily_schedule::where('id', $request->exchange_to_id)->with('subjects')->first();

        $exchange_from_data->date = $exchange_to_data->date;
        $exchange_from_data->day = $exchange_to_data->day;
        $exchange_from_data->save();

        $exchange_to_data->date = $exchange_from_data_reserve->date;
        $exchange_to_data->day = $exchange_from_data_reserve->day;
        $exchange_to_data->save();

        $batches_ids = [];

        foreach ($exchange_from_data_reserve->batches as $key => $batch) {
            array_push($batches_ids, $batch->id);
        }

        // sms part
        if ($request->sms) {
            $student = student::whereIn('batch_id', $batches_ids)->get();
            // create sms json array
            $json_array = [];
            foreach ($student as $key => $std) {
                $text = 'Dear ' . $std->name . ' ' .Carbon::parse($exchange_from_data_reserve->date)->format('d-M') .' tomader ' . $exchange_from_data->subjects->name . ' er poriborte ' . $exchange_to_data->subjects->name . ' class hobe. BT.';
                $json_data = new \stdClass;
                $json_data->MobileNumber =  $std->mobile;
                $json_data->SmsText = $text;
                $json_data->Type = "TEXT";
                array_push($json_array, $json_data);
            }
            $sms_report = $this->onnorokom_list_sms($student, $request, $json_array);
            $balance = $this->onnorokom_balance_for_list();
            return response()->json(['sms_report' => $sms_report, 'balance' => $balance, 'sms' => 'sms']);
        }
        $response = daily_schedule::where('id', $request->exchange_to_id)->with('subjects', 'batches.attendance_lists', 'echelons', 'teachers', 'chapters','teacher_batches')->first();
        return response()->json(['daily_schedule' => $response]);
    }

    public function save_teacher_sign_dashboard_schdule(Request $request)
    {
        $date = Carbon::parse($request->date);
        $date = $date->format('Y-m-d');
        if ($date>=Carbon::today()->format('Y-m-d')) {
            return response()->json(['msg' => 'Only previous date classes can be signable'], 422);
        }
        foreach ($request->batch as $key => $batch) {
            $teacher_attendance = new schedule_teacher_attendance;
            $teacher_attendance->teacher_id = $request->teacher_id;
            $teacher_attendance->batch_id = $batch['value'];
            $teacher_attendance->schedule_id = $request->id;
            $teacher_attendance->type = 'teacher';
            $teacher_attendance->save();
        }
        Cache::forget('daily_schedule_' . $date);
    }

    public function confirm_teacher($id)
    {
        $daily_schedule = daily_schedule::find($id);
        $teacher = $daily_schedule->teacher_id;
        $date = $daily_schedule->date;
        $batch = $daily_schedule->batch_id;
        if ($daily_schedule->status =='confirmed') {
            daily_schedule::where('batch_id', $batch)->whereDate('date', $date)->where('teacher_id', $teacher)->update(['status' => 'contented']);
        } else {
            daily_schedule::where('batch_id', $batch)->whereDate('date', $date)->where('teacher_id', $teacher)->update(['status' => 'confirmed']);
        }
    }

    // get exam schedule for add exam in created blank exam schedule
    public function get_schedule_exam_list(Request $request)
    {
        $daily_schedule = daily_schedule::whereDate('date', '>=', $request->date_from)->whereDate('date', '<=', $request->date_to)->where('batch_id', $request->batch_id)->with('batches');

        if ($request->subject_id && !$request->compare_subject) {
            $daily_schedule = $daily_schedule->where(function($query) use ($request){
                $query->where('subject_id', $request->subject_id);
                if ($request->compare_schedule_type) {
                    $query->orWhere('schedule_type', $request->compare_schedule_type);
                }
            });
        }
        if ($request->subject_id && $request->compare_subject) {
            $daily_schedule = $daily_schedule->where(function($query) use ($request){
                $query->where('subject_id', $request->subject_id)->orWhere('subject_id', $request->compare_subject);
            });
        }
        if ($request->schedule_type) {
            $daily_schedule = $daily_schedule->where('schedule_type', $request->schedule_type);
        }
        if ($request->teacher_id) {
            $daily_schedule = $daily_schedule->where('teacher_id', $request->teacher_id);
        }
        $daily_schedule = $daily_schedule->orderBy('date')->get();
        return response()->json(['daily_schedule' => $daily_schedule]);
    }

    public function search_schedule_exam_list(Request $request)
    {
        if (!$request->search_date_to) {
            $request->search_date_to = '2050-10-10';
        }
        $daily_schedule = daily_schedule::where('batch_id', $request->search_batch)->whereDate('date', '>=', $request->search_date_from)->whereDate('date', '<=', $request->search_date_to)->with('subjects', 'batches');

        if ($request->search_subject) {            
            $daily_schedule = $daily_schedule->where('subject_id', $request->search_subject);
        }
        $daily_schedule = $daily_schedule->get();
        return response()->json(['daily_schedule' => $daily_schedule]);
    }

    public function save_schedule_exam_list(Request $request)
    {
        // return response()->json($request->all(), 422);
        foreach ($request->exam_list as $key => $list) {
            $daily_schedule = daily_schedule::find($list['id']);
            $daily_schedule->topic = $list['topic'];
            $daily_schedule->subject_id = isset($list['subject_id']) ? $list['subject_id'] : null;
            $daily_schedule->chapter_text = $list['chapter_text'];
            $daily_schedule->teacher_id = isset($list['teacher_id']) ? $list['teacher_id'] : null;
            $daily_schedule->schedule_type = $list['schedule_type'];
            $daily_schedule->period = $list['period'];
            if ($list['topic'] || $list['chapter_text']) {
                $daily_schedule->status = 'contented';
            }
            $daily_schedule->save();
            if ($request->apply_batch) {
                if ($request->apply_by == 'period') {
                    $apply_batch = daily_schedule::where('batch_id', $request->apply_batch)->whereDate('date', $daily_schedule->date)->where('period', $daily_schedule->period)->first();
                } else {
                    $apply_batch = daily_schedule::where('batch_id', $request->apply_batch)->whereDate('date', $daily_schedule->date)->where('subject_id', $daily_schedule->subject_id)->first();
                }
                if ($apply_batch) {
                    $apply_batch->topic = $daily_schedule->topic;
                    $apply_batch->chapter_text = $daily_schedule->chapter_text;
                    $apply_batch->subject_id = $daily_schedule->subject_id;
                    $apply_batch->teacher_id = $daily_schedule->teacher_id;
                    $apply_batch->schedule_type = $daily_schedule->schedule_type;
                    $apply_batch->period = $daily_schedule->period;
                    $apply_batch->status = $daily_schedule->status;
                    $apply_batch->save();
                }
            }
        }
    }

    // manage teache sign

    public function get_teacher_schedule_list(Request $request)
    {
        if ($request->job_type == 'class') {
            $daily_schedule = daily_schedule::whereDate('date', '>=', $request->date_from)->whereDate('date', '<=', $request->date_to)->where('teacher_id', $request->teacher_id)->where('schedule_type', 'like', '%class%')->with('teacher_schedules', 'batches.echelons', 'subjects', 'exam_lists');
        }

        if ($request->job_type == 'invigilator') {
            $daily_schedule = daily_schedule::whereDate('date', '>=', $request->date_from)->whereDate('date', '<=', $request->date_to)->has('exam_lists')->whereHas('exam_lists', function($query) use ($request){
                $query->where('invigilator_id', $request->teacher_id);
            })->with('batches.echelons', 'subjects', 'exam_lists')
            ->with(array('teacher_schedules' => function($query){
                $query->where('type', 'invigilator');
            }));
        }

        if ($request->job_type == 'scripter') {
            $daily_schedule = daily_schedule::whereDate('date', '>=', $request->date_from)->whereDate('date', '<=', $request->date_to)->has('exam_lists')->whereHas('exam_lists', function($query) use ($request){
                $query->where('scripter_id', $request->teacher_id);
            })->with('batches.echelons', 'subjects', 'exam_lists')
            ->with(array('teacher_schedules' => function($query){
                $query->where('type', 'scripter');
            }));            
        }

        if ($request->batch_id) {
            $daily_schedule = $daily_schedule->where('batch_id', $request->batch_id);
        }

        if ($request->subject_id) {
            $daily_schedule = $daily_schedule->where('subject_id', $request->subject_id);
        }

        if ($request->protect_combine == 1 && $request->job_type == 'class') {
            $combine_batch = [18];
            $combine_subject = [14,15,16,17,18,19,20,21,22]; //class nine general subject
            // se9s = 17, se10s = 27
            $protected_batch_id = [17,27,28,24]; //se10c, se8
            $daily_schedule = $daily_schedule->whereNotIn('batch_id', $protected_batch_id);
        }

        $daily_schedule = $daily_schedule->orderBy('date')->get();
        

        return response()->json(['daily_schedule' => $daily_schedule]);
    }

    public function save_teacher_schedule_list(Request $request)
    {
        foreach ($request->teacher_list as $key => $list) {
            $daily_schedule = daily_schedule::where('id', $list['id'])->with('exam_lists')->first();
            if (!$list['teacher_done'] && $request->job_type == 'class' && $daily_schedule->teacher_attendance_status == 'done') {
                $daily_schedule->teacher_attendance_status = Null;
            }
            if ($list['teacher_done'] && $request->job_type == 'class') {
                $daily_schedule->teacher_attendance_status = 'done';
            }
            // change and delete previous teacher sign
            if ($list['teacher_id'] != $daily_schedule->teacher_id && $request->job_type == 'class') {
                $daily_schedule->teacher_id = $list['teacher_id'];
                $teacher_sign = schedule_teacher_attendance::where('schedule_id', $daily_schedule->id)->first();
                if ($teacher_sign) {
                    $teacher_sign->delete();
                }
            }

            // when teacher change for invigilator or scripter
            if ($list['exam_lists'] && ($list['exam_lists']['invigilator_id'] != $daily_schedule->exam_lists->invigilator_id || $list['exam_lists']['scripter_id'] != $daily_schedule->exam_lists->scripter_id)){
                return response()->json('You cannot change teacher for script or invigilator from herer', 422);
            }

            $daily_schedule->save();

            // save teacher sign on class
            if ($list['teacher_done'] && $request->job_type == 'class') {
                $teacher_sign = schedule_teacher_attendance::where('schedule_id', $list['id'])->first();
                if (!$teacher_sign) {
                    $teacher_sign = new schedule_teacher_attendance;
                }
                $teacher_sign->schedule_id = $daily_schedule->id;
                $teacher_sign->batch_id = $daily_schedule->batch_id;
                $teacher_sign->teacher_id = $daily_schedule->teacher_id;
                $teacher_sign->type = 'teacher';
                $teacher_sign->save();
            }
            // save teacher sign on invigilator
            if ($list['teacher_done'] && $request->job_type == 'invigilator' && $list['invigilator_minute']) {
                // check exam list
                $exam_list = exam_list::where('schedule_id', $list['id'])->first();
                if (!$exam_list) {
                    return response()->json('exam not setup', 422);
                }

                $teacher_sign = schedule_teacher_attendance::where('schedule_id', $list['id'])->where('type', 'invigilator')->first();
                if (!$teacher_sign) {
                    $teacher_sign = new schedule_teacher_attendance;
                }
                $teacher_sign->schedule_id = $daily_schedule->id;
                $teacher_sign->batch_id = $daily_schedule->batch_id;
                $teacher_sign->teacher_id = $list['exam_lists']['invigilator_id'];
                $teacher_sign->type = 'invigilator';
                $teacher_sign->exam_list_id = $exam_list->id;
                $teacher_sign->invigilator_minute = $list['invigilator_minute'];
                $teacher_sign->save();
            }
            // save teacher sign on invigilator
            if ($list['teacher_done'] && $request->job_type == 'scripter' && $list['script_quantity']) {
                // check exam list
                $exam_list = exam_list::where('schedule_id', $list['id'])->first();
                if (!$exam_list) {
                    return response()->json('exam not setup', 422);
                }

                $teacher_sign = schedule_teacher_attendance::where('schedule_id', $list['id'])->where('type', 'scripter')->first();
                if (!$teacher_sign) {
                    $teacher_sign = new schedule_teacher_attendance;
                }
                $teacher_sign->schedule_id = $daily_schedule->id;
                $teacher_sign->batch_id = $daily_schedule->batch_id;
                $teacher_sign->teacher_id = $list['exam_lists']['scripter_id'];
                $teacher_sign->type = 'scripter';
                $teacher_sign->exam_list_id = $exam_list->id;
                $teacher_sign->script_quantity = $list['script_quantity'];
                $teacher_sign->save();
            }
            // delete not done
            if (!$list['teacher_done']) {
                if ($request->job_type == 'class') {                    
                    $teacher_sign = schedule_teacher_attendance::where('schedule_id', $list['id'])->first();
                }
                if ($request->job_type == 'invigilator') {                    
                    $teacher_sign = schedule_teacher_attendance::where('schedule_id', $list['id'])->where('type', 'invigilator')->first();
                }
                if ($request->job_type == 'scripter') {                    
                    $teacher_sign = schedule_teacher_attendance::where('schedule_id', $list['id'])->where('type', 'scripter')->first();
                }
                if ($teacher_sign) {
                    $teacher_sign->delete();
                }
            }
        }
    }

    public function change_online_class_status(Request $request)
    {
        // return $request->all();
        $daily_schedule = daily_schedule::where('id', $request->schedule_id)->with('subjects', 'batches.attendance_lists', 'teachers', 'batches.echelons')->first();
        if (Carbon::parse($daily_schedule->time)->gt(Carbon::now()->addMinutes(15))) {
            return response()->json(['error' => 'The class will start at ' . Carbon::parse($daily_schedule->time)->subMinutes(15)->format('H:i:s') . '.'], 422);
        }
        if ($request->status != 'exam_start') {
            $daily_schedule->online_class_status = $request->status;
        }
        if ($request->status == 'exam_start') {
            $daily_schedule->online_exam_status = 'exam_start';
            $daily_schedule->online_exam_time = Carbon::now();
        }
        if ($request->status == 'begin') {
            $this->create_batch_attendance($daily_schedule->batch_id);
        }
        if ($request->status == 'finish') {
            $daily_schedule->online_exam_status = 'submit';
        }

        $daily_schedule->save();

        $schedule = [];
        array_push($schedule, $daily_schedule);

        // broadcast(new MessageSent($schedule));
        
        return response()->json(['daily_schedule' => $daily_schedule]);
    }

    public function get_exam_detail($id)
    {
        // return $request->all();
        $online_exam = student_online_exam::where('schedule_id', $id)->with('schedules', 'students')->get();
        return response()->json(['online_exam' => $online_exam]);
    }
    
}
