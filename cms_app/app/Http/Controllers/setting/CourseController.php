<?php

namespace App\Http\Controllers\setting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Admin\branch;
use App\Model\Admin\course;
use Auth;
use Spatie\ResponseCache\Facades\ResponseCache;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('cacheResponse');
    }

    public function admin_course_list()
    {   
        $user = Auth::guard('admin')->user();
        if ($user->hasRole('superadmin') || $user->hasRole('admin')){
            $admin_course_list = course::with('branches')
            ->orderby('name','asc')->get();
            return response()->json(["admin_course_list"=>$admin_course_list]);
        } else {
            $id = $user->branch_id;
            $admin_course_list = course::with('branches')
            ->where('branch_id',$id)
            ->orderby('name','asc')->get();
            return response()->json(["admin_course_list"=>$admin_course_list]);
        }

    }

    public function admin_course_add(Request $request)
    {
        // return response()->json($request->all());
        $this->validate($request, [
           'name'=> 'required',
           'branch_id'=> 'required'
        ]);

        $admin_course = new course;
        $admin_course->name = $request->name;
        $admin_course->description = $request->description;
        $admin_course->branch_id = $request->branch_id;
        $admin_course->save();
        ResponseCache::clear();
    }

    public function admin_course_edit(Request $request, $id)
    {
        $this->validate($request, [
           'name'=> 'required',
           'branch_id'=> 'required'
        ]);

        $admin_course = course::find($id);
        $admin_course->name = $request->name;
        $admin_course->description = $request->description;
        $admin_course->branch_id = $request->branch_id;
        $admin_course->save();
        ResponseCache::clear();

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function admin_course_delete($id)
    {
        course::destroy($id);
        ResponseCache::clear();
    }
}

