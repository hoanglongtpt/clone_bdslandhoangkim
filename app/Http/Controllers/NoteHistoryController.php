<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class NoteHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $ownNotes = Note::query()
            ->select(Note::COLUMNS)
            ->with(['property:id,code,project_id'])
            ->whereHas('property', fn (Builder $query) => $query->visibleTo($user))
            ->where(function (Builder $query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere(function (Builder $legacy) use ($user) {
                        $legacy->whereNull('user_id')->where('author', $user->name);
                    });
            })
            ->orderByDesc('note_date');

        $notes = (clone $ownNotes)
            ->when($request->filled('q'), function (Builder $query) use ($request) {
                $term = '%'.$request->string('q')->trim().'%';
                $query->where(function (Builder $search) use ($term) {
                    $search->where('note', 'like', $term)
                        ->orWhereHas('property', fn (Builder $property) => $property->where('code', 'like', $term));
                });
            })
            ->paginate(30)
            ->withQueryString();

        $totalNotes = (clone $ownNotes)->reorder()->count();

        return view('notes.history', compact('notes', 'totalNotes'));
    }
}
