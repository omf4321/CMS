<?php
namespace App\Repositories;

use App\Model\Admin\batch;
use App\Model\Admin\echelon;
use App\Model\Admin\institution;
use App\Model\Admin\student;
use App\Model\Admin\visiting_list;
use App\Model\Admin\student_payment;
use App\Client;
use Cache;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManagerStatic as Intervention;
use Carbon\Carbon;

class StudentRepository
{
    public function getAdminStudentList($branch_id, $search_by_id = null, $search_by)
    {
        $search_by_field = null;
        if ($search_by_id) {
            if (in_array('batch', $search_by)) {
                $search_by_field = 'batch_id';
            }
            if (in_array('year', $search_by)) {
                $search_by_field = 'running_month';
            }
            if (in_array('echelon', $search_by)) {
                $search_by_field = 'echelon';
            }
        }
        $cacheKey = "student_list_{$branch_id}_{$search_by_id}";

        return Cache::rememberForever($cacheKey, function () use ($branch_id, $search_by_id, $search_by_field) {
            $query = student::with('subjects', 'institutions', 'batches.echelons', 'branches', 'users')
                ->where('branch_id', $branch_id)
                ->orderBy('reg_no', 'asc');
            if ($search_by_field == 'batch_id') {
                $query->where($search_by_field, $search_by_id);
            }
            if ($search_by_field == 'running_month') {
                $query->whereYear($search_by_field, $search_by_id);
            }
            if ($search_by_field == 'echelon') {
                $query->whereHas('batches', function ($q) use ($search_by_id) {
                    $q->where('echelon_id', $search_by_id);
                });
            }
            return $query->get();
        });
    }

    public function saveStudent($request, $id = null)
    {
        $institution_id = null;
        if ($request->institution_name) {
            $institution = institution::firstOrCreate(
                ['name' => $request->institution_name],
                ['name' => $request->institution_name, 'branch_id' => $request->branch_id]
            );
            $institution_id = $institution->id;
        }

        $running_month = $request->year . '-' . $request->running_month . '-01';

        if ($id) {
            $student = student::find($id);
            if (!$student) {
                return response()->json(['msg' => 'Student not found.'], 404);
            }
            if ($student->status == 'inactive') {
                return response()->json(['msg' => 'Failed to edit. The student is inactive.'], 422);
            }
        } else {
            $password = $request->password ?? rand(100000, 1000000);
            $user = new Client;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = bcrypt($password);
            $user->password_text = $password;
            $user->branch_id = $request->branch_id;
            $user->save();

            $student = new student;
            $student->client_id = $user->id;
            $student->status = 'present';
            $student->running_month = $running_month;
        }

        // Handle Client user update or creation if needed (existing logic) ...
        // [আপনার কোডের একই অংশ]

        // এখানে নতুন ফিল্ড গুলো ডাইনামিক অ্যাসাইন করুন
        $student_admission_field = config('setting.student_admission_field'); // অথবা আপনার ভেরিয়েবল

        // student_admission_field থেকে সব ফিল্ডের নাম বের করুন
        $fillableFields = $this->extractFieldNames($student_admission_field);
        // Date format changed
        if ($request->date_of_birth) {
            $request->date_of_birth = Carbon::parse($request->date_of_birth)->format('Y-m-d');
        }

        if ($request->date_of_admit) {
            $request->date_of_admit = Carbon::parse($request->date_of_admit)->format('Y-m-d');
        }
        foreach ($fillableFields as $field) {
            // $institution_id এবং $running_month আলাদা আগে থেকে সেট করছি, তাই শুধু অন্য গুলো রিকোয়েস্ট থেকে নেব
            if ($field === 'institution_id') {
                $student->institution_id = $institution_id;
            } elseif ($field === 'running_month') {
                $student->running_month = $running_month;
            } elseif ($request->has($field)) {
                $student->$field = $request->$field;
            }
        }

        $student->save();

        if (!$id) {
            // নতুন ইউজারের username আপডেট
            $user->username = $student->reg_no;
            $user->save();

            if ($request->visiting_list_id) {
                visiting_list::find($request->visiting_list_id)->delete();
            }
        }

        Cache::flush();

        return $student->load('subjects', 'institutions', 'batches.echelons', 'branches', 'users');
    }


        public function toggleStudentActiveStatus($request, $id)
    {
        $check_reg = student::where('reg_no', $request->reg_no)
                            ->where('status', '=', 'present')
                            ->count();

        $running_month = $request->year . '-' . $request->running_month . '-01';

        $student = student::find($id);

        if (!$student) {
            return ['error' => 'Student not found'];
        }

        if ($student->status == 'inactive') {
            return ['status' => 'inactive'];
        }

        if ($check_reg && $student->status == 'droped') {
            return ['status' => 'exist'];
        }

        if ($student->status == 'droped') {
            $student->status = 'present';
            $student->running_month = $running_month;
            $student->inactive_month = null;
            $student->admission_fee = null;
        } else {
            $student->status = 'droped';
            $student->inactive_month = $running_month;
        }

        $student->save();
        Cache::flush();

        return ['status' => 'success'];
    }

