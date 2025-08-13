<?php

namespace App\Http\Controllers\setting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Admin\branch;
use App\Model\Admin\batch;
use App\Model\Admin\echelon;
use App\Model\Admin\course;
use App\Model\Admin\group;
use App\Model\Admin\student;
use Auth;
use Cache;


class BatchController extends Controller
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

    public function admin_batch_list()
    {   
        $admin_batch_list = batch::with('echelons', 'branches', 'groups', 'courses')->orderby('id', 'asc')->get();
        return response()->json(["admin_batch_list" => $admin_batch_list]);
    }

    public function admin_batch_add(Request $request)
    {
        // return response()->json($request->all());
        $this->validate($request, [
           'name'=> 'required',
           'branch_id'=> 'required',
           'echelon_id'=> 'required',
           'course_id'=> 'required',
           'group_id'=> 'required',
           'reg_base'=> 'required',
           'start_from'=> 'required',
           'end_to'=> 'required',
           'time'=> 'required'
        ]);

        $admin_batch = new batch;
        $admin_batch->name = $request->name;
        $admin_batch->description = $request->description;
        $admin_batch->branch_id = $request->branch_id;
        $admin_batch->echelon_id = $request->echelon_id;
        $admin_batch->course_id = $request->course_id;
        $admin_batch->group_id = $request->group_id;
        $admin_batch->reg_base = $request->reg_base;
        $admin_batch->monthly_fee = $request->monthly_fee;
        $admin_batch->course_fee = $request->course_fee;
        $admin_batch->start_from = $request->start_from;
        $admin_batch->end_to = $request->end_to;
        $admin_batch->time = $request->time;
        $admin_batch->save();
        Cache::flush();
    }

    public function admin_batch_edit(Request $request, $id)
    {
        $this->validate($request, [
           'name'=> 'required',
           'branch_id'=> 'required',
           'echelon_id'=> 'required',
           'course_id'=> 'required',
           'group_id'=> 'required',
           'reg_base'=> 'required',
           'start_from'=> 'required',
           'end_to'=> 'required',
           'time'=> 'required'
        ]);

        $admin_batch = batch::find($id);
        $admin_batch->name = $request->name;
        $admin_batch->description = $request->description;
        $admin_batch->branch_id = $request->branch_id;
        $admin_batch->echelon_id = $request->echelon_id;
        $admin_batch->course_id = $request->course_id;
        $admin_batch->group_id = $request->group_id;
        $admin_batch->reg_base = $request->reg_base;
        $admin_batch->monthly_fee = $request->monthly_fee;
        $admin_batch->course_fee = $request->course_fee;
        $admin_batch->start_from = $request->start_from;
        $admin_batch->end_to = $request->end_to;
        $admin_batch->time = $request->time;
        $admin_batch->save();
        Cache::flush();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function admin_batch_delete($id)
    {
        batch::destroy($id);
        Cache::flush();
    }

    public function inactive_student($id)
    {
        student::where('batch_id', $id)->update(['status' => 'inactive']);
        Cache::flush();
    }
}

