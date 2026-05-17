<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends BaseModel
{
    protected $fillable = [
        'uuid',
        'user_id',
        'phone',
        'name',
        'email',
        'type',
        'title',
        'company',
        'avatar_url',
        'metadata',
        'attributes',
        'is_active',
        'last_seen_at',
    ];

    protected $casts = [
        'metadata' => 'json',
        'attributes' => 'json',
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ContactNote::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(ContactTag::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(ContactRule::class);
    }

    public function customFields(): HasMany
    {
        return $this->hasMany(ContactCustomField::class);
    }

    public function memories(): HasMany
    {
        return $this->hasMany(Memory::class);
    }
}
