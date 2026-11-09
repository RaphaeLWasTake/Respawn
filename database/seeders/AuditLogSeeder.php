<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class AuditLogSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::where('role', 'admin')->value('id');
        $samples = [
            [
                'action' => 'demo.order.created',
                'auditable_type' => 'Demo\\Order',
                'auditable_id' => 10001,
                'changes' => ['status' => 'Menunggu pembayaran', 'amount' => 79000],
                'minutes_ago' => 12,
            ],
            [
                'action' => 'demo.order.status_updated',
                'auditable_type' => 'Demo\\Order',
                'auditable_id' => 10002,
                'changes' => ['from' => 'Diproses', 'to' => 'Lunas'],
                'minutes_ago' => 7,
            ],
            [
                'action' => 'demo.game.updated',
                'auditable_type' => 'Demo\\Game',
                'auditable_id' => 20001,
                'changes' => ['field' => 'active', 'from' => false, 'to' => true],
                'minutes_ago' => 2,
            ],
        ];

        foreach ($samples as $sample) {
            AuditLog::firstOrCreate(
                [
                    'action' => $sample['action'],
                    'auditable_type' => $sample['auditable_type'],
                    'auditable_id' => $sample['auditable_id'],
                ],
                [
                    'actor_user_id' => $adminId,
                    'changes' => $sample['changes'],
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Respawn audit demo seeder',
                    'created_at' => now()->subMinutes($sample['minutes_ago']),
                ]
            );
        }

        $demoCount = AuditLog::whereIn('action', array_column($samples, 'action'))->count();
        $this->command?->info($demoCount.' audit demo records are ready.');
    }
}
