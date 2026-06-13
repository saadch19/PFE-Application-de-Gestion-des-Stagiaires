<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Support\NotificationService;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'subject',
        'body',
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
        static::created(function (Message $message): void {
            $message->sendEmailCopy();
            // Also create an in-app notification for the receiver
            NotificationService::messageReceived($message);
        });
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    private function sendEmailCopy(): void
    {
        $this->loadMissing(['sender', 'receiver']);

        if (blank($this->receiver?->email)) {
            return;
        }

        try {
            Mail::html($this->emailBody(), function ($mail): void {
                $mail->to($this->receiver->email, $this->receiver->full_name)
                    ->subject($this->subject);
            });
        } catch (\Throwable $exception) {
            Log::warning('Impossible d envoyer la notification email.', [
                'message_id' => $this->id,
                'receiver_id' => $this->receiver_id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function emailBody(): string
    {
        $senderName = $this->sender?->full_name ?? config('app.name');
        $messageUrl = route('messages.show', $this);

        return view('emails.notification', [
            'appName' => config('app.name'),
            'receiverName' => $this->receiver->full_name,
            'senderName' => $senderName,
            'subjectText' => $this->subject,
            'bodyText' => $this->body,
            'messageUrl' => $messageUrl,
        ])->render();
    }
}
