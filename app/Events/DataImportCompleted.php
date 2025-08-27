<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DataImportCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public string $importType;
    public array $results;
    public bool $hasErrors;
    public string $fileName;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, string $importType, array $results, string $fileName, bool $hasErrors = false)
    {
        $this->user = $user;
        $this->importType = $importType;
        $this->results = $results;
        $this->fileName = $fileName;
        $this->hasErrors = $hasErrors;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->user->id),
            new PrivateChannel('data-imports'),
            new PrivateChannel('dashboard'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'import.completed';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'import_type' => $this->importType,
            'file_name' => $this->fileName,
            'results' => array_merge([
                'total_rows' => 0,
                'processed_rows' => 0,
                'successful_rows' => 0,
                'failed_rows' => 0,
                'created_contacts' => 0,
                'updated_contacts' => 0,
                'duplicate_contacts' => 0,
                'errors' => [],
            ], $this->results),
            'has_errors' => $this->hasErrors,
            'completed_at' => now()->toISOString(),
        ];
    }
}
