<?php
namespace App\Repositories;

use App\Model\Admin\visiting_list;
use App\Model\Admin\institution;
use Illuminate\Support\Facades\Cache;

class VisitingListRepository
{
    public function getVisitingList($branch_id)
    {
        return Cache::rememberForever("visiting_list_{$branch_id}", function () use ($branch_id) {
            return visiting_list::with('branches', 'echelons', 'institutions')
                ->where('branch_id', $branch_id)
                ->get();
        });
    }

    public function addVisitingList($request)
    {
        $institution_id = null;
        if ($request->institution_name) {
            $institution = institution::firstOrCreate(
                ['name' => $request->institution_name],
                ['branch_id' => $request->branch_id]
            );
            $institution_id = $institution->id;
        }

        $visiting_list = new visiting_list;
        $visiting_list->name = $request->name;
        $visiting_list->branch_id = $request->branch_id;
        $visiting_list->echelon_id = $request->echelon_id;
        $visiting_list->gender = $request->gender;
        $visiting_list->institution_id = $institution_id;
        $visiting_list->school_roll = $request->school_roll;
        $visiting_list->father_name = $request->father_name;
        $visiting_list->mother_name = $request->mother_name;
        $visiting_list->mobile = $request->mobile;
        $visiting_list->mobile2 = $request->mobile2;
        $visiting_list->address = $request->address;
        $visiting_list->save();

        Cache::forget('visiting_list_' . $request->branch_id);

        return $visiting_list;
    }

    public function editVisitingList($request, $id)
    {
        $institution_id = null;
        if ($request->institution_name) {
            $institution = institution::firstOrCreate(
                ['name' => $request->institution_name],
                ['branch_id' => $request->branch_id]
            );
            $institution_id = $institution->id;
        }

        $visiting_list = visiting_list::find($id);
        $visiting_list->name = $request->name;
        $visiting_list->branch_id = $request->branch_id;
        $visiting_list->echelon_id = $request->echelon_id;
        $visiting_list->gender = $request->gender;
        $visiting_list->institution_id = $institution_id;
        $visiting_list->school_roll = $request->school_roll;
        $visiting_list->father_name = $request->father_name;
        $visiting_list->mother_name = $request->mother_name;
        $visiting_list->mobile = $request->mobile;
        $visiting_list->mobile2 = $request->mobile2;
        $visiting_list->address = $request->address;
        $visiting_list->save();

        Cache::forget('visiting_list_' . $request->branch_id);

        return visiting_list::where('id', $id)
            ->with('branches', 'echelons', 'institutions')
            ->first();
    }

    public function deleteVisitingList($id)
    {
        visiting_list::find($id)->delete();
        Cache::forget('visiting_list');
    }

    public function getVisitingListById($visiting_list_id)
    {
        return visiting_list::where('id', $visiting_list_id)
            ->with('branches', 'echelons', 'institutions')
            ->first();
    }
}

