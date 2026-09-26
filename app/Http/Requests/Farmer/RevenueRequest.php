<?php

namespace App\Http\Requests\Farmer;

use App\Http\Requests\ApiFormRequest;

class RevenueRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'period' => ['required', 'in:day,month,year'],
            'year' => ['required_if:period,day,month', 'integer', 'min:2000', 'max:2100'],
            'month' => ['required_if:period,day', 'integer', 'between:1,12'],
        ];
    }

    public function attributes(): array
    {
        return [
            'period' => 'Kỳ thống kê',
            'year' => 'Năm',
            'month' => 'Tháng',
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'period.in' => 'Kỳ thống kê phải là day, month hoặc year.',
            'year.required_if' => 'Năm không được để trống.',
            'month.required_if' => 'Tháng không được để trống.',
        ]);
    }
}
