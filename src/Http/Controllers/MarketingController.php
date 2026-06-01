<?php

namespace Revo\MarketingTool\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;

class MarketingController extends Controller
{
    /**
     * Show the marketing statistics dashboard index page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('marketing-tool::index');
    }

    /**
     * Fetch marketing statistics data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from' => 'nullable|date_format:Y-m-d',
            'to' => 'nullable|date_format:Y-m-d',
        ]);

        $from = $request->input('from') ? Carbon::parse($validated['from'])->startOfDay() : now()->subDays(23)->startOfDay();
        $to = $request->input('to') ? Carbon::parse($validated['to'])->endOfDay() : now()->endOfDay();

        return response()->json([
            'monthly_comparison' => $this->getMonthlyComparison(),
            'funnels' => $this->getFunnels($from, $to),
            'daily_stats' => $this->getDailyStats($from, $to),
            'sources' => $this->getSources($from, $to),
            'retention' => $this->getRetentionData(),
        ]);
    }

    /**
     * Calculate monthly acquisition history and compare current partial month with same period last month.
     *
     * @return array
     */
    private function getMonthlyComparison(): array
    {
        $now = now();
        $months = [];
        $userClass = config('marketing-tool.models.user', \App\Models\User::class);
        
        for ($i = 3; $i >= 0; $i--) {
            $date = (clone $now)->subMonths($i);
            $startOfMonth = (clone $date)->startOfMonth();
            $endOfMonth = (clone $date)->endOfMonth();
            
            $monthName = $date->format('F');
            $yearMonth = $date->format('Y-m');

            $total = $userClass::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
            $uz = $userClass::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->where('phone', 'like', '998%')
                ->count();
            $ru = $total - $uz;

            $months[] = [
                'name' => $monthName,
                'year_month' => $yearMonth,
                'total' => $total,
                'uz' => $uz,
                'ru' => $ru,
            ];
        }

        $currentStart = now()->startOfMonth();
        $currentEnd = now();
        $currentCount = $userClass::whereBetween('created_at', [$currentStart, $currentEnd])->count();

        $prevStart = now()->subMonth()->startOfMonth();
        $prevEnd = now()->subMonth()->setDay(now()->day)->endOfDay();
        $prevCount = $userClass::whereBetween('created_at', [$prevStart, $prevEnd])->count();

        return [
            'history' => $months,
            'current' => [
                'name' => now()->format('F'),
                'range' => now()->startOfMonth()->format('d.m') . ' - ' . now()->format('d.m'),
                'total' => $currentCount,
                'prev_total' => $prevCount,
                'diff' => $currentCount - $prevCount,
                'pct' => $prevCount > 0 ? round((($currentCount - $prevCount) / $prevCount) * 100, 1) : 0,
            ]
        ];
    }

    /**
     * Calculate acquisition and active user funnels.
     *
     * @param  \Carbon\Carbon  $from
     * @param  \Carbon\Carbon  $to
     * @return array
     */
    private function getFunnels($from, $to): array
    {
        $userClass = config('marketing-tool.models.user', \App\Models\User::class);
        $userActivityClass = config('marketing-tool.models.user_activity');
        $dateColumn = config('marketing-tool.fields.user_activity.date_column', 'day');

        // New User Funnel
        $newUsers = $userClass::whereBetween('created_at', [$from, $to])->count();
        $newUsersUz = $userClass::whereBetween('created_at', [$from, $to])->where('phone', 'like', '998%')->count();
        $newUsersRu = $newUsers - $newUsersUz;

        $identified = $userClass::whereBetween('created_at', [$from, $to])
            ->whereNotNull('identified_at')
            ->count();
        $identifiedUz = $userClass::whereBetween('created_at', [$from, $to])
            ->whereNotNull('identified_at')
            ->where('phone', 'like', '998%')
            ->count();
        $identifiedRu = $identified - $identifiedUz;

        $transacting = $userClass::whereBetween('created_at', [$from, $to])
            ->whereHas('transfers', function ($q) {
                $q->where('credit_state', 1)
                  ->where('debit_state', 1);
            })->count();
        $transactingUz = $userClass::whereBetween('created_at', [$from, $to])
            ->whereHas('transfers', function ($q) {
                $q->where('credit_state', 1)
                  ->where('debit_state', 1);
            })
            ->where('phone', 'like', '998%')
            ->count();
        $transactingRu = $transacting - $transactingUz;

        // Active User Funnel
        $activeUsers = 0;
        $activeUsersUz = 0;
        $activeTransacting = 0;
        $activeTransactingUz = 0;

        if ($userActivityClass) {
            $activeUsers = $userActivityClass::whereBetween($dateColumn, [$from, $to])->distinct('user_id')->count('user_id');
            $activeUsersUz = $userActivityClass::whereBetween($dateColumn, [$from, $to])
                ->whereHas('user', function ($q) {
                    $q->where('phone', 'like', '998%');
                })
                ->distinct('user_id')->count('user_id');

            $activeTransacting = $userActivityClass::whereBetween($dateColumn, [$from, $to])
                ->whereHas('user', function ($q) {
                    $q->whereHas('transfers', function ($tq) {
                        $tq->where('credit_state', 1)->where('debit_state', 1);
                    });
                })
                ->distinct('user_id')->count('user_id');

            $activeTransactingUz = $userActivityClass::whereBetween($dateColumn, [$from, $to])
                ->whereHas('user', function ($q) {
                    $q->where('phone', 'like', '998%')
                      ->whereHas('transfers', function ($tq) {
                        $tq->where('credit_state', 1)->where('debit_state', 1);
                    });
                })
                ->distinct('user_id')->count('user_id');
        }

        return [
            'new_user' => [
                ['label' => 'New User', 'total' => $newUsers, 'uz' => $newUsersUz, 'ru' => $newUsersRu],
                ['label' => 'Identifikatsiya qilganlar', 'total' => $identified, 'uz' => $identifiedUz, 'ru' => $identifiedRu],
                ['label' => 'Transaksiya qilganlar', 'total' => $transacting, 'uz' => $transactingUz, 'ru' => $transactingRu],
            ],
            'active_user' => [
                ['label' => 'Active user', 'total' => $activeUsers, 'uz' => $activeUsersUz, 'ru' => $activeUsers - $activeUsersUz],
                ['label' => 'Transaksiya qilgan userla', 'total' => $activeTransacting, 'uz' => $activeTransactingUz, 'ru' => $activeTransacting - $activeTransactingUz],
            ]
        ];
    }

