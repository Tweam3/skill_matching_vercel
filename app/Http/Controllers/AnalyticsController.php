<?php

namespace App\Http\Controllers;

use App\Services\Analytics\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AnalyticsController extends Controller
{
    public function index(AnalyticsService $analytics, Request $request)
    {
        $year = $request->integer('year', now()->year);
        $metrics = Cache::remember('analytics.dashboard.' . $year, 60, fn () => $analytics->allMetrics($year));
        $availableYears = $analytics->availableYears();

        return view('admin.analytics', compact('metrics', 'year', 'availableYears'));
    }

    public function report(AnalyticsService $analytics, Request $request)
    {
        $year = $request->integer('year', now()->year);
        $metrics = $analytics->allMetrics($year);

        $csv = [];
        $csv[] = ['Section', 'Metric', 'Value'];

        $csv[] = ['Summary', 'Total Service Requests', $metrics['total_requests']];
        $csv[] = ['Summary', 'Total Active Providers', $metrics['total_providers']];
        $csv[] = ['Summary', 'Total Matches', $metrics['total_matches']];
        $csv[] = ['Summary', 'Transaction Completion Rate (%)', number_format((float) $metrics['transaction_completion_rate'], 2)];
        $csv[] = ['Summary', 'Active Providers (30d)', $metrics['active_provider_count']];

        $csv[] = ['', '', ''];
        $csv[] = ['Monthly Request Volume', 'Month', 'Requests'];
        foreach ($metrics['monthly_request_volume']['labels'] as $i => $month) {
            $csv[] = ['', $month, $metrics['monthly_request_volume']['data'][$i] ?? 0];
        }

        $csv[] = ['', '', ''];
        $csv[] = ['Monthly Match Volume', 'Month', 'Matches'];
        foreach ($metrics['monthly_match_volume']['labels'] as $i => $month) {
            $csv[] = ['', $month, $metrics['monthly_match_volume']['data'][$i] ?? 0];
        }

        $csv[] = ['', '', ''];
        $csv[] = ['Top-Rated Providers', 'Provider', 'Rating / Completed / Verified'];
        foreach ($metrics['top_rated_providers'] as $p) {
            $csv[] = ['', $p->Full_Name, number_format((float) $p->Avg_Rating, 2) . ' / ' . $p->Total_Completed . ' / ' . ($p->Is_Verified ? 'Yes' : 'No')];
        }

        $csv[] = ['', '', ''];
        $csv[] = ['Most Active Providers', 'Provider', 'Completed / Rating'];
        foreach ($metrics['most_active_providers'] as $p) {
            $csv[] = ['', $p->Full_Name, $p->Total_Completed . ' / ' . number_format((float) $p->Avg_Rating, 2)];
        }

        $csv[] = ['', '', ''];
        $csv[] = ['Most Requested Categories', 'Category', 'Requests'];
        foreach ($metrics['most_requested_categories'] as $cat) {
            $csv[] = ['', $cat->Category, $cat->request_count];
        }

        $csv[] = ['', '', ''];
        $csv[] = ['Completion Rate by Category', 'Category', 'Rate (%)'];
        foreach ($metrics['completion_rate_by_category']['labels'] as $i => $cat) {
            $csv[] = ['', $cat, $metrics['completion_rate_by_category']['data'][$i] ?? 0];
        }

        $csv[] = ['', '', ''];
        $csv[] = ['Average Rating Trend', 'Month', 'Avg Rating'];
        foreach ($metrics['average_rating_trend']['labels'] as $i => $month) {
            $csv[] = ['', $month, $metrics['average_rating_trend']['data'][$i] ?? 'N/A'];
        }

        $handle = fopen('php://temp', 'r+');
        foreach ($csv as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        $filename = 'campusskill-analytics-report-' . $year . '-' . now()->format('Y-m-d') . '.csv';

        return response($content, 200)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
