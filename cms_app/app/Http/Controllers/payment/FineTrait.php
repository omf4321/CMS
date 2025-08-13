<?php

namespace App\Http\Controllers\payment;

use App\Http\Controllers\Controller;
use App\Model\Admin\student;
use App\Model\Admin\fine_rule;
use App\Model\Admin\return_rule;
use Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;



trait FineTrait
{

    public function __construct()
    {
        $this->middleware('admin');
    }

    public function create_fine_rule($request){
        $rule = new fine_rule;
        $rule->branch_id = $request->branch_id;
        $rule->type = $request->type;
        $rule->condition = $request->condition;
        $rule->value = $request->value;
        $rule->unit = $request->unit;
        $rule->with_field = $request->with_field;
        $rule->amount = $request->amount;
        $rule->save();
        return $rule;
    }

    public function update_fine_rule($request, $id){
        $rule = fine_rule::find($id);
        $rule->branch_id = $request->branch_id;
        $rule->type = $request->type;
        $rule->condition = $request->condition;
        $rule->value = $request->value;
        $rule->unit = $request->unit;
        $rule->with_field = $request->with_field;
        $rule->amount = $request->amount;
        $rule->save();
        return $rule;
    }

    public function create_return_rule($request){
        $rule = new return_rule;
        $rule->branch_id = $request->branch_id;
        $rule->fine_type = $request->fine_type;
        $rule->return_type = $request->return_type;
        $rule->allow_previous_fine = $request->allow_previous_fine;
        $rule->condition = $request->condition;
        $rule->value = $request->value;
        $rule->unit = $request->unit;
        $rule->with_field = $request->with_field;
        $rule->amount = $request->amount;
        $rule->save();
        return $rule;
    }

    public function update_return_rule($request, $id){
        $rule = return_rule::find($id);
        $rule->branch_id = $request->branch_id;
        $rule->fine_type = $request->fine_type;
        $rule->return_type = $request->return_type;
        $rule->allow_previous_fine = $request->allow_previous_fine;
        $rule->condition = $request->condition;
        $rule->value = $request->value;
        $rule->unit = $request->unit;
        $rule->with_field = $request->with_field;
        $rule->amount = $request->amount;
        $rule->save();
        return $rule;
    }

    public function get_fine_rule(){
        $rules = fine_rule::all();
        return $rules;
    }

    public function get_return_rule(){
        $rules = return_rule::all();
        return $rules;
    }

    public function update_student_fine($data, $rules){
        if ($data->action_type == 'attendance') {           
            if (!$data->attendance && sizeof($rules)) { //if absent and has attendance fine rules
                foreach ($rules as $key => $rule) {
                    if ($rule->value == $data->student->attendance_fine_counter + 1) {
                        // create a fine and update fine to 

                        if ($key == sizeof($rules) - 1) {
                            student::where('id', $data->student->id)->update(['attendance_fine_counter' => 0, 'fine' => \DB::raw('fine +' . $rule->amount, 'attendance_return_counter' => 0)]);
                        } else {
                            student::where('id', $data->student->id)->update(['attendance_fine_counter' => $rule->value, 'fine' => \DB::raw('fine +' . $rule->amount, 'attendance_return_counter' => 0)]);
                        }
                        $counter_on_rule = 'true';
                    }
                }
                if (!isset($counter_on_rule)) {
                    student::where('id', $data->student->id)->update(['attendance_fine_counter' => \DB::raw('attendance_fine_counter +' . 1, 'attendance_return_counter' => 0)]);
                }
            } // end of absent 

            if (!$data->attendance && sizeof($rules)) { //if present and has attendance fine rules
                foreach ($rules as $key => $rule) {
                    if ($rule->return_value == $data->student->attendance_return_counter + 1) {
                        // create a fine and update fine to 

                        if ($key == sizeof($rules) - 1) {
                            student::where('id', $data->student->id)->update(['attendance_fine_counter' => 0, 'return' => \DB::raw('return +' . $rule->return_amount, 'attendance_return_counter' => 0)]);
                        } else {
                            student::where('id', $data->student->id)->update(['attendance_fine_counter' => 0, 'fine' => \DB::raw('fine +' . $rule->amount, 'attendance_return_counter' => $rule->return_value)]);
                        }
                        $counter_on_rule = 'true';
                    }
                }
                if (!isset($counter_on_rule)) {
                    student::where('id', $data->student->id)->update(['attendance_return_counter' => \DB::raw('attendance_fine_counter + 1', 'attendance_fine_counter' => 0)]);
                }
            } // end of present
        } 

        
    }
    
}
