<?php

namespace App\Http\Controllers\Api;

use App\Models\Folio;
use App\Enums\FolioTypeEnum;
use Illuminate\Http\Request;
use App\Models\FolioTransition;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Database\Eloquent\Builder;

class AvgTimeTransitionReportController extends ApiController
{
    private function addTransitionJoin($query, int $prevStateId, int $nextStateId, string $alias)
    {
        return $query->join(DB::raw("(
            SELECT folio_id, created_at as {$alias}_date
            FROM folio_transitions
            WHERE prev_state_id = {$prevStateId} AND next_state_id = {$nextStateId}
        ) as {$alias}"), 'f.id', '=', "{$alias}.folio_id");
    }

    public function __invoke(Request $request)
    {
        $avgTransitionTimes = DB::table('folios as f');

        $avgTransitionTimes = $this->addTransitionJoin($avgTransitionTimes, 1, 2, 'ft');
        $avgTransitionTimes = $this->addTransitionJoin($avgTransitionTimes, 2, 3, 'st');

        $avgTransitionTimesPrevio = $avgTransitionTimes
            ->join('folio_states as fs1', 'fs1.id', '=', DB::raw('2'))  // From state
            ->join('folio_states as fs2', 'fs2.id', '=', DB::raw('3'))  // To state
            ->select(
                'f.classification',
                'fs1.title as from',
                'fs2.title as to',
                DB::raw('AVG(TIMESTAMPDIFF(SECOND, ft.ft_date, st.st_date)) as avg_second_difference')
            )
            ->where('f.type', FolioTypeEnum::Previo->value)
            ->groupBy('f.classification', 'fs1.title', 'fs2.title')
            ->orderBy('f.classification', 'asc')
            ->get();

        $avgTransitionTimes2 = DB::table('folios as f');

        $avgTransitionTimes2 = $this->addTransitionJoin($avgTransitionTimes2, 6, 7, 'ft');
        $avgTransitionTimes2 = $this->addTransitionJoin($avgTransitionTimes2, 7, 9, 'st');

        $avgTransitionTimesFolio = $avgTransitionTimes2
            ->join('folio_states as fs1', 'fs1.id', '=', DB::raw('7'))  // From state
            ->join('folio_states as fs2', 'fs2.id', '=', DB::raw('9'))  // To state
            ->select(
                'f.classification',
                'fs1.title as from',
                'fs2.title as to',
                DB::raw('AVG(TIMESTAMPDIFF(SECOND, ft.ft_date, st.st_date)) as avg_second_difference')
            )
            ->where('f.type', FolioTypeEnum::Folio->value)
            ->groupBy('f.classification', 'fs1.title', 'fs2.title')
            ->orderBy('f.classification', 'asc')
            ->get();

        $avgTransitionTimesFromPrevios = $avgTransitionTimes->clone()->where('f.type', FolioTypeEnum::Previo->value)->get();
        $avgTransitionTimesFromFolios = $avgTransitionTimes->clone()->where('f.type', FolioTypeEnum::Folio->value)->get();

        // New query for 7->9->10 transition
        $avgTransitionTimes3 = DB::table('folios as f');

        $avgTransitionTimes3 = $this->addTransitionJoin($avgTransitionTimes3, 7, 9, 'ft');
        $avgTransitionTimes3 = $this->addTransitionJoin($avgTransitionTimes3, 9, 10, 'st');

        $avgTransitionTimesFolioProduction = $avgTransitionTimes3
            ->join('folio_states as fs1', 'fs1.id', '=', DB::raw('9'))  // From state
            ->join('folio_states as fs2', 'fs2.id', '=', DB::raw('10'))  // To state
            ->select(
                'f.classification',
                'fs1.title as from',
                'fs2.title as to',
                DB::raw('AVG(TIMESTAMPDIFF(SECOND, ft.ft_date, st.st_date)) as avg_second_difference')
            )
            ->where('f.type', FolioTypeEnum::Folio->value)
            ->groupBy('f.classification', 'fs1.title', 'fs2.title')
            ->orderBy('f.classification', 'asc')
            ->get();

        // Merge existing folio results with new 7->9->10 results
        $mergedFolioResults = $avgTransitionTimesFolio->merge($avgTransitionTimesFolioProduction);

        return response()->json([
            'previo' => $avgTransitionTimesPrevio,
            'folio' => $mergedFolioResults
        ]);
    }

    // public function __invoke()
    // {
    //     $avgTransitionTimes = DB::table('folios as f')
    //         ->join(DB::raw('(
    //             SELECT folio_id, created_at as first_date
    //             FROM folio_transitions
    //             WHERE prev_state_id = 1 AND next_state_id = 2
    //         ) as ft'), 'f.id', '=', 'ft.folio_id')
    //         ->join(DB::raw('(
    //             SELECT folio_id, created_at as second_date
    //             FROM folio_transitions
    //             WHERE prev_state_id = 2 AND next_state_id = 3
    //         ) as st'), 'f.id', '=', 'st.folio_id')
    //         ->join('folio_states as fs1', 'fs1.id', '=', DB::raw('2'))
    //         ->join('folio_states as fs2', 'fs2.id', '=', DB::raw('3'))
    //         ->select(
    //             'f.classification',
    //             'fs1.title as from',
    //             'fs2.title as to',
    //             DB::raw('
    //                 AVG(
    //                     /* Calculate working hours between dates */
    //                     (
    //                         /* Get working days (excluding weekends) */
    //                         (DATEDIFF(st.second_date, ft.first_date) + 1 -
    //                         /* Subtract weekends */
    //                         (
    //                             /* Count Saturdays */
    //                             ((DATEDIFF(st.second_date, ft.first_date) + WEEKDAY(ft.first_date)) DIV 7) +
    //                             CASE WHEN
    //                                 WEEKDAY(ft.first_date) > WEEKDAY(st.second_date) AND
    //                                 (DATEDIFF(st.second_date, ft.first_date) % 7) >= (7 - WEEKDAY(ft.first_date))
    //                             THEN 1 ELSE 0 END +
    //                             /* Count Sundays */
    //                             ((DATEDIFF(st.second_date, ft.first_date) + WEEKDAY(ft.first_date)) DIV 7) +
    //                             CASE WHEN
    //                                 WEEKDAY(ft.first_date) > WEEKDAY(st.second_date) AND
    //                                 (DATEDIFF(st.second_date, ft.first_date) % 7) >= (8 - WEEKDAY(ft.first_date))
    //                             THEN 1 ELSE 0 END
    //                         )) * 10 * 3600 + /* 10 working hours per full day */

    //                         /* Add partial hours from first day */
    //                         CASE
    //                             WHEN TIME(ft.first_date) < "08:00:00" THEN 0
    //                             WHEN TIME(ft.first_date) > "18:00:00" THEN 0
    //                             WHEN TIME(ft.first_date) BETWEEN "08:00:00" AND "18:00:00"
    //                                 THEN TIMESTAMPDIFF(SECOND, TIME(ft.first_date), "18:00:00")
    //                         END +

    //                         /* Add partial hours from last day */
    //                         CASE
    //                             WHEN TIME(st.second_date) < "08:00:00" THEN 0
    //                             WHEN TIME(st.second_date) > "18:00:00" THEN 36000
    //                             WHEN TIME(st.second_date) BETWEEN "08:00:00" AND "18:00:00"
    //                                 THEN TIMESTAMPDIFF(SECOND, "08:00:00", TIME(st.second_date))
    //                         END -

    //                         /* Subtract non-working hours if same day */
    //                         CASE
    //                             WHEN DATE(ft.first_date) = DATE(st.second_date) AND
    //                                  TIME(ft.first_date) BETWEEN "08:00:00" AND "18:00:00" AND
    //                                  TIME(st.second_date) BETWEEN "08:00:00" AND "18:00:00"
    //                             THEN TIMESTAMPDIFF(SECOND, ft.first_date, st.second_date)
    //                             ELSE 0
    //                         END
    //                     )
    //                 ) as avg_seconds_difference
    //             ')
    //         )
    //         ->groupBy('f.classification')
    //         ->orderBy('f.classification', 'asc');

    //     $avgTransitionTimesFromFolios = $avgTransitionTimes->clone()->where('f.type', FolioTypeEnum::Folio->value)->get();
    //     $avgTransitionTimesFromPrevios = $avgTransitionTimes->clone()->where('f.type', FolioTypeEnum::Previo->value)->get();

    //     return response()->json([
    //         'previo' => $avgTransitionTimesFromPrevios->map(function ($item) {
    //             return [
    //                 'classification' => $item->classification,
    //                 'from' => $item->from,
    //                 'to' => $item->to,
    //                 'avg_second_difference' => $item->avg_seconds_difference,
    //                 'avg_working_seconds' => round($item->avg_seconds_difference, 2),
    //                 'avg_working_hours' => round($item->avg_seconds_difference / 3600, 2),
    //             ];
    //         }),
    //         'folio' => $avgTransitionTimesFromFolios->map(function ($item) {
    //             return [
    //                 'classification' => $item->classification,
    //                 'from' => $item->from,
    //                 'to' => $item->to,
    //                 'avg_second_difference' => $item->avg_seconds_difference,
    //                 'avg_working_seconds' => round($item->avg_seconds_difference, 2),
    //                 'avg_working_hours' => round($item->avg_seconds_difference / 3600, 2),
    //             ];
    //         }),
    //     ]);


    //     return response()->json([
    //         'data' => $avgTransitionTimes->map(function ($item) {
    //             return [
    //                 'classification' => $item->classification,
    //                 'from' => $item->from,
    //                 'to' => $item->to,
    //                 'avg_second_difference' => $item->avg_seconds_difference,
    //                 'avg_working_seconds' => round($item->avg_seconds_difference, 2),
    //                 'avg_working_hours' => round($item->avg_seconds_difference / 3600, 2),
    //                 'avg_working_days' => round($item->avg_seconds_difference / (3600 * 10), 2), // 10 working hours per day
    //             ];
    //         })
    //     ]);
    // }

}
