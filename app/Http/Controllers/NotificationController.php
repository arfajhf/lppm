<?php

namespace App\Http\Controllers;

use App\Models\ActivityNotification;
use App\Support\LppmWorkspace;

/**
 * Menyajikan notifikasi aktivitas LPPM berdasarkan cakupan akses pengguna.
 */
class NotificationController extends Controller
{
    /**
     * Menampilkan daftar notifikasi dan rekap kanal notifikasi yang tersedia.
     */
    public function index()
    {
        $user = auth()->user();
        $module = LppmWorkspace::module($user, 'notifications');

        $notificationQuery = ActivityNotification::with('user')
            ->when(
                ! $user->hasLppmPermission('lppm.access.manage'),
                fn ($query) => $query->where('user_id', $user->user_id)
            );

        $channelSummary = (clone $notificationQuery)
            ->selectRaw('channel, count(*) as total')
            ->groupBy('channel')
            ->pluck('total', 'channel');

        $notifications = $notificationQuery->latest()->paginate(12);

        return view('notifications.index', compact('notifications', 'module', 'channelSummary'));
    }
}
