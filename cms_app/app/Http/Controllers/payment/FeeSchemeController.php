<?php

namespace App\Http\Controllers\payment;

use App\Http\Controllers\Controller;
use App\Repositories\FeeSchemeRepository;
use Illuminate\Http\Request;

class FeeSchemeController extends Controller
{
    protected $feeSchemes;

    public function __construct(FeeSchemeRepository $feeSchemes)
    {
        $this->feeSchemes = $feeSchemes;
    }

    public function index()
    {
        return response()->json($this->feeSchemes->getAll());
    }

    public function show($id)
    {
        return response()->json($this->feeSchemes->find($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:monthly_fee_rule,course_fee_rule,late_fine,other',
            'description' => 'nullable|string',
            'batch_ids' => 'nullable|array',
            'batch_ids.*' => 'integer',
            'fixed_amount' => 'nullable|numeric|min:0',
            'percent_amount' => 'nullable|numeric|min:0|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'validity_period' => 'required|in:forever,date_range',
            'recurring' => 'boolean',
            'recurrence_cycle' => 'nullable|in:daily,weekly,monthly,yearly',
            'apply_on' => 'required|in:admission,admission_post_admission,post_admission',
            'recurrence_interval_days' => 'nullable|integer|min:0',
            'max_amount' => 'nullable|numeric|min:0'
        ]);

        // Store as JSON if batch_ids provided
        if (isset($data['batch_ids'])) {
            $data['batch_ids'] = json_encode($data['batch_ids']);
        }

        $scheme = $this->feeSchemes->create($data);

        return response()->json(['message' => 'Fee Scheme created successfully', 'data' => $scheme], 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'type' => 'sometimes|required|in:monthly_fee_rule,course_fee_rule,late_fine,other',
            'description' => 'nullable|string',
            'batch_ids' => 'nullable|array',
            'batch_ids.*' => 'integer',
            'fixed_amount' => 'nullable|numeric|min:0',
            'percent_amount' => 'nullable|numeric|min:0|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'validity_period' => 'sometimes|required|in:forever,date_range',
            'recurring' => 'boolean',
            'recurrence_cycle' => 'nullable|in:daily,weekly,monthly,yearly',
            'apply_on' => 'sometimes|required|in:admission,admission_post_admission,post_admission',
            'recurrence_interval_days' => 'nullable|integer|min:0',
            'max_amount' => 'nullable|numeric|min:0'
        ]);

        if (isset($data['batch_ids'])) {
            $data['batch_ids'] = json_encode($data['batch_ids']);
        }

        $scheme = $this->feeSchemes->update($id, $data);

        return response()->json(['message' => 'Fee Scheme updated successfully', 'data' => $scheme]);
    }

    public function destroy($id)
    {
        $this->feeSchemes->delete($id);

        return response()->json(['message' => 'Fee Scheme deleted successfully']);
    }
}
