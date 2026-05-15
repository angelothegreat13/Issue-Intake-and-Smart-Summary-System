<?php

namespace Database\Seeders;

use App\Services\IssueService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class IssueSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(IssueService::class);

        $issues = [
            // 1. Critical bug
            [
                'title'       => 'Database connection pool exhausted under high load',
                'description' => 'The application throws "too many connections" errors during peak traffic hours. The connection pool limit of 100 is being hit consistently between 9 AM and 11 AM. This causes 500 errors for end users and prevents order processing. Logs show all connections are held open for 30+ seconds each.',
                'priority'    => 'critical',
                'category'    => 'bug',
                'status'      => 'open',
            ],
            // 2. High infrastructure — escalated (high + open)
            [
                'title'       => 'Redis cache cluster running at 95% memory capacity',
                'description' => 'The Redis cluster used for session and page caching is at 95% memory utilization. If it reaches 100% it will begin evicting keys, which will cause cache misses and increased database load. We need to either increase the memory allocation or implement more aggressive TTL policies.',
                'priority'    => 'high',
                'category'    => 'infrastructure',
                'status'      => 'open',
            ],
            // 3. Medium feature
            [
                'title'       => 'Add CSV export to the sales reporting dashboard',
                'description' => 'Account managers frequently request data exports from the sales reporting dashboard. Currently they must manually copy table data into spreadsheets. A CSV export button on each report would save roughly 2 hours per week per account manager. The export should respect the currently applied date and region filters.',
                'priority'    => 'medium',
                'category'    => 'feature',
                'status'      => 'open',
            ],
            // 4. Critical security — escalated (critical)
            [
                'title'       => 'SQL injection vulnerability in user search endpoint',
                'description' => 'A penetration test identified a SQL injection vulnerability in the /api/users/search endpoint. The "q" query parameter is being interpolated directly into a raw SQL query without sanitization. An attacker could extract the full users table or drop tables. The endpoint is publicly accessible without authentication.',
                'priority'    => 'critical',
                'category'    => 'security',
                'status'      => 'open',
            ],
            // 5. High performance with due_at in the past — escalated (high + open + overdue)
            [
                'title'       => 'API response times degraded to 3+ seconds on /api/orders',
                'description' => 'The /api/orders endpoint has seen a 10x increase in response time over the past week, going from ~300ms to 3+ seconds. This is impacting the mobile app experience significantly. Preliminary investigation suggests a missing index on the orders.customer_id column after last week\'s schema migration.',
                'priority'    => 'high',
                'category'    => 'performance',
                'status'      => 'open',
                'due_at'      => Carbon::yesterday(),
            ],
            // 6. Low resolved — no escalation
            [
                'title'       => 'Update footer copyright year from 2024 to 2025',
                'description' => 'The website footer still displays "© 2024 ServerPartDeals". This needs to be updated to "© 2025 ServerPartDeals" across all pages including the marketing site, customer portal, and admin dashboard.',
                'priority'    => 'low',
                'category'    => 'bug',
                'status'      => 'resolved',
            ],
            // 7. Medium bug
            [
                'title'       => 'Email notifications not sent when order status changes',
                'description' => 'Customers are not receiving email notifications when their order status changes from "processing" to "shipped". The email templates exist and the mail configuration is correct. The issue appears to be in the OrderObserver — the notify() call is being skipped when the status transition happens via bulk update queries that bypass model events.',
                'priority'    => 'medium',
                'category'    => 'bug',
                'status'      => 'in_progress',
            ],
            // 8. High feature overdue — escalated (high + open + overdue)
            [
                'title'       => 'Integrate Stripe refund API for automated refund processing',
                'description' => 'Currently, refunds must be issued manually through the Stripe dashboard by the finance team. This takes up to 24 hours and requires manual intervention for every refund request. Integrating the Stripe Refund API would allow refunds to be processed automatically within minutes of approval in our admin panel, reducing support workload and improving customer satisfaction.',
                'priority'    => 'high',
                'category'    => 'feature',
                'status'      => 'open',
                'due_at'      => Carbon::now()->subDays(30),
            ],
        ];

        foreach ($issues as $data) {
            $service->create($data);
        }
    }
}
