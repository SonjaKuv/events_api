<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'login',
        'email',
        'password',
        'avatar',
        'telegram_id',
        'vk_id',
        'instagram_id',
        'friends',
        'is_deleted',
    ];

    public $timestamps = true;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
        'friends' => 'array',
        'is_deleted' => 'boolean',
    ];

    /**
     * События, созданные пользователем
     * 
     * @return HasMany
     */
    public function createdEvents(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * События, в которых участвует пользователь
     * 
     * @return BelongsToMany
     */
    public function participatingEvents(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_participants')
            ->withPivot('status')
            ->withTimestamps();
    }

    /**
     * Получить все события пользователя (созданные + участвующие)
     * 
     * @return Builder
     */
    public function getAllEvents(): Builder
    {
        return Event::where(function ($query) {
            $query->where('user_id', $this->id)
                ->orWhereHas('participants', function ($q) {
                    $q->where('user_id', $this->id);
                });
        });
    }
}
