<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    public const COLUMNS = ['customers.id', 'full_name', 'contact_type', 'phone1', 'phone2', 'email', 'status', 'potential_level'];

    public $timestamps = false;

    protected $guarded = [];

    protected $hidden = ['raw_json'];

    public function properties()
    {
        return $this->belongsToMany(Property::class, 'property_customers');
    }
}
