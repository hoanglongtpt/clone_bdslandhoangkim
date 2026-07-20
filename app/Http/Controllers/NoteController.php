<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Note;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NoteController extends Controller
{
    public function index(Request $request, Property $property, string $group)
    {
        abort_unless(in_array($group, ['1', '2'], true), 404);
        abort_unless(Property::query()->visibleTo($request->user())->whereKey($property->id)->exists(), 403);

        $notes = $property->notes()
            ->where('note_group', $group)
            ->orderByDesc('note_date')
            ->get()
            ->map(fn (Note $note) => [
                'id' => $note->id,
                'note' => $note->note,
                'author' => $note->author ?: 'Không rõ',
                'note_date' => $note->note_date?->format('d/m/Y H:i'),
            ]);

        return response()->json([
            'property' => ['id' => $property->id, 'code' => $property->code],
            'group' => $group,
            'title' => $group === '1' ? 'Ghi chú bán' : 'Ghi chú thuê',
            'notes' => $notes,
        ]);
    }

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
