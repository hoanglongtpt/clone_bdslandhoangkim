<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $search = $request->string('employee')->trim()->toString();
        $activities = ActivityLog::query()
            ->with('user')
            ->when(! $user->isAdmin(), fn (Builder $query) => $query->where('user_id', $user->id))
            ->when($user->isAdmin() && $search !== '', function (Builder $query) use ($search) {
                $term = '%'.$search.'%';
                $query->whereHas('user', function (Builder $userQuery) use ($term) {
                    $userQuery->where('name', 'like', $term)
                        ->orWhere('username', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return view('activities.index', compact('activities'));
    }
}
