<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'icon',
        'color',
        'title',
        'body',
        'url',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Notification $notification): void {
            $notification->sendEmail();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Convenience factory — create a notification for a user.
     */
    public static function notify(User|int $recipient, array $data): self
    {
        $userId = $recipient instanceof User ? $recipient->id : $recipient;

        return static::create(array_merge(['user_id' => $userId], $data));
    }

    // ── Email delivery ──────────────────────────────────────────────────────

    private function sendEmail(): void
    {
        $this->loadMissing('user');

        if (blank($this->user?->email)) {
            return;
        }

        try {
            $appUrl  = config('app.url', url('/'));
            $linkUrl = $this->url ? (str_starts_with($this->url, 'http') ? $this->url : $appUrl . $this->url) : $appUrl;

            $html = view('emails.notification', [
                'appName'      => config('app.name'),
                'receiverName' => $this->user->full_name,
                'senderName'   => config('app.name'),
                'subjectText'  => $this->title,
                'bodyText'     => $this->body,
                'messageUrl'   => $linkUrl,
            ])->render();

            Mail::html($html, function ($mail): void {
                $mail->to($this->user->email, $this->user->full_name)
                     ->subject($this->title);
            });
        } catch (\Throwable $e) {
            Log::warning('Notification email failed.', [
                'notification_id' => $this->id,
                'user_id'         => $this->user_id,
                'error'           => $e->getMessage(),
            ]);
        }
    }
}
