<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Support\NotificationService;
use Illuminate\Console\Command;

class SendDeadlineReminders extends Command
{
    protected $signature   = 'notifications:deadline-reminders';
    protected $description = 'Send notifications for tasks due within the next 3 days.';

    public function handle(): int
    {
        $tasks = Task::query()
            ->with(['assignedTo'])
            ->where('status', '!=', 'termine')
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [now()->addDay()->startOfDay(), now()->addDays(3)->endOfDay()])
            ->get();

        $count = 0;

        foreach ($tasks as $task) {
            if ($task->assignedTo) {
                NotificationService::taskDeadlineApproaching($task);
                $count++;
            }
        }

        $this->info("Sent {$count} deadline reminder notification(s).");

        return Command::SUCCESS;
    }
}
