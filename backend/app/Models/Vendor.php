<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Scopes\TenantScope;

class Vendor extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'owner_id', 'active'];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'vendor_user')->withTimestamps();
    }

    public function school()
    {
        return $this->belongsTo(\App\Models\School::class, 'school_id');
    }
}
