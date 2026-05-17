<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends BaseModel
{
    public const TYPE_CONTACT = 'contact';
    public const TYPE_CLIENT = 'client';
    public const TYPE_FAMILY = 'family';
    public const TYPE_FRIEND = 'friend';
    public const TYPE_FIANCEE = 'fiancée';
    public const TYPE_PARTNER = 'partner';
    public const TYPE_PROSPECT = 'prospect';
    public const TYPE_VENDOR = 'vendor';

    public const TYPES = [
        self::TYPE_CONTACT,
        self::TYPE_CLIENT,
        self::TYPE_FAMILY,
        self::TYPE_FRIEND,
        self::TYPE_FIANCEE,
        self::TYPE_PARTNER,
        self::TYPE_PROSPECT,
        self::TYPE_VENDOR,
    ];

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

    public function scopeOfType($query, string $type)
    {
        if (!in_array($type, self::TYPES, true)) {
            return $query;
        }

        return $query->where('type', $type);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function ($subQuery) use ($term) {
            $subQuery->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('company', 'like', "%{$term}%")
                ->orWhere('title', 'like', "%{$term}%");
        });
    }

    public function getTypeLabelAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->type ?? self::TYPE_CONTACT));
    }

    public static function getAvailableTypes(): array
    {
        return self::TYPES;
    }
}
