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
            'clock_in' => [
                'required',
                'regex:/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/',
            ],
            'clock_out' => [
                'required',
                'regex:/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/',
                'after:clock_in',
            ],
            'breaks.*.start' => [
                'nullable',
                'regex:/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/',
                'after:clock_in',
            ],
            'breaks.*.end' => [
                'nullable',
                'regex:/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/',
            ],
            'reason' => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }

    public function messages()
    {
        return [
            'clock_in.required' => '出勤時間を入力してください',
            'clock_in.regex' => '出勤時間を正しい形式（00:00〜23:59）で入力してください',
            'clock_out.required' => '退勤時間を入力してください',
            'clock_out.regex' => '退勤時間を正しい形式（00:00〜23:59）で入力してください',
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'breaks.*.start.regex' => '休憩時間を0:00の形式で入力してください',
            'breaks.*.start.after' => '休憩時間が不適切な値です',
            'breaks.*.end.regex' => '休憩時間を正しい形式（00:00〜23:59）で入力してください',
            'breaks.*.end.after' => '休憩時間が不適切な値です',
            'reason.required' => '備考を記入してください',
            'reason.string' => '備考を文字列で入力してください',
            'reason.max' => '備考を255文字以内で入力してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $breaks = $this->input('breaks', []);

            // 開始と終了の前後関係チェック
            foreach ($breaks as $index => $break) {
                if (!empty($break['start']) && !empty($break['end'])) {
                    $start = \Carbon\Carbon::createFromFormat('H:i', $break['start']);
                    $end = \Carbon\Carbon::createFromFormat('H:i', $break['end']);
                    if ($end->lessThanOrEqualTo($start)) {
                        $validator->errors()->add("breaks.$index.end", '休憩終了時間は開始時間より後にしてください');
                    }
                }
            }

            // 重複チェック
            for ($i = 0; $i < count($breaks); $i++) {
                if (empty($breaks[$i]['start']) || empty($breaks[$i]['end'])) {
                    continue;
                }
                $startA = \Carbon\Carbon::createFromFormat('H:i', $breaks[$i]['start']);
                $endA   = \Carbon\Carbon::createFromFormat('H:i', $breaks[$i]['end']);

                for ($j = $i + 1; $j < count($breaks); $j++) {
                    if (empty($breaks[$j]['start']) || empty($breaks[$j]['end'])) {
                        continue;
                    }
                    $startB = \Carbon\Carbon::createFromFormat('H:i', $breaks[$j]['start']);
                    $endB   = \Carbon\Carbon::createFromFormat('H:i', $breaks[$j]['end']);

                    // ★ 重複判定 (A.start < B.end && B.start < A.end)
                    if ($startA->lt($endB) && $startB->lt($endA)) {
                        $validator->errors()->add("breaks.$i.start", "休憩時間が他の休憩と重複しています");
                        $validator->errors()->add("breaks.$j.start", "休憩時間が他の休憩と重複しています");
                    }
                }
            }
        });
    }
}
