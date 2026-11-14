<?php

namespace App\Support;

use Carbon\CarbonInterface;

class AcademicTerms
{
    /** Terms running on the given date, one per cohort at most. */
    public static function active(CarbonInterface $now): array
    {
        $md = (int) $now->format('md');
        $active = [];

        foreach (config('spaces.terms') as $cohort) {
            foreach ($cohort['terms'] as $term) {
                $inside = $term['start'] <= $term['end']
                    ? ($md >= $term['start'] && $md <= $term['end'])
                    : ($md >= $term['start'] || $md <= $term['end']);

                if ($inside) {
                    $active[] = $cohort['name'] . ': ' . $term['name'];
                    break;
                }
            }
        }

        return $active;
    }

    public static function label(CarbonInterface $now): string
    {
        return implode(' · ', self::active($now)) ?: 'Break';
    }
}