    public function transferStudent($request, $id)
    {
        $running_month = $request->year . '-' . $request->running_month . '-01';

        $student = student::find($id);

        if (!$student) {
            return ['error' => 'Student not found'];
        }

        $student->batch_id = $request->batch_id;
        $student->reg_no = $request->reg_no;
        $student->date_of_admit = $request->date_of_admit;
        $student->monthly_fee = $request->monthly_fee;
        $student->course_fee = $request->course_fee;
        $student->admission_fee = $request->admission_fee;
        $student->status = 'present';
        $student->admission_status = 'transfer';
        $student->running_month = $running_month;
        $student->inactive_month = null;

        if (!$student->client_id) {
            $user = new Client;
            $user->name = $student->name;
            $password = $student->mobile;
            $user->password = bcrypt($password);
            $user->password_text = $password;
            $user->branch_id = $student->branch_id;
            $user->save();
            $student->client_id = $user->id;
        } else {
            $user = Client::find($student->client_id);
        }

        $user->username = $request->reg_no;
        $user->save();

        $student->save();

        Cache::flush();

        $student = student::where('id', $id)
            ->with('subjects', 'institutions', 'batches.echelons', 'branches')
            ->first();

        return ['status' => 'success', 'student' => $student];
    }

    public function checkActiveRegNo($reg_no){
        return $exists = student::where('reg_no', $reg_no)->where('status', 'present')->exists();
    }

    public function getNextMaxRegNo($id)
    {
        $item = student::where('batch_id', $id)->where('status', '!=', 'inactive')->count();
        if ($item > 0) {
            $max_reg = student::selectRaw('reg_no')->where('batch_id', $id)->where('status', 'present')->max('reg_no');
            return $max_reg + 1;
        } else {
            return batch::selectRaw('reg_base')->where('id', $id)->max('reg_base') + 1;
        }
    }

    public function getLowestAvailableRegNo($batchId)
    {
        // Get all active reg_no's sorted ascending
        $activeRegNos = student::where('batch_id', $batchId)
                            ->where('status', '!=', 'inactive')
                            ->orderBy('reg_no', 'asc')
                            ->pluck('reg_no')
                            ->toArray();

        $reg_start = batch::selectRaw('reg_base')->where('id', $batchId)->max('reg_base') + 1;
        if (empty($activeRegNos)) {
            // If no active student, start from 1 or batch base no
            return $reg_start;
        }

        $expected = $reg_start; // or get batch base reg_no if needed

        foreach ($activeRegNos as $regNo) {
            if ($regNo != $expected) {
                // Found a gap, return this as available
                return $expected;
            }
            $expected++;
        }

        // No gaps found, so next available is max + 1
        return $expected;
    }

    public function uploadImage($request)
    {
        if (!$request->hasFile('photo')) {
            return ['error' => 'No photo file provided'];
        }

        $student = student::where('reg_no', $request->reg_no)
            ->where('status', 'present')->first();

        if (!$student) {
            return ['error' => 'Student not found of this reg no'];
        }

        $batch_name = $student->batches->name;

        $width = intval($request->width);
        $height = intval($request->height);
        $left = intval($request->left);
        $top = intval($request->top);
        $image = $request->file('photo');
        
        $img = Image::make($image);
        $img->crop($width, $height, $left, $top);

        if ($img->width() > 155) {
            $img->resize(155, null, function ($constraint) {
                $constraint->aspectRatio();
            });
        }

        $filename = \Str::random(16) . '.' . $image->getClientOriginalExtension();
        $path = public_path('storage/avatar/' . $batch_name);

        if (!file_exists($path)) {
            File::makeDirectory($path, 0777, true, true);
        }

        // Delete old photo
        File::delete($path . '/' . $student->photo);

        $img->save($path . '/' . $filename);

        $student->update(['photo' => $filename]);

        Cache::flush();

        return [
            'file_name' => $filename,
            'date_of_admit' => $student->date_of_admit,
            'id' => $student->id
        ];
    }

    public function filterAdmissionFields(array $allFields, array $choosableFields, array $selectedFields, array $groupMap) {
        // Expand selected groups into fields
        foreach ($groupMap as $groupKey => $fields) {
            if (in_array($groupKey, $selectedFields)) {
                $selectedFields = array_merge($selectedFields, $fields);
            }
        }

        $selectedFields = array_unique($selectedFields);
        $result = [];

        foreach ($allFields as $section) {
            if (isset($section['fields'])) {
                $section['fields'] = array_values(array_filter($section['fields'], function ($field) use ($choosableFields, $selectedFields) {
                    if (!in_array($field['name'], $choosableFields)) {
                        return true; // mandatory
                    }
                    return in_array($field['name'], $selectedFields);
                }));
            }

            if (isset($section['subsections'])) {
                $section['subsections'] = array_values(array_filter(array_map(function ($sub) use ($choosableFields, $selectedFields) {
                    $sub['fields'] = array_values(array_filter($sub['fields'], function ($field) use ($choosableFields, $selectedFields) {
                        if (!in_array($field['name'], $choosableFields)) {
                            return true;
                        }
                        return in_array($field['name'], $selectedFields);
                    }));
                    return $sub;
                }, $section['subsections']), function ($sub) {
                    return !empty($sub['fields']);
                }));
            }

            if (!empty($section['fields']) || !empty($section['subsections'])) {
                $result[] = $section;
            }
        }

        return $result;
    }

    // student_admission_field থেকে সব ফিল্ডের নাম বের করার জন্য ফাংশন
    public function extractFieldNames($sections) {
        $fields = [];

        foreach ($sections as $section) {
            // সরাসরি 'fields' থাকলে
            if (isset($section['fields'])) {
                foreach ($section['fields'] as $field) {
                    $fields[] = $field['name'];
                }
            }

            // যদি 'subsections' থাকে, রিকার্সিভলি কল দিন
            if (isset($section['subsections'])) {
                $fields = array_merge($fields, $this->extractFieldNames($section['subsections']));
            }
        }

        return $fields;
    }

    
}
