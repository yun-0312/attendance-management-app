<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    protected $dates = ['date'];

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
}