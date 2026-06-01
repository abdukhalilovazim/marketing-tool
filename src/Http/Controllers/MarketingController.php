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
}
