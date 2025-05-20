<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // Protect all routes
    }

    public function index()
    {
        return view('dashboard'); // Your task manager view
    }

    // Add other methods (store, update, etc.) here...
}
