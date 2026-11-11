<?php

namespace App\Services\Analytics;

use App\Models\Assignment;
use App\Models\Review;
use App\Models\Skill;
use App\Models\SkillRequest;
use App\Models\User;
use App\Models\UserMatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Monthly request volume — count of requests created per month
     * over the last $months periods.
     */
    public function monthlyRequestVolume(int $months = 12, ?int $year = null): array
    {
        if ($year) {
            return $this->monthlyCountsByYear('skill_requests', 'Created_At', $year);
        }
        return $this->monthlyCounts('skill_requests', 'Created_At', $months);
    }

    /**
     * Monthly match volume — count of provider matches created per month
     * over the last $months periods.
     */
    public function monthlyMatchVolume(int $months = 12, ?int $year = null): array
    {
        if ($year) {
            return $this->monthlyCountsByYear('matches', 'Matched_At', $year);
        }
        return $this->monthlyCounts('matches', 'Matched_At', $months);
    }

    /**
     * Top-rated providers — users with the highest average rating who have
     * completed at least 3 transactions.
     */
    public function topRatedProviders(int $limit = 5): Collection
    {
        return User::query()
            ->where('Role', 'Student')
            ->where('Account_Status', 'Active')
            ->where('Total_Completed', '>=', 3)
            ->orderByDesc('Avg_Rating')
            ->orderByDesc('Total_Completed')
            ->limit($limit)
            ->with('skills')
            ->get();
    }

    /**
     * Transaction completion rate — percentage of resolved assignments
     * (Completed out of Completed + Failed) across the platform.
     */
    public function transactionCompletionRate(): float
    {
        $resolved = Assignment::whereIn('Status', ['Completed', 'Failed'])->count();
        $completed = Assignment::where('Status', 'Completed')->count();

        if ($resolved === 0) {
            return 0.0;
        }

        return round(($completed / $resolved) * 100, 1);
    }

    /**
     * Completion rate broken down by skill category.
     */
    public function completionRateByCategory(): array
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            $results = DB::select("
                SELECT s.\"Category\", COUNT(*) as total, SUM(CASE WHEN a.\"Status\" = 'Completed' THEN 1 ELSE 0 END) as completed
                FROM skills as s
                INNER JOIN skill_requests as r ON s.\"Skill_ID\" = r.\"Skill_ID\"
                INNER JOIN request_assignments as a ON r.\"Request_ID\" = a.\"Request_ID\"
                WHERE a.\"Status\" IN ('Completed', 'Failed')
                GROUP BY s.\"Category\"
            ");
        } else {
            $results = DB::table('skills as s')
                ->join('skill_requests as r', 's.Skill_ID', '=', 'r.Skill_ID')
                ->join('request_assignments as a', 'r.Request_ID', '=', 'a.Request_ID')
                ->selectRaw('s.Category, COUNT(*) as total, SUM(CASE WHEN a.Status = \'Completed\' THEN 1 ELSE 0 END) as completed')
                ->whereIn('a.Status', ['Completed', 'Failed'])
            ->groupBy('s.Category')
            ->get();
        }

        $labels = [];
        $data = [];

        $results = collect($results)->map(function ($row) {
            return [
                'category' => $row->category ?? $row->Category,
                'total' => (int) $row->total,
                'completed' => (int) $row->completed,
            ];
        })->sortByDesc(function ($row) {
            return $row['total'] > 0 ? (float) $row['completed'] / $row['total'] * 100 : 0;
        })->values();

        foreach ($results as $row) {
            $labels[] = $row['category'];
            $rate = $row['total'] > 0 ? (float) $row['completed'] / $row['total'] * 100 : 0;
            $data[] = round($rate, 1);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Active provider count — unique providers who applied to requests
     * in the last $days days.
     */
    public function activeProviderCount(int $days = 30): int
    {
        return (int) Assignment::where('Created_At', '>=', now()->subDays($days))
            ->where('Status', '!=', 'Rejected')
            ->pluck('User_ID')
            ->unique()
            ->count();
    }

    /**
     * Most active service providers — users with the highest number of
     * completed transactions.
     */
    public function mostActiveProviders(int $limit = 5): Collection
    {
        return User::query()
            ->where('Role', 'Student')
            ->where('Account_Status', 'Active')
            ->where('Total_Completed', '>', 0)
            ->orderByDesc('Total_Completed')
            ->orderByDesc('Avg_Rating')
            ->limit($limit)
            ->with('skills')
            ->get();
    }

    /**
     * Most requested skill categories — categories with the highest number
     * of service requests.
     */
    public function mostRequestedSkillCategories(int $limit = 5): Collection
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            $results = DB::table('skills as s')
                ->join('skill_requests as r', 's.Skill_ID', '=', 'r.Skill_ID')
                ->selectRaw('s."Category", COUNT(*) as request_count')
                ->groupByRaw('s."Category"')
                ->orderByDesc('request_count')
                ->limit($limit)
                ->get();
        } else {
            $results = DB::table('skills as s')
                ->join('skill_requests as r', 's.Skill_ID', '=', 'r.Skill_ID')
                ->selectRaw('s.Category, COUNT(*) as request_count')
                ->groupBy('s.Category')
                ->orderByDesc('request_count')
                ->limit($limit)
                ->get();
        }

        return $results;
    }

    /**
     * Average user rating trend — monthly average rating over the last
     * $months periods, computed from submitted reviews.
     */
    public function averageUserRatingTrend(int $months = 12, ?int $year = null): array
    {
        if ($year) {
            return $this->averageUserRatingTrendByYear($year);
        }
        $since = now()->subMonths($months)->startOfMonth();
        $driver = DB::connection()->getDriverName();
        $monthExpr = match ($driver) {
            'sqlite' => "strftime('%Y-%m', Created_At)",
            'pgsql' => "to_char(\"Created_At\", 'YYYY-MM')",
            default => "DATE_FORMAT(Created_At, '%Y-%m')",
        };

        $ratingColumn = $driver === 'pgsql' ? '"Rating"' : 'Rating';

        $rawData = DB::table('reviews')
            ->selectRaw("{$monthExpr} as month, AVG({$ratingColumn}) as avg_rating")
            ->where('Created_At', '>=', $since)
            ->groupBy('month')
            ->pluck('avg_rating', 'month')
            ->toArray();

        $labels = [];
        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $labels[] = now()->subMonths($i)->format('M Y');
            $data[] = isset($rawData[$key]) ? round((float) $rawData[$key], 2) : null;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Get all distinct years that have data across requests, matches, and reviews.
     */
    public function availableYears(): array
    {
        $driver = DB::connection()->getDriverName();
        $columns = [
            'skill_requests' => 'Created_At',
            'matches' => 'Matched_At',
            'reviews' => 'Created_At',
        ];

        $years = collect([]);

        foreach ($columns as $table => $column) {
            $yearExpr = match ($driver) {
                'sqlite' => "strftime('%Y', {$column})",
                'pgsql' => "to_char(\"{$column}\", 'YYYY')",
                default => "YEAR({$column})",
            };

            $tableYears = DB::table($table)
                ->selectRaw("{$yearExpr} as year")
                ->distinct()
                ->pluck('year')
                ->map(fn ($y) => (int) $y);
            $years = $years->merge($tableYears);
        }

        return $years->unique()->sortDesc()->values()->all();
    }

    /**
     * Aggregate all KPIs into a single payload for the dashboard view.
     */
    public function allMetrics(?int $year = null): array
    {
        $months = 12;
        $labels = $this->generateMonthLabels($months);

        return [
            'monthly_request_volume' => [
                'labels' => $labels,
                'data' => $this->monthlyRequestVolume($months, $year)['data'],
            ],
            'top_rated_providers' => $this->topRatedProviders(),
            'transaction_completion_rate' => $this->transactionCompletionRate(),
            'monthly_match_volume' => [
                'labels' => $labels,
                'data' => $this->monthlyMatchVolume($months, $year)['data'],
            ],
            'completion_rate_by_category' => $this->completionRateByCategory(),
            'active_provider_count' => $this->activeProviderCount(),
            'most_active_providers' => $this->mostActiveProviders(),
            'most_requested_categories' => $this->mostRequestedSkillCategories(),
            'average_rating_trend' => $this->averageUserRatingTrend($months, $year),
            'total_requests' => SkillRequest::count(),
            'total_providers' => User::where('Role', 'Student')->where('Account_Status', 'Active')->count(),
            'total_assignments' => Assignment::count(),
            'total_matches' => UserMatch::count(),
        ];
    }

    /**
     * Generate month labels like ["Sep 2025", "Oct 2025", ...] for the last $months.
     */
    protected function generateMonthLabels(int $months): array
    {
        return collect(range($months - 1, 0))
            ->map(fn ($i) => now()->subMonths($i)->format('M Y'))
            ->toArray();
    }

    /**
     * Query a table for counts grouped by month over the last $months periods.
     */
    protected function monthlyCounts(string $table, string $column, int $months): array
    {
        $since = now()->subMonths($months)->startOfMonth();
        $driver = DB::connection()->getDriverName();
        $monthExpr = match ($driver) {
            'sqlite' => "strftime('%Y-%m', {$column})",
            'pgsql' => "to_char(\"{$column}\", 'YYYY-MM')",
            default => "DATE_FORMAT({$column}, '%Y-%m')",
        };

        $rawData = DB::table($table)
            ->selectRaw("{$monthExpr} as month, COUNT(*) as count")
            ->where($column, '>=', $since)
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $data[] = (int) ($rawData[$key] ?? 0);
        }

        return ['labels' => $this->generateMonthLabels($months), 'data' => $data];
    }

    /**
     * Query a table for counts grouped by month for a specific year.
     * Returns all 12 months (Jan-Dec) for that year.
     */
    protected function monthlyCountsByYear(string $table, string $column, int $year): array
    {
        $driver = DB::connection()->getDriverName();
        $monthExpr = match ($driver) {
            'sqlite' => "strftime('%m', {$column})",
            'pgsql' => "to_char(\"{$column}\", 'MM')",
            default => "MONTH({$column})",
        };
        $yearExpr = match ($driver) {
            'sqlite' => "strftime('%Y', {$column})",
            'pgsql' => "to_char(\"{$column}\", 'YYYY')",
            default => "YEAR({$column})",
        };

        $rawData = DB::table($table)
            ->selectRaw("{$monthExpr} as month, COUNT(*) as count")
            ->whereRaw("{$yearExpr} = ?", [$year])
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $labels = [];
        $data = [];
        for ($m = 1; $m <= 12; $m++) {
            $key = str_pad((string) $m, 2, '0', STR_PAD_LEFT);
            $labels[] = now()->month($m)->format('M');
            $data[] = (int) ($rawData[$key] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Average user rating trend for a specific year — monthly average rating
     * computed from submitted reviews.
     */
    protected function averageUserRatingTrendByYear(int $year): array
    {
        $driver = DB::connection()->getDriverName();
        $monthExpr = match ($driver) {
            'sqlite' => "strftime('%m', Created_At)",
            'pgsql' => "to_char(\"Created_At\", 'MM')",
            default => "MONTH(Created_At)",
        };
        $yearExpr = match ($driver) {
            'sqlite' => "strftime('%Y', Created_At)",
            'pgsql' => "to_char(\"Created_At\", 'YYYY')",
            default => "YEAR(Created_At)",
        };
        $ratingColumn = $driver === 'pgsql' ? '"Rating"' : 'Rating';

        $rawData = DB::table('reviews')
            ->selectRaw("{$monthExpr} as month, AVG({$ratingColumn}) as avg_rating")
            ->whereRaw("{$yearExpr} = ?", [$year])
            ->groupBy('month')
            ->pluck('avg_rating', 'month')
            ->toArray();

        $labels = [];
        $data = [];
        for ($m = 1; $m <= 12; $m++) {
            $key = str_pad((string) $m, 2, '0', STR_PAD_LEFT);
            $labels[] = now()->month($m)->format('M');
            $data[] = isset($rawData[$key]) ? round((float) $rawData[$key], 2) : null;
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
