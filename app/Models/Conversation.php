<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'name',
        'description',
        'created_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the users that belong to the conversation.
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_user')
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    /**
     * Get all messages for the conversation.
     *
     * @return HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)
            ->orderBy('created_at', 'asc');
    }

    /**
     * Get the latest message for the conversation.
     *
     * @return HasOne
     */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Get the user who created the conversation.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if conversation is a group conversation.
     *
     * @return bool
     */
    public function isGroup(): bool
    {
        return $this->type === 'group';
    }

    /**
     * Check if conversation is a private conversation.
     *
     * @return bool
     */
    public function isPrivate(): bool
    {
        return $this->type === 'private';
    }

    /**
     * Get the other user in a private conversation.
     *
     * @param int $userId
     * @return User|null
     */
    public function getOtherUser(int $userId): ?User
    {
        if ($this->isGroup()) {
            return null;
        }

        return $this->users()->where('users.id', '!=', $userId)->first();
    }

    /**
     * Get the display name for the conversation.
     *
     * @param int $currentUserId
     * @return string
     */
    public function getDisplayName(int $currentUserId): string
    {
        if ($this->isGroup()) {
            return $this->name ?? 'Groupe sans nom';
        }

        $otherUser = $this->getOtherUser($currentUserId);
        return $otherUser ? $otherUser->name : 'Utilisateur inconnu';
    }

    /**
     * Get unread messages count for a specific user.
     *
     * @param int $userId
     * @return int
     */
    public function getUnreadCount(int $userId): int
    {
        $lastReadAt = $this->users()
            ->where('users.id', $userId)
            ->first()
            ?->pivot
            ?->last_read_at;

        if (!$lastReadAt) {
            return $this->messages()->where('user_id', '!=', $userId)->count();
        }

        return $this->messages()
            ->where('user_id', '!=', $userId)
            ->where('created_at', '>', $lastReadAt)
            ->count();
    }

    /**
     * Mark conversation as read for a user.
     *
     * @param int $userId
     * @return void
     */
    public function markAsRead(int $userId): void
    {
        $this->users()->updateExistingPivot($userId, [
            'last_read_at' => now(),
        ]);
    }
}
