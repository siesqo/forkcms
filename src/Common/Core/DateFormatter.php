<?php

namespace Common\Core;

final class DateFormatter
{
    private const PHP_TO_ICU = [
        'Y' => 'yyyy', 'y' => 'yy',
        'F' => 'MMMM', 'M' => 'MMM', 'm' => 'MM', 'n' => 'M',
        'l' => 'EEEE', 'D' => 'EEE',
        'd' => 'dd',   'j' => 'd',
        'H' => 'HH',   'G' => 'H', 'h' => 'hh', 'g' => 'h',
        'A' => 'a',    'a' => 'a',
        'i' => 'mm',   's' => 'ss',
    ];

    /**
     * Format a timestamp with locale-aware month/day names using a PHP date format string.
     * Pass null as $timestamp to format the current time.
     */
    public static function format(int|\DateTime|string|null $timestamp, string $phpFormat, string $locale): string
    {
        $formatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            null,
            null,
            self::toIcu($phpFormat)
        );

        return $formatter->format(self::normalize($timestamp)) ?: '';
    }

    /**
     * Return a locale-aware relative time string ("3 minutes ago", "yesterday", etc.).
     * The $labelFn callable receives a label key and returns the translated string.
     * Labels with a %d placeholder are formatted via sprintf.
     */
    public static function timeAgoText(int|\DateTime $timestamp, callable $labelFn): string
    {
        $ts = self::normalize($timestamp);
        $diff = max(0, time() - $ts);

        if ($diff < 60) {
            return $diff <= 1
                ? $labelFn('TimeAgoJustNow')
                : sprintf($labelFn('TimeAgoSeconds'), $diff);
        }
        $minutes = (int) floor($diff / 60);
        if ($minutes < 60) {
            return $minutes === 1
                ? $labelFn('TimeAgoMinute')
                : sprintf($labelFn('TimeAgoMinutes'), $minutes);
        }
        $hours = (int) floor($diff / 3600);
        if ($hours < 24) {
            return $hours === 1
                ? $labelFn('TimeAgoHour')
                : sprintf($labelFn('TimeAgoHours'), $hours);
        }
        $days = (int) floor($diff / 86400);
        if ($days === 1) {
            return $labelFn('TimeAgoYesterday');
        }
        if ($days < 7) {
            return sprintf($labelFn('TimeAgoDays'), $days);
        }
        $weeks = (int) floor($days / 7);
        if ($weeks === 1) {
            return $labelFn('TimeAgoLastWeek');
        }
        if ($weeks < 4) {
            return sprintf($labelFn('TimeAgoWeeks'), $weeks);
        }
        $months = (int) floor($days / 30);
        if ($months === 1) {
            return $labelFn('TimeAgoLastMonth');
        }
        if ($months < 12) {
            return sprintf($labelFn('TimeAgoMonths'), $months);
        }
        $years = (int) floor($days / 365);
        if ($years === 1) {
            return $labelFn('TimeAgoLastYear');
        }
        return sprintf($labelFn('TimeAgoYears'), $years);
    }

    /**
     * Return an array of month names for the given locale, keyed 1–12.
     */
    public static function getMonths(string $locale, bool $abbreviated = false): array
    {
        $formatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            null,
            null,
            $abbreviated ? 'MMM' : 'MMMM'
        );
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = $formatter->format(mktime(0, 0, 0, $i, 1, 2024));
        }
        return $months;
    }

    /**
     * Return an array of weekday names keyed by 3-letter English abbreviation (sun, mon, …).
     * $firstDay accepts 'monday' (default) or 'sunday'.
     */
    public static function getWeekDays(string $locale, bool $abbreviated = false, string $firstDay = 'monday'): array
    {
        $formatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            null,
            null,
            $abbreviated ? 'EEE' : 'EEEE'
        );
        $keys = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
        $base = mktime(0, 0, 0, 1, 5, 2025); // known Sunday
        $days = [];
        foreach ($keys as $offset => $key) {
            $days[$key] = $formatter->format($base + $offset * 86400);
        }
        if ($firstDay === 'monday') {
            $sun = $days['sun'];
            unset($days['sun']);
            $days['sun'] = $sun;
        }
        return $days;
    }

    private static function toIcu(string $phpFormat): string
    {
        $icu = '';
        for ($i = 0, $len = strlen($phpFormat); $i < $len; $i++) {
            $char = $phpFormat[$i];
            if ($char === '\\' && $i + 1 < $len) {
                $icu .= "'" . $phpFormat[++$i] . "'";
            } elseif (isset(self::PHP_TO_ICU[$char])) {
                $icu .= self::PHP_TO_ICU[$char];
            } elseif (ctype_alpha($char)) {
                $icu .= "'" . $char . "'";
            } else {
                $icu .= $char;
            }
        }
        return $icu;
    }

    private static function normalize(int|\DateTime|string|null $timestamp): int
    {
        if ($timestamp === null) {
            return time();
        }
        if ($timestamp instanceof \DateTime) {
            return $timestamp->getTimestamp();
        }
        if (is_string($timestamp) && !is_numeric($timestamp)) {
            return (int) strtotime($timestamp);
        }
        return (int) $timestamp;
    }
}
