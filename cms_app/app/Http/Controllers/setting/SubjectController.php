<?php

namespace App\Http\Controllers\setting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Admin\branch;
use App\Model\Admin\subject;
use Auth;
use Spatie\ResponseCache\Facades\ResponseCache;

class SubjectController extends Controller
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

    public function admin_subject_list()
    {   
        $user = Auth::guard('admin')->user();
        if ($user->hasRole('superadmin') || $user->hasRole('admin')) {
            $admin_subject_list = subject::with('echelons', 'branches', 'teachers')
            ->orderby('name','asc')->get();
            return response()->json(["admin_subject_list"=>$admin_subject_list]);
        } else {
            $id = $user->branch_id;
            $admin_subject_list = subject::with('echelons', 'branches', 'teachers')
            ->where('branch_id', $id)
            ->orderby('name', 'asc')->get();
            return response()->json(["admin_subject_list" => $admin_subject_list]);
        }
    }

    public function admin_subject_add(Request $request)
    {
        // return response()->json($request->all());
        $this->validate($request, [
           'name'=> 'required',
           'branch_id'=> 'required',
           'echelon_id'=> 'required'
        ]);

        $admin_subject = new subject;
        $admin_subject->name = $request->name;
        $admin_subject->bangla_text = $request->bangla_text;
        $admin_subject->branch_id = $request->branch_id;
        $admin_subject->echelon_id = $request->echelon_id;
        $admin_subject->teacher_id = $request->teacher_id;
        $admin_subject->save();
        ResponseCache::clear();
    }

    public function admin_subject_edit(Request $request, $id)
    {
        $this->validate($request, [
           'name'=> 'required',
           'branch_id'=> 'required',
           'echelon_id'=> 'required'
        ]);

        $admin_subject = subject::find($id);
        $admin_subject->name = $request->name;
        $admin_subject->bangla_text = $request->bangla_text;
        $admin_subject->branch_id = $request->branch_id;
        $admin_subject->echelon_id = $request->echelon_id;
        $admin_subject->teacher_id = $request->teacher_id;
        $admin_subject->save();
        ResponseCache::clear();
        return response()->json(['subject' => subject::where('id', $id)->with('echelons', 'branches', 'teachers')->first()]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function admin_subject_delete($id)
    {
        subject::destroy($id);
        ResponseCache::clear();
    }
}

