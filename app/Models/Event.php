<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'start_datetime',
        'is_long',
        'end_date',
        'location_name',
        'location_coords',
        'weather',
        'image',
        'description',
        'link',
        'is_public',
        'whitelist',
        'tags'
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_date' => 'date',
        'location_coords' => 'array',
        'weather' => 'array',
        'whitelist' => 'array',
        'tags' => 'array',
        'is_long' => 'boolean',
        'is_public' => 'boolean',
    ];

    /**
     * Связь с таблицей пользователей (создатель события)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Связь с комментариями события
     */
    public function comments()
    {
        return $this->hasMany(EventComment::class);
    }

    /**
     * Связь с участниками события
     */
    public function participants()
    {
        return $this->belongsToMany(User::class, 'event_participants')
            ->withPivot('status')
            ->withTimestamps();
    }

    /**
     * Получить публичные события
     */
    public static function publicEvents()
    {
        return self::where('is_public', true)->get();
    }

    /**
     * Получить долгие события
     */
    public static function longEvents()
    {
        return self::where('is_long', true)->get();
    }
}
