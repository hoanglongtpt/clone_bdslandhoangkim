<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;

class ActivityController extends Controller
{
    public function index()
    {
        $activities = ActivityLog::with('user')
            ->when(! auth()->user()->isAdmin(), fn ($query) => $query->where('user_id', auth()->id()))
            ->latest()->paginate(50);

        return view('activities.index', compact('activities'));
    }
}
