<?php

use App\Http\Controllers\StoreController;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('genre');
            $table->string('icon', 32);
            $table->string('image');
            $table->string('tag');
            $table->string('color', 32);
            $table->string('currency');
            $table->string('id_label');
            $table->boolean('region')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('game_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('amount');
            $table->unsignedInteger('price');
            $table->unsignedInteger('stock')->default(100);
            $table->timestamps();
        });

        foreach (StoreController::defaultGames() as $slug => $game) {
            $gameId = DB::table('games')->insertGetId([
                'slug' => $slug,
                'name' => $game['name'],
                'genre' => $game['genre'],
                'icon' => $game['icon'],
                'image' => $game['image'],
                'tag' => $game['tag'],
                'color' => $game['color'],
                'currency' => $game['currency'],
                'id_label' => $game['id_label'],
                'region' => $game['region'],
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($game['packages'] as $package) {
                DB::table('game_packages')->insert([
                    'game_id' => $gameId,
                    'name' => $package['name'],
                    'amount' => $package['amount'],
                    'price' => $package['price'],
                    'stock' => 100,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('game_packages');
        Schema::dropIfExists('games');
    }
};
