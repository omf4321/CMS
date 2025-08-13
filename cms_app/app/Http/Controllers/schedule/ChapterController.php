<?php

namespace App\Http\Controllers\schedule;

use App\Http\Controllers\Controller;
use App\Model\Admin\chapter;
use App\Model\Admin\schedule_planner;
use App\Model\Admin\chapter_topic;
use Auth;
use Illuminate\Http\Request;
use DB;
use Spatie\ResponseCache\Facades\ResponseCache;

// Contains
// User Login Success Home Page
// User Dashboard


class ChapterController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function planner_list($id)
    {
        $planner_list = schedule_planner::with('courses', 'subjects', 'chapters', 'subjects.echelons', 'subjects.branches')->whereHas('subjects', function($query) use ($id) {
            $query->where('id', $id);
        })->get();  
        return response()->json(['planner_list' => $planner_list]);
    }

    public function planner_add(Request $request)
    {
        // return response()->json($request->all(), 422);
        DB::beginTransaction();
        try {
            $this->validate($request, [
               'topic'=> 'required',
               'echelon_id'=> 'required',
               'subject_id'=> 'required',
               'class_no'=> 'required'
            ]);
            // initialize class no 0 because start with +1
            $class_no = (int) $request->class_no - 1;
            $saved_planner = [];
            foreach ($request->topics as $key => $text) {
                $planner = new schedule_planner;
                $planner->topic = $text;
                $planner->course_id = $request->course_id;
                $planner->subject_id = $request->subject_id;
                $planner->chapter_id = $request->chapter_id;
                $planner->class_no = $class_no + 1;
                $planner->save();
                $class_no = $class_no + 1;
                array_push($saved_planner, $planner);
            }
            // DB Commit
            DB::commit();
            ResponseCache::clear();
            return response()->json(['saved_planner' => $saved_planner]);
        } catch (Exception $e) {
            DB::rollback();
        }
    }

    public function planner_edit(Request $request, $id)
    {
        $planner = schedule_planner::find($id);
        $planner->topic = $request->topic;
        $planner->class_no = $request->class_no;
        $planner->subject_id = $request->subject_id;
        $planner->chapter_id = $request->chapter_id;
        $planner->save();
        ResponseCache::clear();
        return response()->json(['planner' => schedule_planner::where('id', $id)->with('chapters')->first()]);
    }

    public function planner_delete($id)
    {
        schedule_planner::where('id',$id)->delete();
        ResponseCache::clear();
    }

    public function planner_add_before(Request $request)
    {
        DB::beginTransaction();
        try {
            $topics = schedule_planner::where('course_id', $request->course_id)->where('subject_id', $request->subject_id)->where('chapter_id', $request->chapter_id);
            if ($topics) {
                $topic = new schedule_planner;
                $topic->topic = $request->topic;
                $topic->course_id = $request->course_id;
                $topic->subject_id = $request->subject_id;
                $topic->chapter_id = $request->chapter_id;
                $topic->class_no = $request->class_no;
                $topic->save();

                $topics = $topics->where('class_no', '>=', $request->class_no)->where('id', '!=', $topic->id)->get();
                foreach ($topics as $element) {
                    $topic = schedule_planner::find($element->id);
                    $topic->class_no = $element->class_no + 1;
                    $topic->save();
                }
            }
            // DB Commit
            ResponseCache::clear();
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
        }
    }

    public function chapter_list($id)
    {
        $chapter_list = chapter::with('subjects', 'subjects.echelons', 'subjects.branches')->whereHas('subjects', function($query) use ($id) {
            $query->where('branch_id', $id);
        })->get();  
        return response()->json(['chapter_list' => $chapter_list]);
    }

    public function chapter_add(Request $request)
    {
        // if class ten
        if ($request->echelon_id == 8) {
            return response()->json(['chapter' => [], 'topic' => []]);
        }
        $this->validate($request, [
           'name'=> 'required',
           'echelon_id'=> 'required',
           'subject_id'=> 'required',
           'chapter_no'=> 'required'
        ]);
        DB::beginTransaction();
        try {
            $chapter = new chapter;
            $chapter->name = $request->name;
            $chapter->subject_id = $request->subject_id;
            $chapter->chapter_no = $request->chapter_no;
            $chapter->save();
            $topic = [];
            foreach ($request->chapter_topics as $key => $chapter_topic) {
                $a = explode('-', $chapter_topic);
                $name = sizeof($a)>1 ? $a[1] : $a[0];
                $topic_no = sizeof($a)>1 ? $a[0] : null;
                $x = chapter_topic::Create(
                    ['name'=>ucfirst($name), 'topic_no'=>$topic_no, 'chapter_id'=>$chapter->id]
                );
                array_push($topic, $x);
            } 
            // DB Commit
            ResponseCache::clear();
            DB::commit();
            return response()->json(['chapter' => chapter::with('subjects', 'subjects.echelons', 'subjects.branches')->where('id', $chapter->id)->first(), 'topic' => $topic]);
        } catch (Exception $e) {
            DB::rollback();
        }
    }

    public function chapter_edit(Request $request, $id)
    {
        // if class ten
        if ($request->echelon_id == 8) {
            return response()->json(['chapter' => [], 'topic' => []]);
        }
        DB::beginTransaction();
        try {
            $chapter = chapter::find($id);
            $chapter->name = $request->name;
            $chapter->subject_id = $request->subject_id;
            $chapter->chapter_no = $request->chapter_no;
            $chapter->save();
            $ids = [];
            foreach ($request->chapter_topics as $chapter_topic) {
                $a = explode('-', $chapter_topic);
                $name = sizeof($a)>1 ? $a[1] : $a[0];
                $topic_no = sizeof($a)>1 ? $a[0] : null;
                $item = chapter_topic::where('name', $name)->where('chapter_id', $chapter->id)->first();
                if ($item) {
                    $item->name = ucfirst($name);
                    $item->topic_no = $topic_no;
                    $item->save();
                    array_push($ids, $item->id);
                }
                else {
                    $item = chapter_topic::Create(
                        ['name' => ucfirst($name), 'topic_no' => $topic_no, 'chapter_id' => $chapter->id]
                    );
                    array_push($ids, $item->id);
                }
            }
            if (sizeof($request->chapter_topics)==0) {
                chapter_topic::where('chapter_id', $chapter->id)->delete();
            }
            if (sizeof($request->chapter_topics)>0) {
                chapter_topic::whereNotIn('id', $ids)->where('chapter_id', $chapter->id)->delete();
            }
            // DB Commit
            ResponseCache::clear();
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
        }
    }

    public function chapter_delete($id)
    {
        $user = Auth::guard('admin')->user();
        if ($user->hasRole('superadmin')) {            
            chapter::where('id',$id)->delete();
            ResponseCache::clear();
        }
        else {
            return response()->json('error', 422);
        }
    }
}
