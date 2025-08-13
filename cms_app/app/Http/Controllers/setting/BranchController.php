<?php

namespace App\Http\Controllers\setting;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Model\Admin\branch;
use Auth;

class BranchController extends Controller
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
    
    public function admin_branch_list()
    {
        $user = Auth::guard('admin')->user();
        if ($user->hasRole('superadmin') || $user->hasRole('admin')){
            $admin_branch_list = branch::all();
            return response()->json(['admin_branch_list'=>$admin_branch_list]);
        }
    }

    public function admin_branch_add(Request $request)
    {
        $user = Auth::guard('admin')->user();
        if ($user->hasRole('superadmin')){
            $this->validate($request, [
            	'name'=> 'required'
            ]);

            $admin_branch = new branch;
            $admin_branch->name = $request->name;
            $admin_branch->description = $request->description;
            $admin_branch->save();
        }
    }

    public function admin_branch_edit(Request $request, $id)
    {
    	$user = Auth::guard('admin')->user();
        if ($user->hasRole('superadmin')){
            $admin_branch = branch::find($id);
            $admin_branch->name = $request->name;
            $admin_branch->description = $request->description;
            $admin_branch->save();
        }
    }

    public function admin_branch_delete($id)
    {
        $user = Auth::guard('admin')->user();
        if ($user->hasRole('superadmin')){
            branch::where('id',$id)->delete();
        }
    }
}
