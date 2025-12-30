<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'conversation_id',
        'user_id',
        'body',
        'type',
        'attachment_path',
        'read_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $casts = [
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the conversation that owns the message.
     *
     * @return BelongsTo
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the user that sent the message.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the message has been read.
     *
     * @return bool
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Mark the message as read.
     *
     * @return void
     */
    public function markAsRead(): void
    {
        if (!$this->isRead()) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Check if the message is from the given user.
     *
     * @param int $userId
     * @return bool
     */
    public function isSentBy(int $userId): bool
    {
        return $this->user_id === $userId;
    }

    /**
     * Check if the message has an attachment.
     *
     * @return bool
     */
    public function hasAttachment(): bool
    {
        return $this->attachment_path !== null;
    }

    /**
     * Get the attachment URL.
     *
     * @return string|null
     */
    public function getAttachmentUrlAttribute(): ?string
    {
        if (!$this->hasAttachment()) {
            return null;
        }

        return asset('storage/' . $this->attachment_path);
    }

    /**
     * Get formatted time for display.
     *
     * @return string
     */
    public function getFormattedTimeAttribute(): string
    {
        return $this->created_at->format('H:i');
    }

    /**
     * Get formatted date for display.
     *
     * @return string
     */
    public function getFormattedDateAttribute(): string
    {
        if ($this->created_at->isToday()) {
            return 'Aujourd\'hui';
        }

        if ($this->created_at->isYesterday()) {
            return 'Hier';
        }

        return $this->created_at->format('d/m/Y');
    }
}
