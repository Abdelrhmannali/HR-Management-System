<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Http\Requests\DestroyAttendanceRequest;
use App\Http\Requests\CheckInRequest;
use App\Http\Requests\CheckOutRequest;

use App\Models\Attendence;
use App\Models\Employee;
use App\Models\Holiday;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendenceController extends Controller
{
}