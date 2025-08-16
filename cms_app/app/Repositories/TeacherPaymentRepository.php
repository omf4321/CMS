<?php

namespace App\Repositories;

use App\Model\Admin\teacher;
use App\Model\Admin\teacher_payment;
use App\Model\Admin\schedule_teacher_attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TeacherPaymentRepository
{
    public function getTeacherPayment($request, $teacherId)
    {
        $teacher = teacher::with(['per_class_payments', 'invigilator_payments', 'script_payments'])->findOrFail($teacherId);

        [$firstOfMonth, $endOfMonth] = $this->getMonthRange($request->year, $request->month);

        $teacherAttendance = $this->getTeacherAttendance($teacherId, $firstOfMonth, $endOfMonth);
        $teacherPayments = $this->getRecentTeacherPayments($teacherId);

        $paidAmount = $this->calculatePaidAmount($teacherPayments, $firstOfMonth);
        $dueAmount = $this->calculateDueAmount($teacherPayments);
        $previousDue = $this->calculatePreviousDue($teacherPayments);

        $paymentStatus = $this->getPaymentStatus($teacherPayments, $firstOfMonth);
        $paymentMsg = $this->getPaymentMessage($paymentStatus, $dueAmount);

        [$classPayment, $classTotal] = $this->calculateClassPayments($teacher, $teacherAttendance);
        [$invigilatorPayment, $invigilatorTotal] = $this->calculateInvigilatorPayments($teacher, $teacherAttendance);
        [$scriptPayment, $scriptTotal] = $this->calculateScriptPayments($teacher, $teacherAttendance);

        $finalTotal = $this->calculateFinalTotal($teacherPayments, $paymentStatus, $classTotal, $invigilatorTotal, $scriptTotal, $previousDue, $dueAmount, $paidAmount);

        return [
            'teacher_payment' => $teacherPayments,
            'class_payment' => $classPayment,
            'invigilator_payment' => $invigilatorPayment,
            'script_payment' => $scriptPayment,
            'due_amount' => $dueAmount,
            'class_payment_total' => $classTotal,
            'invigilator_payment_total' => $invigilatorTotal,
            'script_payment_total' => $scriptTotal,
            'final_total' => $finalTotal,
            'payment_msg' => $paymentMsg
        ];
    }

    public function saveTeacherPayment($request)
    {
        $user = Auth::guard('admin')->user();

        $payment = new teacher_payment();
        $payment->month = $request->year . '-' . $request->month . '-01';
        $payment->teacher_id = $request->teacher_id;
        $payment->payer_id = $user->id;
        $payment->amount = $request->amount;
        $payment->paid = $request->paid;
        $payment->status = $request->status;
        $payment->save();

        return $payment;
    }

    private function getMonthRange($year, $month)
    {
        $first = Carbon::parse("$year-$month-01")->format('Y-m-d');
        $end = Carbon::parse($first)->endOfMonth();
        return [$first, $end];
    }

    private function getTeacherAttendance($teacherId, $from, $to)
    {
        return schedule_teacher_attendance::where('teacher_id', $teacherId)
            ->whereHas('schedules', function ($query) use ($from, $to) {
                $query->whereBetween('date', [$from, $to]);
            })
            ->orderBy('type')
            ->with('batches', 'exam_lists')
            ->get();
    }

    private function getRecentTeacherPayments($teacherId)
    {
        return teacher_payment::where('teacher_id', $teacherId)
            ->orderBy('id', 'desc')
            ->whereDate('month', '>=', '2021-01-01')
            ->limit(3)
            ->get();
    }

    private function calculatePaidAmount($payments, $firstOfMonth)
    {
        return $payments->where('month', $firstOfMonth)->sum('paid');
    }

    private function calculateDueAmount($payments)
    {
        return $payments->count() ? $payments[0]->amount - $payments[0]->paid : 0;
    }

    private function calculatePreviousDue($payments)
    {
        return $payments->count() > 1 ? $payments[1]->amount - $payments[1]->paid : 0;
    }

    private function getPaymentStatus($payments, $firstOfMonth)
    {
        return $payments->count() && $payments[0]->month == $firstOfMonth ? 'due' : 'regular';
    }

    private function getPaymentMessage($status, $dueAmount)
    {
        if ($status === 'due' && $dueAmount > 0) return 'It is a due payment';
        if ($status === 'regular' && $dueAmount > 0) return 'Has previous month due.';
        return '';
    }

    private function calculateClassPayments($teacher, $attendance)
    {
        $classPayments = [];
        $total = 0;

        foreach ($attendance->where('type', 'teacher') as $record) {
            $index = $teacher->per_class_payments->search(fn($p) => $p->batch_id === $record->batch_id) ?? 0;
            $perClass = $teacher->per_class_payments[$index];
            $existingIndex = collect($classPayments)->search(fn($c) => $c->new_batch_id == $perClass->batch_id);

            if ($existingIndex === false) {
                $record->new_batch_id = $perClass->batch_id;
                $record->amount = $perClass->amount;
                $record->count_class = 1;
                $record->batch_name = $perClass->batch_id ? $record->batches->name : '';
                $classPayments[] = $record;
            } else {
                $classPayments[$existingIndex]->amount += $perClass->amount;
                $classPayments[$existingIndex]->count_class++;
            }

            $total += $perClass->amount;
        }

        return [$classPayments, $total];
    }

    private function calculateInvigilatorPayments($teacher, $attendance)
    {
        $invigilatorPayments = [];
        $total = 0;

        foreach ($attendance->where('type', 'invigilator') as $record) {
            $hours = floor($record->invigilator_minute / 60);
            $minutes = $record->invigilator_minute % 60;
            $halfHour = floor($minutes / 30);
            if ($minutes % 30 > 15) $halfHour++;
            $totalHours = $hours + ($halfHour * 0.5);

            $amount = $teacher->invigilator_payments ? $teacher->invigilator_payments->per_hour_amount * $totalHours : 0;

            if (!empty($invigilatorPayments)) {
                $invigilatorPayments[0]->amount += $amount;
                $invigilatorPayments[0]->count_class++;
                $invigilatorPayments[0]->total_minutes += $record->invigilator_minute;
            } else {
                $record->amount = $amount;
                $record->count_class = 1;
                $record->total_minutes = $record->invigilator_minute;
                $invigilatorPayments[] = $record;
            }

            $total += $amount;
        }

        return [$invigilatorPayments, $total];
    }

    private function calculateScriptPayments($teacher, $attendance)
    {
        $scriptPayments = [];
        $total = 0;

        foreach ($attendance->where('type', 'scripter') as $record) {
            $mark = $record->exam_lists->sub_full_mark;
            $scriptRates = $teacher->script_payments()->orderBy('marks_range')->get();

            $index = $scriptRates->search(fn($rate) => $rate->marks_range >= $mark);
            if ($index === false) $index = $scriptRates->count() - 1;

            $amount = $scriptRates[$index]->amount * $record->script_quantity ?? 0;
            $record->mark = $mark;
            $record->amount = $amount;
            $record->count_script = $record->script_quantity;

            $existingIndex = collect($scriptPayments)->search(fn($s) => $s->mark == $mark);

            if ($existingIndex !== false) {
                $scriptPayments[$existingIndex]->amount += $amount;
                $scriptPayments[$existingIndex]->count_script += $record->script_quantity;
            } else {
                $scriptPayments[] = $record;
            }

            $total += $amount;
        }

        return [$scriptPayments, $total];
    }

    private function calculateFinalTotal($payments, $status, $classTotal, $invigilatorTotal, $scriptTotal, $previousDue, $dueAmount, $paidAmount)
    {
        if ($payments->count() && $payments[0]->amount == $payments[0]->paid) {
            return 0;
        }
        if ($status == 'due') {
            return ($classTotal + $invigilatorTotal + $scriptTotal + $previousDue) - $paidAmount;
        }
        return $classTotal + $invigilatorTotal + $scriptTotal + $dueAmount;
    }
}
