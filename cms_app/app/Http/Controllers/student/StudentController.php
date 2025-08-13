<?php

namespace App\Http\Controllers\student;

use DB;
use Auth;
use Cache;
use App\Client;
use Intervention;
use Carbon\Carbon;
use App\Model\Admin\batch;
use App\Model\Admin\echelon;
use App\Model\Admin\student;
use App\Model\Admin\subject;
use Illuminate\Http\Request;
use App\Model\Admin\institution;
use App\Model\Admin\visiting_list;
use App\Http\Controllers\Controller;
use App\Model\Admin\student_payment;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\sms\sms_trait;
use App\Repositories\StudentRepository;
use App\Repositories\VisitingListRepository;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    use sms_trait;
    
    protected $studentRepo;
    protected $visitingListRepo;

    public function __construct(StudentRepository $studentRepo, VisitingListRepository $visitingListRepo)
    {
        $this->middleware('auth:admin');
        $this->studentRepo = $studentRepo;
        $this->visitingListRepo = $visitingListRepo;
    }

    public function initialize_student_form()
    {
        $all_fields = config('setting.student_admission_field');
        $choosableFields = config('setting.choosable_fields');
        $groupMap = config('setting.groupMap');
        $userSelected = Auth::guard('admin')->user()->branches->organizations->admission_fields;

        $filtered = $this->studentRepo->filterAdmissionFields($all_fields, $choosableFields, json_decode($userSelected), $groupMap);

        return response()->json(['admission_fields' => $filtered]);
    }

    public function admin_student_list($branch_id, $search_by_id = null)
    {
        $search_by = config('setting.search_student_by');
        $admin_student_list = $this->studentRepo->getAdminStudentList($branch_id, $search_by_id, $search_by);
        return response()->json(['admin_student_list' => $admin_student_list]);
    }

    public function admin_student_add(Request $request)
    {
        $check_reg = $this->studentRepo->checkActiveRegNo($request->reg_no);
        if ($check_reg) {
            return response()->json(['msg' => 'A active student exist of this reg no'], 422);
        }

        $student = $this->studentRepo->saveStudent($request);

        return response()->json(['student' => $student]);
    }

    public function admin_student_edit(Request $request, $id)
    {
        $student = $this->studentRepo->saveStudent($request, $id);
        return response()->json(['student' => $student]);
    }

    public function admin_student_active(Request $request, $id)
    {
        $result = $this->studentRepo->toggleStudentActiveStatus($request, $id);

        if (isset($result['error'])) {
            return response()->json(['msg' => $result['error']], 404);
        }
        return response()->json(['admin_student_active' => $result['status']]);
    }
    
    public function admin_student_transfer(Request $request, $id)
    {
        $check_reg = $this->studentRepo->checkActiveRegNo($request->reg_no);
        if ($check_reg) {
            return response()->json(['admin_student_transfer'=>'exist']);
        }
        $result = $this->studentRepo->transferStudent($request, $id);

        if (isset($result['error'])) {
            return response()->json(['msg' => $result['error']], 404);
        }
        return response()->json(['student' => $result['student']]);
    }

    public function admin_student_delete($id)
    {
        $student = student::find($id);
        if ($student->payments()->exists()) {
            return response()->json(['msg'=> 'He already paid. You can not delete.'], 422);
        }
        $student->delete();
        Cache::flush();
    }

    public function get_reg_droped($id)
    {
        return response()->json(['get_reg_droped' => $this->studentRepo->getLowestAvailableRegNo($id)]);
    }

    public function get_reg_max($id)
    {
        return response()->json(['get_reg_max' => $this->studentRepo->getNextMaxRegNo($id)]);
    }

    public function reg_no_check($reg_no)
    {
        $check_reg = $this->studentRepo->checkActiveRegNo($reg_no);
        if ($check_reg) {
            return response()->json(['reg_no_check' => 1]);
        } else {
            return response()->json(['reg_no_check' => 0]);
        }
    }

    public function upload_image(Request $request)
    {
        $result = $this->studentRepo->uploadImage($request);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 422);
        }

        return response()->json($result);
    }

    // =============================== visiting list =======================================
    // =====================================================================================
    public function get_visiting_list($branch_id)
    {
        $visiting_list = $this->visitingListRepo->getVisitingList($branch_id);
        return response()->json(['visiting_list' => $visiting_list]);
    }

    public function add_visiting_list(Request $request)
    {
        $visiting_list = $this->visitingListRepo->addVisitingList($request);
        return response()->json(['visiting_list' => $visiting_list]);
    }

    public function edit_visiting_list(Request $request, $id)
    {
        $visiting_list = $this->visitingListRepo->editVisitingList($request, $id);
        return response()->json(['visiting_list' => $visiting_list]);
    }

    public function delete_visiting_list($id)
    {
        $this->visitingListRepo->deleteVisitingList($id);
        return response()->json(['message' => 'Deleted successfully']);
    }

    public function new_student_from_visiting_list($visiting_list_id)
    {
        $visiting_list = $this->visitingListRepo->getVisitingListById($visiting_list_id);
        return response()->json(['visiting_list' => $visiting_list]);
    }

    public function send_visiting_list_sms(Request $request){
        
    }

}
