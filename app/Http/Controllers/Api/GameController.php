<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GamePackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class GameController extends Controller
{
    private const GAME_FIELDS = [
        'name' => ['required', 'string', 'max:150'],
        'slug' => ['required', 'alpha_dash', 'max:150'],
        'genre' => ['required', 'string', 'max:100'],
        'icon' => ['required', 'string', 'max:32'],
        'image' => ['required', 'string', 'max:255'],
        'tag' => ['required', 'string', 'max:100'],
        'color' => ['required', 'string', 'max:32'],
        'currency' => ['required', 'string', 'max:100'],
        'id_label' => ['required', 'string', 'max:100'],
        'region' => ['required', 'boolean'],
        'active' => ['sometimes', 'boolean'],
    ];

    public function index(): JsonResponse
    {
        return response()->json(Game::with('packages')->orderBy('name')->paginate(20));
    }

    public function publicIndex(): JsonResponse
    {
        $data = request()->validate([
            'search' => ['sometimes', 'string', 'max:100'],
            'genre' => ['sometimes', 'string', 'max:100'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        $games = Game::with('packages')
            ->where('active', true)
            ->when($data['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%')
                        ->orWhere('tag', 'like', '%'.$search.'%');
                });
            })
            ->when($data['genre'] ?? null, fn ($query, $genre) => $query->where('genre', $genre))
            ->orderBy('name')
            ->paginate($data['per_page'] ?? 20)
            ->withQueryString();

        return response()->json($games);
    }

    public function publicShow(string $slug): JsonResponse
    {
        $game = Game::with('packages')->where('slug', $slug)->where('active', true)->firstOrFail();

        return response()->json(['data' => $game]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->gameRules());
        $packages = $data['packages'] ?? [];
        unset($data['packages']);

        $game = DB::transaction(function () use ($data, $packages) {
            $game = Game::create($data);
            $game->packages()->createMany($packages);

            return $game->load('packages');
        });

        return response()->json(['data' => $game], 201);
    }

    public function update(Request $request, Game $game): JsonResponse
    {
        if (! $request->hasAny(['name', 'slug', 'genre', 'icon', 'image', 'tag', 'color', 'currency', 'id_label', 'region', 'active'])) {
            throw ValidationException::withMessages([
                'game' => ['Minimal satu field game harus dikirim.'],
            ]);
        }

        $rules = $this->gameRules($game);
        $rules['packages'] = ['prohibited'];
        unset($rules['packages.*.name'], $rules['packages.*.amount'], $rules['packages.*.price'], $rules['packages.*.stock']);
        foreach ($rules as $field => $fieldRules) {
            $rules[$field] = array_merge(['sometimes'], $fieldRules);
            if ($field === 'slug') {
                $rules[$field] = ['sometimes', 'required', 'alpha_dash', 'max:150', Rule::unique('games', 'slug')->ignore($game->id)];
            }
        }
        $data = $request->validate($rules);
        $game->update($data);

        return response()->json(['data' => $game->refresh()->load('packages')]);
    }

    public function destroy(Game $game): JsonResponse
    {
        $game->delete();

        return response()->json(['message' => 'Game berhasil dihapus.']);
    }

    public function storePackage(Request $request, Game $game): JsonResponse
    {
        $package = $game->packages()->create($request->validate($this->packageRules()));

        return response()->json(['data' => $package], 201);
    }

    public function updatePackage(Request $request, Game $game, GamePackage $package): JsonResponse
    {
        abort_unless($package->game_id === $game->id, 404);
        if (! $request->hasAny(['name', 'amount', 'price', 'stock'])) {
            throw ValidationException::withMessages([
                'package' => ['Minimal satu field opsi harus dikirim.'],
            ]);
        }

        $package->update($request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'amount' => ['sometimes', 'required', 'integer', 'min:1'],
            'price' => ['sometimes', 'required', 'integer', 'min:1'],
            'stock' => ['sometimes', 'required', 'integer', 'min:0'],
        ]));

        return response()->json(['data' => $package->refresh()]);
    }

    public function destroyPackage(Game $game, GamePackage $package): JsonResponse
    {
        abort_unless($package->game_id === $game->id, 404);
        $package->delete();

        return response()->json(['message' => 'Opsi game berhasil dihapus.']);
    }

    private function gameRules(?Game $game = null): array
    {
        $rules = self::GAME_FIELDS;
        $rules['slug'] = $game
            ? ['required', 'alpha_dash', 'max:150', Rule::unique('games', 'slug')->ignore($game->id)]
            : ['required', 'alpha_dash', 'max:150', 'unique:games,slug'];
        $rules['packages'] = ['sometimes', 'array'];
        $rules['packages.*.name'] = ['required', 'string', 'max:150'];
        $rules['packages.*.amount'] = ['required', 'integer', 'min:1'];
        $rules['packages.*.price'] = ['required', 'integer', 'min:1'];
        $rules['packages.*.stock'] = ['sometimes', 'integer', 'min:0'];

        return $rules;
    }

    private function packageRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'amount' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'integer', 'min:1'],
            'stock' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
