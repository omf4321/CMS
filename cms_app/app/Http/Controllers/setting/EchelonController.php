<?php

namespace App\Http\Controllers\setting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Admin\branch;
use App\Model\Admin\echelon;
use Auth;
use Cache;

class EchelonController extends Controller
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

    public function admin_echelon_list()
    {   
        $user = Auth::guard('admin')->user();
        if ($user->hasRole('superadmin') || $user->hasRole('admin')) {
            $admin_echelon_list = echelon::with('branches')
            ->orderby('class_no','asc')->get();
            return response()->json(["admin_echelon_list"=>$admin_echelon_list]);
        }

    }

    public function admin_echelon_add(Request $request)
    {
        // return response()->json($request->all());
        $this->validate($request, [
           'name'=> 'required',
           'class_no'=> 'required',
           'branch_id'=> 'required'
        ]);

        $admin_echelon = new echelon;
        $admin_echelon->name = $request->name;
        $admin_echelon->class_no = $request->class_no;
        $admin_echelon->bangla_text = $request->bangla_text;
        $admin_echelon->branch_id = $request->branch_id;
        $admin_echelon->save();
        Cache::flush();
    }

    public function admin_echelon_edit(Request $request, $id)
    {
    	$this->validate($request, [
           'name'=> 'required',
           'class_no'=> 'required',
           'branch_id'=> 'required'
        ]);

        $admin_echelon = echelon::find($id);
        $admin_echelon->name = $request->name;
        $admin_echelon->class_no = $request->class_no;
        $admin_echelon->bangla_text = $request->bangla_text;
        $admin_echelon->branch_id = $request->branch_id;
        $admin_echelon->save();
        Cache::flush();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function admin_echelon_delete($id)
    {
        echelon::destroy($id);
        Cache::flush();
    }
}