    /**
     * Calculate daily user acquisition and activity statistics.
     *
     * @param  \Carbon\Carbon  $from
     * @param  \Carbon\Carbon  $to
     * @return array
     */
    private function getDailyStats($from, $to): array
    {
        $userClass = config('marketing-tool.models.user', \App\Models\User::class);
        $userActivityClass = config('marketing-tool.models.user_activity');
        $transferClass = config('marketing-tool.models.transfer', \App\Models\Transfer::class);
        $dateColumn = config('marketing-tool.fields.user_activity.date_column', 'day');

        $labels = [];
        $newUsers = [];
        $newUsersUz = [];
        $newUsersRu = [];
        $activeUsers = [];
        $activeUsersUz = [];
        $activeUsersRu = [];
        $transfers = [];
        $transfersUz = [];
        $transfersRu = [];

        $current = clone $from;
        while ($current <= $to) {
            $date = $current->format('Y-m-d');
            $labels[] = $date;

            $nuTotal = $userClass::whereDate('created_at', $date)->count();
            $nuUz = $userClass::whereDate('created_at', $date)->where('phone', 'like', '998%')->count();
            $newUsers[] = $nuTotal;
            $newUsersUz[] = $nuUz;
            $newUsersRu[] = $nuTotal - $nuUz;

            $auTotal = 0;
            $auUz = 0;
            if ($userActivityClass) {
                $auTotal = $userActivityClass::where($dateColumn, $date)->distinct('user_id')->count('user_id');
                $auUz = $userActivityClass::where($dateColumn, $date)
                    ->whereHas('user', function ($q) { $q->where('phone', 'like', '998%'); })
                    ->distinct('user_id')->count('user_id');
            }
            $activeUsers[] = $auTotal;
            $activeUsersUz[] = $auUz;
            $activeUsersRu[] = $auTotal - $auUz;

            $transTotal = 0;
            $transUz = 0;
            if ($transferClass) {
                $transTotal = $transferClass::whereDate('created_at', $date)
                    ->where('credit_state', 1)
                    ->where('debit_state', 1)
                    ->count();
                $transUz = $transferClass::whereDate('created_at', $date)
                    ->where('credit_state', 1)
                    ->where('debit_state', 1)
                    ->where('phone', 'like', '998%')
                    ->count();
            }
            $transfers[] = $transTotal;
            $transfersUz[] = $transUz;
            $transfersRu[] = $transTotal - $transUz;

            $current->addDay();
        }

        return [
            'labels' => $labels,
            'new_user' => [
                'total' => $newUsers,
                'uz' => $newUsersUz,
                'ru' => $newUsersRu,
            ],
            'active_user' => [
                'total' => $activeUsers,
                'uz' => $activeUsersUz,
                'ru' => $activeUsersRu,
            ],
            'transfers' => [
                'total' => $transfers,
                'uz' => $transfersUz,
                'ru' => $transfersRu,
            ]
        ];
    }

