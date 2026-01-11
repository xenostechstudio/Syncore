<?php

namespace App\Livewire\Components;

use App\Models\SystemNotification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class NotificationDropdown extends Component
{
    protected static ?bool $tableExistsCache = null;

    /**
     * Placeholder shown while component is loading
     */
    public function placeholder(): string
    {
        return <<<'HTML'
        <button class="relative rounded-lg p-2 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
        </button>
        HTML;
    }

    public function markAsRead(int $notificationId): void
    {
        if (!$this->tableExists()) {
            return;
        }

        $notification = SystemNotification::where('user_id', auth()->id())
            ->find($notificationId);

        if ($notification) {
            $notification->markAsRead();
        }
    }

    public function markAllAsRead(): void
    {
        if (!$this->tableExists()) {
            return;
        }

        NotificationService::markAllAsRead(auth()->id());
    }

    #[Computed(cache: true)]
    public function unreadCount(): int
    {
        if (!$this->tableExists()) {
            return 0;
        }

        return Cache::remember(
            'notifications_unread_' . auth()->id(),
            60, // Cache for 1 minute
            fn () => NotificationService::getUnreadCount(auth()->id())
        );
    }

    #[Computed]
    public function notifications()
    {
        if (!$this->tableExists()) {
            return collect();
        }

        return SystemNotification::query()
            ->select(['id', 'title', 'message', 'type', 'read_at', 'created_at'])
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }

    protected function tableExists(): bool
    {
        if (static::$tableExistsCache === null) {
            static::$tableExistsCache = Schema::hasTable('system_notifications');
        }
        return static::$tableExistsCache;
    }

    public function render()
    {
        return view('livewire.components.notification-dropdown');
    }
}
