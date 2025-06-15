<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 
        'user_id', 
        'content'
    ];

    protected $casts = [
        'event_id' => 'integer',
        'user_id' => 'integer',
        'content' => 'string',
    ];

    /**
     * Связь с событием
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Связь с пользователем (автором комментария)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Проверить, является ли пользователь автором комментария
     */
    public function isAuthor(int $userId): bool
    {
        return $this->user_id === $userId;
    }

    /**
     * Получить краткое содержание комментария
     */
    public function getExcerpt(int $length = 100): string
    {
        return strlen($this->content) > $length 
            ? substr($this->content, 0, $length) . '...' 
            : $this->content;
    }
}
