<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Win;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::query()->firstOrCreate([
            'email' => 'chase@crumbls.com',
        ], [
            'name' => 'Chase Miller',
            'email_verified_at' => now(),
            'password' => Str::random(100),
            'timezone' => 'America/Denver',
        ]);

        $today = now('UTC')->startOfDay();
        $startAt = $today->copy()->subDays(299);
        $endAt = $today->copy()->addDay();
        $minPerDay = 3;
        $winsByDate = $user->wins()
            ->select(['id', 'user_id', 'created_at'])
            ->whereBetween('created_at', [$startAt, $endAt])
            ->get()
            ->groupBy(fn (Win $win): string => $win->created_at->toDateString());
        $winTemplates = [
            'Moved an important project forward.',
            'Made time for a focused block of work.',
            'Solved a problem that had been lingering.',
            'Had a productive conversation with a teammate.',
            'Took a useful idea from draft to done.',
            'Protected time for the work that matters.',
            'Learned something that made the next step clearer.',
            'Finished a task that had been on the list too long.',
            'Made a thoughtful decision with good information.',
            'Created space to think and reset.',
        ];
        $wins = collect(range(0, 299))
            ->flatMap(function (int $daysAgo) use ($minPerDay, $today, $user, $winTemplates, $winsByDate) {
                $localDate = $today->copy()->subDays($daysAgo);
                $dateKey = $localDate->toDateString();

                $total = rand(1, $minPerDay - $winsByDate->get($dateKey, collect())->count());

                $winsNeeded = max(0, $total);

                if ($winsNeeded === 0) {
                    return collect();
                }

                return collect(range(0, $winsNeeded - 1))
                    ->map(function (int $winIndex) use ($daysAgo, $localDate, $user, $winTemplates): array {
                        $recordedAt = $localDate->copy()
                            ->setTime(8 + (($daysAgo + ($winIndex * 3)) % 12), (($daysAgo * 7) + ($winIndex * 17)) % 60)
                            ->utc();

                        return [
                            'user_id' => $user->id,
                            'content' => $winTemplates[($daysAgo + $winIndex) % count($winTemplates)],
                            'created_at' => $recordedAt,
                            'updated_at' => $recordedAt,
                        ];
                    });
            })
            ->map(function (array $win): array {
                $win['content'] = Win::sanitizeContent($win['content']);

                return $win;
            })
            ->values()
            ->all();

        if ($wins !== []) {
            Win::query()->insert($wins);
        }
    }
}
