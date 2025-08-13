<?php

namespace App\Imports;

use App\User;
use App\Model\Admin\exam_import;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Model\Admin\student;
use App\Model\Admin\daily_schedule;
use App\Model\Admin\student_exam_mark;

class ExamImport implements ToCollection, WithHeadingRow
{
    // public function mapping(): array
    // {
    //     return [
    //         'exam_code'  => 'B1',
    //         'exam_set' => 'B2',
    //         'reg_no' => 'B3',
    //     ];
    // }
    public function  __construct($schedule_id)
    {
        $this->schedule_id = $schedule_id;

    }

    private $unfound_student = [];      
    
    public function collection(Collection $rows)
    {                    
        $schedule = daily_schedule::where('id', $this->schedule_id)->with('exam_lists')->first();

        $student_list = student::where('batch_id', $schedule->batch_id)->where('status', 'present')->get();
        foreach ($student_list as $key => $student) {
            $std = student_exam_mark::firstOrCreate(
                ['student_id' =>  $student->id, 'date' => $schedule->date, 'subject_id' => $schedule->subject_id],
                ['student_id' =>  $student->id, 'date' => $schedule->date, 'subject_id' => $schedule->subject_id]
            );
        }  

        foreach ($rows as $key => $row) 
        {   
            $roll = ltrim($row['roll_no'], '0');
            $student = student::where('reg_no', $roll)->where('batch_id', $schedule->batch_id)->first();

            if ($student && $schedule) {
                exam_import::where('schedule_id', $schedule->id)->where('student_id', $student->id)->delete();
                for ($i = 1; $i <= $schedule->exam_lists->ob_full_mark; $i++) { 
                    $option_row_name = 'q_'.$i.'_options';
                    $key_row_name = 'q_'.$i.'_key';
                    $marks_row_name = 'q_'.$i.'_marks';
                    exam_import::Create(
                    [
                        'schedule_id' => $schedule->id,
                        'student_id' => $student->id,
                        'question_no' => $i,
                        'option' => $row[$option_row_name],
                        'key' => $row[$key_row_name],
                        'marks' => $row[$marks_row_name]
                    ]);
                }
                $mark = student_exam_mark::where('date', $schedule->date)->where('subject_id', $schedule->subject_id)->where('student_id', $student->id)->first();
                if ($mark) {
                    $mark->ob_mark = $row['correct_answers'];
                    $mark->save();
                }
            } else {
                if (!$student) {
                    array_push($this->unfound_student, $roll);
                }
            }
        } //end foreach
    }

    public function getRowCount(): Array
    {
        return $this->unfound_student;
    }


    // exam_import::create([
    //     'exam_id' => $exam_list->id,
    //     'student_id' => $student->id,
    //     'correct_answers' => $row['correct_answers']
    // ]);
}