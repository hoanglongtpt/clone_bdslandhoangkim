<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    public const COLUMNS = ['notes.id', 'notes.property_id', 'notes.note_group', 'notes.note', 'notes.note_date', 'notes.author', 'notes.user_id'];

    public $timestamps = false;

    protected $fillable = ['id', 'property_id', 'note_group', 'note', 'note_date', 'author', 'user_id'];

    protected $casts = ['note_date' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
