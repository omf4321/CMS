<?php

namespace App\Http\Controllers\setting;

use App\Http\Controllers\Controller;
use App\Model\Admin\batch;
use App\Model\Admin\branch;
use App\Model\Admin\course;
use App\Model\Admin\echelon;
use App\Model\Admin\group;
use App\Model\Admin\institution;
use App\Model\Admin\subject;
use App\Model\Admin\teacher;
use App\Model\Admin\chapter;
use App\Model\Admin\exam_type;
use App\Model\Admin\chapter_topic;
use Auth;

class SettingController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth:admin');
        // $this->middleware('cacheResponse');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('setting');
    }

    public function branch_list_by_role()
    {
        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            if ($user->hasRole('superadmin') || $user->hasRole('admin')) {
                return response()->json(['branch_list_by_role'=>branch::all()]);
            } 
        }
        return response()->json(['branch_list_by_role'=>branch::get()]);
    }

    public function course_list_by_branch($id)
    {
        return response()->json(['course_list_by_branch' => course::all()->where('branch_id', $id)]);
    }

    public function echelon_list_by_branch($id)
    {
        return response()->json(['echelon_list_by_branch' => echelon::where('branch_id', $id)->get()]);
    }

    public function group_list_by_branch($id)
    {
        return response()->json(['group_list_by_branch' => group::all()->where('branch_id', $id)]);
    }

    public function institution_list_by_branch($id)
    {
        return response()->json(['institution_list_by_branch' => institution::selectRaw('id, name')->where('branch_id', $id)->get()]);
    }

    public function subject_list_by_branch_and_echelon($branch_id, $echelon_id)
    {
        return response()->json(['subject_list_by_branch_and_echelon' => subject::selectRaw('id, name')->where('branch_id', $branch_id)->where('echelon_id', $echelon_id)->orderBy('name')->get()]);
    }

    public function subject_list_by_branch($branch_id)
    {
        return response()->json(['subject_list_by_branch' => subject::where('branch_id', $branch_id)->orderBy('name')->get()]);
    }

    public function batch_list_by_branch_and_echelon($branch_id, $echelon_id=null)
    {
        if ($echelon_id) {
            return response()->json(['batch_list_by_branch_and_echelon' => batch::selectRaw('id, name')->where('branch_id', $branch_id)->where('echelon_id', $echelon_id)->where('active', 1)->get()]);
        }
        else {
            return response()->json(['batch_list_by_branch_and_echelon' => batch::selectRaw('id, name')->where('branch_id', $branch_id)->where('active', 1)->get()]);
        }
    }

    public function batch_list_by_branch($id)
    {  
        return response()->json(['batch_list_by_branch' => batch::where('branch_id', $id)->where('active', 1)->get()]);
    }
    public function batch_list_by_echelon($id)
    {  
        return response()->json(['batch_list_by_echelon' => batch::where('echelon_id', $id)->get()]);
    }

    public function teacher_list_by_branch($id)
    {
        $teacher_list_by_branch = teacher::where('branch_id', $id)->get(); 
        return response()->json(['teacher_list_by_branch' => $teacher_list_by_branch]);
    }

    public function chapter_list_by_branch($id)
    {
        $chapter_list_by_branch = chapter::whereHas('subjects', function($query) use ($id) {
            $query->where('branch_id', $id);
        })->orderBy('chapter_no', 'asc')->with('subjects')->get(); 
        return response()->json(['chapter_list_by_branch' => $chapter_list_by_branch]);
    }
    public function chapter_list_by_class($id)
    {        
        // if class ten
        if ($id == 8) {
            $id = 7;
        }
        $chapter_list_by_class = chapter::with('subjects', 'topics')->whereHas('subjects', function($query) use ($id) {
            $query->where('echelon_id', $id);
        })->orderBy('chapter_no', 'asc')->get(); 
        
        return response()->json(['chapter_list_by_class' => $chapter_list_by_class]);
    }

    public function chapter_list_by_subject($id)
    {        
        $chapter_list_by_subject = chapter::where('subject_id', $id)->orderBy('chapter_no', 'asc')->with('subjects.echelons', 'subjects.branches', 'topics')->get(); 
        return response()->json(['chapter_list_by_subject' => $chapter_list_by_subject]);
    }

    public function chapter_topic_by_class($id)
    {        
        // if class ten
        if ($id == 8) {
            $id = 7;
        }
        $chapter_topic_list = chapter_topic::whereHas('chapters.subjects', function($query) use ($id){
            $query->where('echelon_id', $id);
        })->orderBy('topic_no', 'asc')->get(); 
        return response()->json(['chapter_topic_list' => $chapter_topic_list]);
    }

    public function chapter_topic_by_subject($id)
    {        
        $chapter_topic_by_subject = chapter_topic::whereHas('chapters', function($query) use ($id){
            $query->where('subject_id', $id);
        })->orderBy('topic_no', 'asc')->get(); 
        return response()->json(['chapter_topic_by_subject' => $chapter_topic_by_subject]);
    }

    public function chapter_topic()
    {
        $chapter_topic_list = chapter_topic::orderBy('topic_no')->get(); 
        return response()->json(['chapter_topic_list' => $chapter_topic_list]);
    }

    public function exam_type_list()
    {
        $exam_type_list = exam_type::orderBy('id')->get(); 
        return response()->json(['exam_type_list' => $exam_type_list]);
    }
}
