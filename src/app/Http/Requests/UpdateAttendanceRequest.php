<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'clock_in' => 'required|date_format:H:i',
            'clock_out' => 'required|date_format:H:i|after:clock_in',
            'breaks.*.start' => 'nullable|date_format:H:i|after:clock_in',
            'breaks.*.end' => 'nullable|date_format:H:i|after:breaks.*.start',
            'reason' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'clock_in.required' => '出勤時間を入力してください',
            'clock_in.date_format' => '出勤時間を0:00の形式で入力してください',
            'clock_out.required' => '退勤時間を入力してください',
            'clock_out.date_format' => '退勤時間を0:00の形式で入力してください',
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'breaks.*.start.date_format' => '休憩時間を0:00の形式で入力してください',
            'breaks.*.start.after' => '休憩時間が不適切な値です',
            'breaks.*.end.date_format' => '休憩時間を0:00の形式で入力してください',
            'breaks.*.end.after' => '休憩時間が不適切な値です',
            'reason.required' => '備考を記入してください',
            'reason.string' => '備考を文字列で入力してください',
            'reason.max' => '備考を255文字以内で入力してください',
        ];
    }

    public function withValidator ($validator) {
        $validator->after(function ($validator) {
            $breaks = $this->input('breaks', []);
            foreach ($breaks as $index => $break) {
                if (!empty($break['start']) && !empty($break['end'])) {
                    $start = \Carbon\Carbon::createFromFormat('H:i', $break['start']);
                    $end = \Carbon\Carbon::createFromFormat('H:i', $break['end']);
                    if ($end->lessThanOrEqualTo($start)) {
                        $validator->errors()->add("breaks.$index.end", '休憩終了時間は開始時間より後にしてください');
                    }
                }
            }
        });
    }
}
