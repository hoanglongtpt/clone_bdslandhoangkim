<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    public const COLUMNS = [
        'id', 'project_id', 'code', 'tower', 'floor', 'room', 'property_type', 'interior',
        'area', 'price_sell', 'price_rent', 'sales_commission', 'status', 'status_new',
        'rent_expiry', 'updated_date', 'source_created_at',
    ];

    public $timestamps = false;

    protected $fillable = [
        'project_id', 'code', 'tower', 'floor', 'room', 'property_type', 'interior',
        'area', 'price_sell', 'price_rent', 'sales_commission', 'status', 'status_new',
        'rent_expiry', 'updated_date',
    ];

    protected $casts = [
        'area' => 'decimal:2', 'price_sell' => 'decimal:2', 'price_rent' => 'decimal:2',
        'sales_commission' => 'decimal:2', 'rent_expiry' => 'date', 'updated_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class)->select(Note::COLUMNS);
    }

    public function saleNotes()
    {
        return $this->hasMany(Note::class)->select(Note::COLUMNS)->where('note_group', '1')->orderByDesc('note_date');
    }

    public function rentNotes()
    {
        return $this->hasMany(Note::class)->select(Note::COLUMNS)->where('note_group', '2')->orderByDesc('note_date');
    }

    public function latestSaleNote()
    {
        return $this->hasOne(Note::class)->select(Note::COLUMNS)->where('note_group', '1')->ofMany('note_date', 'max');
    }

    public function latestRentNote()
    {
        return $this->hasOne(Note::class)->select(Note::COLUMNS)->where('note_group', '2')->ofMany('note_date', 'max');
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'property_customers', 'property_id', 'customer_id')->select(Customer::COLUMNS);
    }

    public function media()
    {
        return $this->hasMany(Media::class)->select(Media::COLUMNS);
    }

    public function firstImage()
    {
        return $this->hasOne(Media::class)->select(Media::COLUMNS)->where('media_type', 'image')->orderBy('source_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where(function (Builder $visible) use ($user) {
            $visible->whereHas('users', fn (Builder $q) => $q->where('users.id', $user->id))
                ->orWhereHas('project.users', fn (Builder $q) => $q->where('users.id', $user->id));
        });
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        return $query->select(self::COLUMNS)->where($field ?? $this->getRouteKeyName(), $value);
    }
}
