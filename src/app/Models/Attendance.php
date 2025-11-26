<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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

    public function getCurrentStatusAttribute()
    {
        // 退勤済
        if ($this->clock_out) {
            return '退勤済';
        }
        // 休憩中：break_times の中で break_end が null のものがある
        if ($this->breakTimes()->whereNull('break_end')->exists()) {
            return '休憩中';
        }
        // 出勤中（clock_in はあるが clock_out がない）
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
        $start = \Carbon\Carbon::create($year, $month, 1);
        $end   = $start->copy()->endOfMonth();

        return static::where('user_id', $userId)
            ->with('breakTimes')
            ->whereBetween('work_date', [$start, $end])
            ->get()
            ->keyBy('work_date');
    }

    //休憩時間取得
    public function getTotalBreakTimeAttribute()
    {
        return $this->breakTimes->sum(function($b){
            return $b->break_end
                ? $b->break_end->diffInMinutes($b->break_start)
                : 0;
        });
    }

    /**
     * 指定年月の Carbon インスタンスを返す
     */
    public static function monthDate($year, $month)
    {
        return Carbon::create($year, $month, 1);
    }

    /**
     * 前月を返す
     */
    public static function prevMonth($year, $month)
    {
        return self::monthDate($year, $month)->subMonth();
    }

    /**
     * 次月を返す
     */
    public static function nextMonth($year, $month)
    {
        return self::monthDate($year, $month)->addMonth();
    }
}