<?php

namespace App\Http\Controllers\fine;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Admin\branch;
use App\Model\Admin\fine_rule;
use Auth;

class FineController extends Controller
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

    public function get_fine_rule()
    {   
        $user = Auth::guard('admin')->user();
        if ($user->hasRole('superadmin') || $user->hasRole('admin')){
            $fine_rule = fine_rule::with('branches')
            ->orderby('name','asc')->get();
            return response()->json(["fine_rule"=>$fine_rule]);
        } else {
            $id = $user->branch_id;
            $fine_rule = fine_rule::with('branches')
            ->where('branch_id',$id)
            ->orderby('name','asc')->get();
            return response()->json(["fine_rule"=>$fine_rule]);
        }

    }

    public function add_fine_rule(Request $request)
    {
        // return response()->json($request->all());
        $this->validate($request, [
           'name'=> 'required',
           'branch_id'=> 'required',
           'name' => 'required',
           'fine_type' => 'required'
        ]);

        $fine = new fine_rule;
        $fine->name = $request->name;
        $fine->description = $request->description;
        $fine->branch_id = $request->branch_id;
        $fine->fine_type = $request->fine_type;
        $fine->collect_fine_on = $request->collect_fine_on;
        $fine->fine_amount = $request->fine_amount;
        $fine->return_amount = $request->return_amount ? $request->return_amount : 0;
        $fine->fine_value = $request->fine_value;
        $fine->return_value = $request->return_value ? $request->return_value : null;

        if ($request->fine_type == 'attendance') {
            $fine->fine_condition = 'absent';
            $fine->return_condition = 'present';
        } else if($request->fine_type == 'exam' && $request->fine_condition == 'absent') {
            $fine->fine_condition = 'absent';
            if ($request->has_return) {
                $fine->return_condition = $request->return_condition;
                $fine->return_value = $request->return_value;
                $fine->return_unit = 'percent';
                $fine->return_field = $request->return_field;
            }
        } else {
            $fine->fine_condition = $request->fine_condition;
            $fine->fine_value = $request->fine_value;
            $fine->fine_unit = 'percent';
            $fine->fine_field = $request->fine_field;
            if ($request->has_return) {
                $fine->return_condition = $request->return_condition;
                $fine->return_value = $request->return_value;
                $fine->return_unit = 'percent';
                $fine->return_field = $request->return_field;
            }
        }
        $fine->save();
    }

    public function update_fine_rule(Request $request, $id)
    {
        // return response()->json($request->all());
        $this->validate($request, [
           'name'=> 'required',
           'branch_id'=> 'required',
           'name' => 'required',
           'fine_type' => 'required'
        ]);

        $fine = fine_rule::find($id);
        $fine->name = $request->name;
        $fine->description = $request->description;
        $fine->branch_id = $request->branch_id;
        $fine->fine_type = $request->fine_type;
        $fine->collect_fine_on = $request->collect_fine_on;
        $fine->fine_amount = $request->fine_amount;
        $fine->return_amount = $request->return_amount ? $request->return_amount : 0;
        $fine->fine_value = $request->fine_value;
        $fine->return_value = $request->return_value ? $request->return_value : null;

        if ($request->fine_type == 'attendance') {
            $fine->fine_condition = 'absent';
            $fine->return_condition = 'present';
        } else if($request->fine_type == 'exam' && $request->fine_condition == 'absent') {
            $fine->fine_condition = 'absent';
            if ($request->has_return) {
                $fine->return_condition = $request->return_condition;
                $fine->return_value = $request->return_value;
                $fine->return_unit = 'percent';
                $fine->return_field = $request->return_field;
            }
        } else {
            $fine->fine_condition = $request->fine_condition;
            $fine->fine_value = $request->fine_value;
            $fine->fine_unit = 'percent';
            $fine->fine_field = $request->fine_field;
            if ($request->has_return) {
                $fine->return_condition = $request->return_condition;
                $fine->return_value = $request->return_value;
                $fine->return_unit = 'percent';
                $fine->return_field = $request->return_field;
            }
        }
        $fine->save();
        return response()->json(['fine_rule' => $fine]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete_fine_rule($id)
    {
        fine_rule::destroy($id);
    }
}

