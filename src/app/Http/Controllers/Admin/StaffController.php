<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class StaffController extends Controller
{
    public function index () {
        $users = User::where('role', 'user')->orderBy('id')->get();
        return view('admin.staff.index', compact('users'));
    }
}
