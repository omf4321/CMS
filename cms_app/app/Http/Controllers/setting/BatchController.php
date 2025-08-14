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
    }

    public function admin_batch_list()
    {   
        $admin_batch_list = batch::with('echelons', 'branches', 'groups', 'courses')->orderby('id', 'asc')->get();
        return response()->json(["admin_batch_list" => $admin_batch_list]);
    }

    public function admin_batch_add(Request $request)
    {
        // return response()->json($request->all());
        $validated = $this->validate($request, [
            'name'               => 'required|string|max:191',
            'description'        => 'nullable|string|max:191',
            'branch_id'          => 'required|integer|exists:branches,id',
            'echelon_id'         => 'required|integer|exists:echelons,id',
            'course_id'          => 'required|integer|exists:courses,id',
            'active'             => 'nullable|boolean',
            'group_id'           => 'required|integer',
            'reg_base'           => 'required|integer|min:0',
            'fee_type'           => 'required|in:monthly_fee,course_fee,both',
            'monthly_fee'        => 'nullable|integer|min:0',
            'course_fee'         => 'nullable|integer|min:0',
            'admission_fee'      => 'nullable|numeric|min:0',
            'monthly_fee_mode'   => 'required|in:current,previous',
            'admission_fee_mode' => 'required|in:seperate,combine',
            'start_from'         => 'required|date',
            'end_to'             => 'required|date|after_or_equal:start_from',
            'time'               => 'nullable|date_format:H:i'
        ]);

        $batch = Batch::create($validated);
        Cache::flush();
        return response()->json([
            'message' => 'Batch created successfully',
            'data'    => $batch
        ], 201);
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

