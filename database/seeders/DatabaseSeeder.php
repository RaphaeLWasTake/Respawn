<?php

namespace Database\Seeders;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(['email' => 'admin@respawn.id'], [
            'name' => 'Respawn Admin',
            'username' => 'admin',
            'password' => Hash::make('Admin123!'),
            'role' => 'admin',
        ]);
        User::firstOrCreate(['email' => 'helper@respawn.id'], [
            'name' => 'Nadia CS',
            'username' => 'helper',
            'password' => Hash::make('Helper123!'),
            'role' => 'helper',
        ]);

        $demoUsers = collect([
            ['name' => 'Raka Pratama', 'username' => 'raka.gg', 'email' => 'raka@example.test'],
            ['name' => 'Naya Putri', 'username' => 'nayaplay', 'email' => 'naya@example.test'],
            ['name' => 'Dimas Arya', 'username' => 'dimaspro', 'email' => 'dimas@example.test'],
            ['name' => 'Salsa N.', 'username' => 'salsan', 'email' => 'salsa@example.test'],
        ])->map(fn (array $data) => User::firstOrCreate(['email' => $data['email']], [
            ...$data,
            'password' => Hash::make('User123!'),
            'role' => 'user',
        ]));

        $orders = [
            ['Genshin Impact', 'Blessing of the Welkin Moon', '812345678', 79000, 'QRIS', 'Lunas'],
            ['Free Fire', '720 Diamond', '1234567890', 100000, 'GoPay', 'Menunggu pembayaran'],
            ['Mobile Legends: Bang Bang', 'Weekly Diamond Pass', '11223344 (8765)', 30000, 'DANA', 'Lunas'],
            ['PUBG Mobile', '660 UC', '512334455', 159000, 'Transfer Bank', 'Diproses'],
            ['Honkai: Star Rail', 'Express Supply Pass', '801234567', 79000, 'QRIS', 'Menunggu pembayaran'],
        ];
        foreach ($orders as $index => [$game, $package, $playerId, $amount, $method, $status]) {
            DB::table('orders')->updateOrInsert(
                ['game' => $game, 'player_id' => $playerId],
                ['user_id' => $demoUsers[$index % $demoUsers->count()]->id, 'package' => $package, 'amount' => $amount, 'payment_method' => $method, 'status' => $status, 'created_at' => now()->subMinutes(($index + 1) * 11), 'updated_at' => now()]
            );
        }

        $ticketSamples = [
            ['Pembayaran belum terkonfirmasi', 'Aku sudah bayar pakai QRIS sekitar 10 menit lalu, tapi diamond belum masuk. Bisa dibantu cek?'],
            ['Salah memasukkan User ID', 'Aku baru sadar UID yang dimasukkan kurang satu angka. Pesanan #1042 bisa dibatalkan?'],
            ['Tanya promo Weekly Pass', 'Kalau beli Weekly Diamond Pass sekarang, bonusnya langsung masuk atau bertahap?'],
            ['Top-up sedang diproses', 'Status pesananku masih diproses dari tadi. Mohon dibantu ya, kak.'],
        ];
        foreach ($ticketSamples as $index => [$subject, $message]) {
            SupportTicket::firstOrCreate(
                ['subject' => $subject],
                ['user_id' => $demoUsers[$index]->id, 'message' => $message, 'status' => $index === 2 ? 'Sudah dibalas' : 'Menunggu balasan', 'reply' => $index === 2 ? 'Bonus pass akan diklaim bertahap selama 7 hari, ya. Selamat bermain!' : null]
            );
        }

        for ($index = 0; $index < 124; $index++) {
            DB::table('visitor_logs')->updateOrInsert(
                ['visitor_key' => hash('sha256', 'respawn-demo-visitor-'.$index), 'visited_on' => today()->toDateString()],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
