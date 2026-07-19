<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Note;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NoteController extends Controller
{
    public function store(Request $request, Property $property)
    {
        abort_unless($request->user()->canEditProperties(), 403);
        abort_unless(Property::query()->visibleTo($request->user())->whereKey($property->id)->exists(), 403);
        $data = $request->validate(['note_group' => ['required', 'in:1,2'], 'note' => ['required', 'string', 'max:10000']]);
        $note = DB::transaction(function () use ($data, $property, $request) {
            $nextId = ((int) Note::query()->lockForUpdate()->max('id')) + 1;

            return Note::create(['id' => $nextId, 'property_id' => $property->id, 'note_group' => $data['note_group'],
                'note' => $data['note'], 'note_date' => now(), 'author' => $request->user()->name]);
        });
        ActivityLog::record('note.created', $note, "Thêm ghi chú cho căn {$property->code}");

        return back()->with('success', 'Đã thêm ghi chú.');
    }
}
