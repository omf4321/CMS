<?php

namespace App\Http\Controllers\setting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Admin\branch;
use App\Model\Admin\group;
use Auth;
use Spatie\ResponseCache\Facades\ResponseCache;

class GroupController extends Controller
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

    public function admin_group_list()
    {   
        $user = Auth::guard('admin')->user();
        if ($user->hasRole('superadmin') || $user->hasRole('admin')){
            $admin_group_list = group::with('branches')
            ->orderby('name','asc')->get();
            return response()->json(["admin_group_list"=>$admin_group_list]);
        } else {
            $id = $user->branch_id;
            $admin_group_list = group::with('branches')
            ->where('branch_id',$id)
            ->orderby('name','asc')->get();
            return response()->json(["admin_group_list"=>$admin_group_list]);
        }
    }

    public function admin_group_add(Request $request)
    {
        // return response()->json($request->all());
        $this->validate($request, [
           'name'=> 'required',
           'branch_id'=> 'required'
        ]);

        $admin_group = new group;
        $admin_group->name = $request->name;
        $admin_group->description = $request->description;
        $admin_group->branch_id = $request->branch_id;
        $admin_group->save();
        ResponseCache::clear();
    }

    public function admin_group_edit(Request $request, $id)
    {
        $this->validate($request, [
           'name'=> 'required',
           'branch_id'=> 'required'
        ]);

        $admin_group = group::find($id);
        $admin_group->name = $request->name;
        $admin_group->description = $request->description;
        $admin_group->branch_id = $request->branch_id;
        $admin_group->save();
        ResponseCache::clear();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function admin_group_delete($id)
    {
        group::destroy($id);
        ResponseCache::clear();
    }
}

