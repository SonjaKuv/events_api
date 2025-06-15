<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель для промежуточной таблицы event_participants
 * Связывает события и пользователей
 */
class EventParticipants extends Model
{
    use HasFactory;

    protected $table = 'event_participants';

    protected $fillable = [
        'event_id',
        'user_id',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Связь с событием
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Связь с пользователем
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Константы для статусов участия
     */
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_DECLINED = 'declined';

    /**
     * Получить все возможные статусы
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_ACCEPTED,
            self::STATUS_DECLINED,
        ];
    }

    /**
     * Проверить, принял ли участник приглашение
     */
    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    /**
     * Проверить, отклонил ли участник приглашение
     */
    public function isDeclined(): bool
    {
        return $this->status === self::STATUS_DECLINED;
    }

    /**
     * Проверить, ожидает ли приглашение ответа
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
