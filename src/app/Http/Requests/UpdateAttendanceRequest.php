<?php

namespace App\Http\Requests;

use Carbon\Carbon;
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
                'before_or_equal:clock_out',
            ],
            'breaks.*.end' => [
                'nullable',
                'regex:/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/',
                'after:breaks.*.start',
                'before:clock_out',
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
            'breaks.*.start.before_or_equal' => '休憩時間が不適切な値です',
            'breaks.*.end.regex' => '休憩時間を正しい形式（00:00〜23:59）で入力してください',
            'breaks.*.end.after' => '休憩時間が不適切な値です',
            'breaks.*.end.before' => '休憩時間もしくは退勤時間が不適切な値です',
            'reason.required' => '備考を記入してください',
            'reason.string' => '備考を文字列で入力してください',
            'reason.max' => '備考を255文字以内で入力してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $breaks = $this->input('breaks', []);

            foreach ($breaks as $index => $break) {

                $start = $break['start'] ?? null;
                $end   = $break['end'] ?? null;
                if ($start && !$this->isValidTime($start)) continue;
                if ($end && !$this->isValidTime($end)) continue;
                if ($end && empty($start)) {
                    $validator->errors()->add("breaks.$index.start", '休憩開始時間を入力してください');
                }
                if ($start && $end) {
                    $startTime = Carbon::createFromFormat('H:i', $start);
                    $endTime   = Carbon::createFromFormat('H:i', $end);

                    if ($endTime->lessThanOrEqualTo($startTime)) {
                        $validator->errors()->add("breaks.$index.end", '休憩終了時間は開始時間より後にしてください');
                    }
                }
            }
            for ($i = 0; $i < count($breaks); $i++) {

                if (empty($breaks[$i]['start']) || empty($breaks[$i]['end'])) {
                    continue;
                }

                $startA = $this->safeCreateTime($breaks[$i]['start']);
                $endA   = $this->safeCreateTime($breaks[$i]['end']);
                if (!$startA || !$endA) continue;
                for ($j = $i + 1; $j < count($breaks); $j++) {
                    if (empty($breaks[$j]['start']) || empty($breaks[$j]['end'])) {
                        continue;
                    }
                    $startB = $this->safeCreateTime($breaks[$j]['start']);
                    $endB   = $this->safeCreateTime($breaks[$j]['end']);
                    if (!$startB || !$endB) continue;

                    if ($startA->lt($endB) && $startB->lt($endA)) {
                        $validator->errors()->add("breaks.$i.start", "休憩時間が他の休憩と重複しています");
                        $validator->errors()->add("breaks.$j.start", "休憩時間が他の休憩と重複しています");
                    }
                }
            }
        });
    }

    private function isValidTime($value)
    {
        return preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/', $value);
    }

    private function safeCreateTime($value)
    {
        if (!$this->isValidTime($value)) return null;
        return Carbon::createFromFormat('H:i', $value);
    }
}