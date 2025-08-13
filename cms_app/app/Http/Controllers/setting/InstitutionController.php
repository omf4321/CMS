<?php

namespace App\Http\Controllers\setting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Admin\branch;
use App\Model\Admin\institution;
use Auth;
use Spatie\ResponseCache\Facades\ResponseCache;

class InstitutionController extends Controller
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

    public function admin_institution_list()
    {   
        $user = Auth::guard('admin')->user();
        if ($user->hasRole('superadmin') || $user->hasRole('admin')){
            $admin_institution_list = institution::with('branches')
            ->orderby('name','asc')->get();
            return response()->json(["admin_institution_list"=>$admin_institution_list]);
        } else {
            $id = $user->branch_id;
            $admin_institution_list = institution::with('branches')
            ->where('branch_id',$id)
            ->orderby('name','asc')->get();
            return response()->json(["admin_institution_list"=>$admin_institution_list]);
        }

    }

    public function admin_institution_add(Request $request)
    {
        // return response()->json($request->all());
        $this->validate($request, [
           'name'=> 'required',
           'branch_id'=> 'required'
        ]);

        $admin_institution = new institution;
        $admin_institution->name = $request->name;
        $admin_institution->description = $request->description;
        $admin_institution->branch_id = $request->branch_id;
        $admin_institution->save();
        ResponseCache::clear();
    }

    public function admin_institution_edit(Request $request, $id)
    {
        $this->validate($request, [
           'name'=> 'required',
           'branch_id'=> 'required'
        ]);

        $admin_institution = institution::find($id);
        $admin_institution->name = $request->name;
        $admin_institution->description = $request->description;
        $admin_institution->branch_id = $request->branch_id;
        $admin_institution->save();
        ResponseCache::clear();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function admin_institution_delete($id)
    {
        institution::destroy($id);
        ResponseCache::clear();
    }
}

