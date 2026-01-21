<?php

namespace App\Http\Controllers\Appointment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AppoinmentCategoryController extends Controller
{
    public function index()
    {
        return view('appointments.categories.index');
    }
}
