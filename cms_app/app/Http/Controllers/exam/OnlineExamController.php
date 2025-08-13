<?php

namespace App\Http\Controllers\exam;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\User;
use App\Model\Admin\online_exam;
use App\Model\Admin\online_exam_subject;
use App\Model\Admin\question_tag;
use App\Model\Admin\branch;
use Carbon\Carbon;

// Contains
// User Login Success Home Page
// User Dashboard


class OnlineExamController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:admin');
    }
    
    public function get_online_exam($branch_id, $date)
    {
        $admin = Auth::guard('admin')->user();
        $role = $admin->getRoleNames()->first();
        if ($role != 'admin' && $role != 'superadmin') {
            $branch_name = branch::find($branch_id)->name;
            $subjects = $online_exam_list->where('branch_name', 'all_branch')->orwhere('branch_name', $branch_name);
        } else {
            $branch_name = $branch_id == 'all_branch' ? 'all_branch' : branch::find($branch_id)->name;
            $online_exam_list = online_exam::whereDate('date', '>=', $date)->where('branch_name', $branch_name);
        }
        return response()->json(['online_exam_list' => $online_exam_list->get()]);
    }

    public function add_online_exam(Request $request)
    {                                
        $admin = Auth::guard('admin')->user();
        $role = $admin->getRoleNames()->first();
        $online_exam = new online_exam;
        $online_exam->branch_name = 'all_branch';
        // branch id and name
        if ($role != 'admin' && $role == 'superadmin' && $request->branch_id != 'all_branch') {
            $online_exam->branch_name = branch::find($admin->branch_id)->name;
        }
        if ($role == 'admin' || $role == 'superadmin' && $request->branch_id != 'all_branch') {
            $online_exam->branch_name = branch::find($request->branch_id)->name;
        }
        // exam rules
        $rules = [];
        if ($request->change_answer) { array_push($rules, 'change_answer'); }
        if ($request->answer_later) { array_push($rules, 'answer_later'); }
        if ($request->left_answer) { array_push($rules, 'left_answer'); }
        if ($request->random_question) { array_push($rules, 'random_question'); }
        if ($request->random_option) { array_push($rules, 'random_option'); }

        $online_exam->exam_rule = $rules;

        $online_exam->name = $request->name;
        $online_exam->full_mark = $request->full_mark;
        $online_exam->negative_mark = $request->negative_mark;
        $online_exam->start_time = $request->start_time;
        $online_exam->full_time = $request->full_time;
        $online_exam->sit_exam_at = $request->sit_exam_at;
        $online_exam->pass_mark_option = $request->pass_mark_option;
        $online_exam->pass_mark = $request->pass_mark;
        $online_exam->show_result_option = $request->show_result_option;
        $online_exam->show_pdf = $request->show_pdf;
        $online_exam->date = $request->date;
        $online_exam->subjects = $request->subjects;
        $online_exam->total_attendance = 0;
        $online_exam->selected_question = 0;
        $online_exam->status = 'created';
        $online_exam->save();
        return response()->json(['online_exam_list' => $online_exam]);
    }

    public function edit_online_exam(Request $request, $id)
    {                        
        $admin = Auth::guard('admin')->user();
        $role = $admin->getRoleNames()->first();
        $online_exam = online_exam::find($id);
        $online_exam->branch_name = 'all_branch';
        // branch id and name
        if ($role != 'admin' && $role == 'superadmin' && $request->branch_id != 'all_branch') {
            $online_exam->branch_name = branch::find($admin->branch_id)->name;
        }
        if ($role == 'admin' || $role == 'superadmin' && $request->branch_id != 'all_branch') {
            $online_exam->branch_name = branch::find($request->branch_id)->name;
        }
        // exam rules
        $rules = [];
        if ($request->change_answer) { array_push($rules, 'change_answer'); }
        if ($request->answer_later) { array_push($rules, 'answer_later'); }
        if ($request->left_answer) { array_push($rules, 'left_answer'); }
        if ($request->random_question) { array_push($rules, 'random_question'); }
        if ($request->random_option) { array_push($rules, 'random_option'); }

        $online_exam->exam_rule = $rules;

        $online_exam->name = $request->name;
        $online_exam->full_mark = $request->full_mark;
        $online_exam->negative_mark = $request->negative_mark;
        $online_exam->start_time = $request->start_time;
        $online_exam->full_time = $request->full_time;
        $online_exam->sit_exam_at = $request->sit_exam_at;
        $online_exam->pass_mark_option = $request->pass_mark_option;
        $online_exam->pass_mark = $request->pass_mark;
        $online_exam->show_result_option = $request->show_result_option;
        $online_exam->show_pdf = $request->show_pdf;
        $online_exam->date = $request->date;
        $online_exam->subjects = $request->subjects;
        $online_exam->save();
        return response()->json(['online_exam_list' => $online_exam]);
    }

    public function delete_online_exam($id)
    {
        online_exam::where('id', $id)->delete();
    }

    public function get_online_exam_subject()
    {                        
        $subjects = online_exam_subject::all();
        return response()->json(['subjects' => $subjects]);
    }

    public function get_question_tag()
    {                        
        $tags = question_tag::all();
        return response()->json(['tags' => $tags]);
    }

    public function add_online_exam_subject(Request $request)
    {                        
        $subjects = new online_exam_subject;
        $subjects->name = $request->name;
        $subjects->description = $request->description;
        $subjects->save();
        return response()->json(['subjects' => $subjects]); 
    }

    public function edit_online_exam_subject(Request $request, $id)
    {                        
        $subjects = online_exam_subject::find($id);
        $subjects->name = $request->name;
        $subjects->description = $request->description;
        $subjects->save();
        return response()->json(['subjects' => $subjects]); 
    }

    public function delete_online_exam_subject($id)
    {                        
        $subjects = online_exam_subject::find($id);
        $subjects->delete();
    }

    public function get_online_question($search_by)
    {                        
        if ($search_by == 'tag') {
            $search_by_list = question_tag::has('questions')->orderBy('created_at', 'desc')->with('questions.options')->get();
        }
        if ($search_by == 'subject') {
            $search_by_list = online_exam_subject::has('questions')->with('questions.options')->get();
        }
        return response()->json(['search_by_list' => $search_by_list]);
    }


    public function get_selection_online_exam_question($search_by, $online_exam_id){
        $online_exam = online_exam::find($online_exam_id);
        $subjects = [];
        $question_range = [];
        foreach ($online_exam->subjects as $key => $subject) {
            array_push($subjects, (int) $subject['subject']);
            array_push($question_range, $subject['question_range']);
        }
        if ($search_by == 'tag') {
            $search_by_list = question_tag::whereHas('questions', function($query) use ($subjects){
                $query->whereIn('subject_id', $subjects);
            })->with(array('questions' => function($query) use ($subjects){
                $query->whereIn('subject_id', $subjects)->orderBy('created_at', 'desc')->with('options');
            }))->orderBy('created_at', 'desc')->get();
        }
        if ($search_by == 'subject') {
            $search_by_list = online_exam_subject::whereHas('questions', function($query) use ($subjects){
                $query->whereIn('subject_id', $subjects);
            })->with(array('questions' => function($query) use ($subjects){
                $query->whereIn('subject_id', $subjects)->orderBy('created_at', 'desc')->with('options');
            }))->get();
            $list = [];
            foreach ($subjects as $id) {
               array_push($list, $search_by_list->firstWhere('id', '=', $id));
            }
            $search_by_list = $list;
        }
        return response()->json(['search_by_list' => $search_by_list, 'subjects' => $subjects, 'question_range' => $question_range]);
    }

}