    /**
     * Calculate app installation source statistics.
     *
     * @param  \Carbon\Carbon  $from
     * @param  \Carbon\Carbon  $to
     * @return array
     */
    private function getSources($from, $to): array
    {
        $sourceClass = config('marketing-tool.models.app_discovery_source');
        $userSourceClass = config('marketing-tool.models.user_app_discovery_source');

        if (!$sourceClass || !$userSourceClass) {
            return [];
        }

        $sources = $sourceClass::all();
        $data = [];

        foreach ($sources as $source) {
            $count = $userSourceClass::where('app_discovery_source_id', $source->id)
                ->whereBetween('created_at', [$from, $to])
                ->count();
            
            if ($count > 0) {
                $data[] = [
                    'label' => $source->title_uz ?? $source->title_ru,
                    'value' => $count,
                ];
            }
        }

        usort($data, fn ($a, $b) => $b['value'] <=> $a['value']);

        return $data;
    }

    /**
     * Calculate monthly cohort retention dynamically.
     *
     * @return array
     */
    private function getRetentionData(): array
    {
        $userClass = config('marketing-tool.models.user', \App\Models\User::class);
        $userActivityClass = config('marketing-tool.models.user_activity');
        $transferClass = config('marketing-tool.models.transfer', \App\Models\Transfer::class);
        $dateColumn = config('marketing-tool.fields.user_activity.date_column', 'day');

        $MONTH_NAMES = ["Yan", "Fev", "Mar", "Apr", "May", "Iyn", "Iyl", "Avg", "Sen", "Okt", "Noy", "Dek"];

        // 1. Yangi Foydalanuvchilar Cohort Retention
        $newCohorts = [];
        $now = now();

        // Get all users in the last 18 months
        $users = $userClass::where('created_at', '>=', now()->subMonths(18))
            ->select('id', 'created_at')
            ->get();

        // Group by month
        $groupedUsers = [];
        foreach ($users as $user) {
            $monthKey = $user->created_at->format('Y-m'); // e.g. "2025-01"
            $groupedUsers[$monthKey][] = $user;
        }
        ksort($groupedUsers);

        foreach ($groupedUsers as $monthKey => $cohortUsers) {
            $year = (int) substr($monthKey, 0, 4);
            $month = (int) substr($monthKey, 5, 2);
            $cohortLabel = $MONTH_NAMES[$month - 1] . ' ' . $year;

            $totalCount = count($cohortUsers);
            if ($totalCount === 0) continue;

            $userIds = array_column($cohortUsers, 'id');

            // Fetch all activity dates & successful transfer dates for these users
            $activityDates = [];
            if ($userActivityClass) {
                $activities = $userActivityClass::whereIn('user_id', $userIds)
                    ->get(['user_id', $dateColumn, 'created_at']);
                foreach ($activities as $act) {
                    $dateStr = $act->$dateColumn instanceof Carbon ? $act->$dateColumn->format('Y-m-d') : substr((string)$act->$dateColumn, 0, 10);
                    $activityDates[$act->user_id][] = Carbon::parse($dateStr);
                }
            }

            if ($transferClass) {
                $transfers = $transferClass::whereIn('user_id', $userIds)
                    ->where('credit_state', 1)
                    ->where('debit_state', 1)
                    ->get(['user_id', 'created_at']);
                foreach ($transfers as $tr) {
                    $activityDates[$tr->user_id][] = $tr->created_at;
                }
            }

            // Calculate retention metrics for each user in the cohort
            $retained7Day = 0;
            $retained15Day = 0;
            $monthlyRetentionCounts = array_fill(1, 12, 0); // 1 to 12 months

            // To avoid double counting same user in same target period
            foreach ($cohortUsers as $user) {
                $regDate = $user->created_at;
                $dates = $activityDates[$user->id] ?? [];
                
                $has7 = false;
                $has15 = false;
                $monthsRetained = array_fill(1, 12, false);

                foreach ($dates as $date) {
                    $diffDays = $regDate->diffInDays($date);
                    if ($diffDays >= 0 && $diffDays <= 7) {
                        $has7 = true;
                    }
                    if ($diffDays >= 0 && $diffDays <= 15) {
                        $has15 = true;
                    }

                    // Month diff based on calendar months
                    $diffMonths = ($date->year - $regDate->year) * 12 + ($date->month - $regDate->month);
                    if ($diffMonths >= 1 && $diffMonths <= 12) {
                        $monthsRetained[$diffMonths] = true;
                    }
                }

                if ($has7) $retained7Day++;
                if ($has15) $retained15Day++;
                foreach ($monthsRetained as $m => $val) {
                    if ($val) $monthlyRetentionCounts[$m]++;
                }
            }

            // Calculate percentages
            $v = [
                $totalCount > 0 ? round(($retained7Day / $totalCount) * 100) : 0,
                $totalCount > 0 ? round(($retained15Day / $totalCount) * 100) : 0,
            ];

            // Determine how many calendar months have passed since this cohort month
            $monthsElapsed = ($now->year - $year) * 12 + ($now->month - $month);

            for ($m = 1; $m <= 12; $m++) {
                if ($m <= $monthsElapsed) {
                    $v[] = $totalCount > 0 ? round(($monthlyRetentionCounts[$m] / $totalCount) * 100) : 0;
                }
            }

            $newCohorts[] = [
                'l' => $cohortLabel,
                'u' => $totalCount,
                'yr' => $year,
                'v' => $v,
            ];
        }

        // 2. Faol Foydalanuvchilar (First transfer month cohort)
        $activeCohorts = [];
        if ($transferClass) {
            // Find first successful transfer for all users
            $firstTransfers = $transferClass::where('credit_state', 1)
                ->where('debit_state', 1)
                ->selectRaw('user_id, MIN(created_at) as first_transfer_at')
                ->groupBy('user_id')
                ->having('first_transfer_at', '>=', now()->subMonths(18))
                ->get();

            // Group users by first transfer month
            $activeGroupedUsers = [];
            foreach ($firstTransfers as $ft) {
                $monthKey = Carbon::parse($ft->first_transfer_at)->format('Y-m');
                $activeGroupedUsers[$monthKey][] = [
                    'user_id' => $ft->user_id,
                    'first_transfer_at' => Carbon::parse($ft->first_transfer_at),
                ];
            }
            ksort($activeGroupedUsers);

            foreach ($activeGroupedUsers as $monthKey => $cohortUsers) {
                $year = (int) substr($monthKey, 0, 4);
                $month = (int) substr($monthKey, 5, 2);
                $cohortLabel = $MONTH_NAMES[$month - 1] . ' ' . $year;

                $totalCount = count($cohortUsers);
                if ($totalCount === 0) continue;

                $userIds = array_column($cohortUsers, 'user_id');

                // Fetch all successful transfers for these users
                $transfers = $transferClass::whereIn('user_id', $userIds)
                    ->where('credit_state', 1)
                    ->where('debit_state', 1)
                    ->get(['user_id', 'created_at']);

                $transferDates = [];
                foreach ($transfers as $tr) {
                    $transferDates[$tr->user_id][] = $tr->created_at;
                }

                $retained7Day = 0;
                $retained15Day = 0;
                $monthlyRetentionCounts = array_fill(1, 12, 0);

                foreach ($cohortUsers as $cu) {
                    $firstTrDate = $cu['first_transfer_at'];
                    $dates = $transferDates[$cu['user_id']] ?? [];

                    $has7 = false;
                    $has15 = false;
                    $monthsRetained = array_fill(1, 12, false);

                    foreach ($dates as $date) {
                        $diffDays = $firstTrDate->diffInDays($date);
                        if ($diffDays > 0 && $diffDays <= 7) {
                            $has7 = true;
                        }
                        if ($diffDays > 0 && $diffDays <= 15) {
                            $has15 = true;
                        }

                        $diffMonths = ($date->year - $firstTrDate->year) * 12 + ($date->month - $firstTrDate->month);
                        if ($diffMonths >= 1 && $diffMonths <= 12) {
                            $monthsRetained[$diffMonths] = true;
                        }
                    }

                    if ($has7) $retained7Day++;
                    if ($has15) $retained15Day++;
                    foreach ($monthsRetained as $m => $val) {
                        if ($val) $monthlyRetentionCounts[$m]++;
                    }
                }

                $v = [
                    $totalCount > 0 ? round(($retained7Day / $totalCount) * 100) : 0,
                    $totalCount > 0 ? round(($retained15Day / $totalCount) * 100) : 0,
                ];

                $monthsElapsed = ($now->year - $year) * 12 + ($now->month - $month);

                for ($m = 1; $m <= 12; $m++) {
                    if ($m <= $monthsElapsed) {
                        $v[] = $totalCount > 0 ? round(($monthlyRetentionCounts[$m] / $totalCount) * 100) : 0;
                    }
                }

                $activeCohorts[] = [
                    'l' => $cohortLabel,
                    'u' => $totalCount,
                    'yr' => $year,
                    'v' => $v,
                ];
            }
        }

        return [
            'new' => $newCohorts,
            'active' => $activeCohorts,
        ];
    }
}
