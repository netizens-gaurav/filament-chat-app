<?php

namespace App\Models;

use App\Enums\ConversationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Conversation extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'title',
        'created_by',
        'last_message_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        //
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ConversationType::class,
            'last_message_at' => 'datetime',
        ];
    }

    /**
     * Users participating in this conversation
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['last_read_at', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * All messages in this conversation
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    /**
     * Latest message (for conversation list preview)
     */
    public function latestMessage(): HasMany
    {
        return $this->hasMany(Message::class)->latest();
    }

    /**
     * User who created the conversation
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the other participant in a 1-on-1 conversation
     */
    public function getOtherParticipant(User $currentUser): ?User
    {
        return $this->users()
            ->where('user_id', '!=', $currentUser->id)
            ->first();
    }

    /**
     * Get conversation title (for 1-on-1, use other user's name)
     */
    public function getDisplayTitle(User $currentUser): string
    {
        if ($this->type === ConversationType::GROUP && $this->title) {
            return $this->title;
        }

        $otherUser = $this->getOtherParticipant($currentUser);
        return $otherUser ? $otherUser->name : 'Unknown User';
    }

    /**
     * Check if user is a participant
     */
    public function hasParticipant(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Find or create a 1-on-1 conversation between two users
     */
    public static function findOrCreatePrivate(User $user1, User $user2): self
    {
        // Find existing private conversation between these two users
        $conversation = self::where('type', 'private')
            ->whereHas('users', function ($query) use ($user1) {
                $query->where('user_id', $user1->id);
            })
            ->whereHas('users', function ($query) use ($user2) {
                $query->where('user_id', $user2->id);
            })
            ->first();

        if ($conversation) {
            return $conversation;
        }

        // Create new conversation
        $conversation = self::create([
            'type' => 'private',
            'created_by' => $user1->id,
        ]);

        $conversation->users()->attach([$user1->id, $user2->id], [
            'joined_at' => now(),
        ]);

        return $conversation;
    }
}
