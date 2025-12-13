<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\AttendanceRequest;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'status',
    ];

    protected $casts = [
        'work_date' => 'date', 
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function attendanceRequests()
    {
        return $this->hasMany(AttendanceRequest::class);
    }

    public function breakTimeRequests()
    {
        return $this->hasMany(BreakTimeRequest::class);
    }

    public function getCurrentStatusAttribute()
    {
        if ($this->clock_out) {
            return '退勤済';
        }
        if ($this->breakTimes()->whereNull('break_end')->exists()) {
            return '休憩中';
        }
        if ($this->clock_in && !$this->clock_out) {
            return '勤務中';
        }
        return '勤務外';
    }

    public static function todayForUser($userId)
    {
        return static::where('user_id', $userId)
            ->whereDate('clock_in', today())
            ->first();
    }

    // 勤務時間合計計算
    public function getTotalWorkTimeAttribute()
    {
        if (!$this->clock_in || !$this->clock_out) {
            return null;
        }
        $totalMinutes = $this->clock_out->diffInMinutes($this->clock_in)
            - $this->breakTimes->sum(function($b){
                return $b->break_end
                    ? $b->break_end->diffInMinutes($b->break_start)
                    : 0;
            });
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        return sprintf('%d:%02d', $hours, $minutes);
    }

    //月ごとの勤怠取得
    public static function forMonth($userId, $year, $month)
    {
        $start = Carbon::create($year, $month, 1);
        $end   = $start->copy()->endOfMonth();

        return static::where('user_id', $userId)
            ->with('breakTimes')
            ->whereBetween('work_date', [$start, $end])
            ->get()
            ->keyBy(function ($attendance) {
                return $attendance->work_date->format('Y-m-d');
            });
    }

    //休憩時間取得
    public function getTotalBreakTimeAttribute()
    {
        $totalMinutes = $this->breakTimes->sum(function($b){
            return $b->break_end
                ? $b->break_end->diffInMinutes($b->break_start)
                : 0;
        });
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        return sprintf('%d:%02d', $hours, $minutes);
    }

    //指定年月の Carbon インスタンスを返す
    public static function monthDate($year, $month)
    {
        return Carbon::create($year, $month, 1);
    }

    // 前月を返す
    public static function prevMonth($year, $month)
    {
        return self::monthDate($year, $month)->subMonth();
    }

    //次月を返す
    public static function nextMonth($year, $month)
    {
        return self::monthDate($year, $month)->addMonth();
    }

    //変更があるかチェック
    public function isChangedFromOriginal($request)
    {
        $date = $this->work_date->toDateString();
        // 出勤・退勤チェック
        $newClockIn  = Carbon::parse("$date {$request->clock_in}");
        $newClockOut = Carbon::parse("$date {$request->clock_out}");
        if (!$this->clock_in->equalTo($newClockIn)) {
            return true;
        }
        if (!$this->clock_out->equalTo($newClockOut)) {
            return true;
        }
        // 休憩チェック
        $breaks = $request->breaks ?? [];
        foreach ($breaks as $index => $break) {
            // 新規追加された休憩
            if (!empty($break['start']) || !empty($break['end'])) {
                if (!isset($this->breakTimes[$index])) {
                    return true;
                }
            }
            // 既存の休憩の変更チェック
            if (isset($this->breakTimes[$index])) {
                $oldStart = optional($this->breakTimes[$index]->break_start)->format('H:i');
                $oldEnd   = optional($this->breakTimes[$index]->break_end)->format('H:i');

                $newStart = $break['start'] ?? null;
                $newEnd   = $break['end'] ?? null;

                if ($oldStart !== $newStart || $oldEnd !== $newEnd) {
                    return true;
                }
            }
        }
        return false;
    }

    //修正申告が未承認の場合、承認待ちの情報取得
    public function latestPendingRequest() {
        return $this->attendanceRequests()
            ->where('status', 'pending')
            ->latest()
            ->with('breakTimeRequests')
            ->first();
    }
}