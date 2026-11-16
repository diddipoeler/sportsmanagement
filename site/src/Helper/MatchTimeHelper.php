<?php
/**
 * Joomla 5/6 SportsManagement site helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Format match times without loading the legacy HTML helper.
 */
final class MatchTimeHelper
{
    /**
     * Format a match start time and optionally display live-match status.
     * Uses Joomla date formatting and component locale strings, then
     * calculates a live game label when the match falls into its configured
     * regular or additional time window. Escape the final live-status title
     * and translated label only once so user-configured text cannot create
     * markup or display HTML entities literally in tooltip attributes.
     * Rejects zero database timestamps and malformed optional text settings.
     * Non-scalar legacy values and invalid dates fall back to an unavailable
     * time instead of causing PHP 8 conversion errors.
     * Invalid break and live-status text settings also use empty labels
     * rather than attempting to cast arrays to strings. Match period counts,
     * duration and halftime configuration are bounded before arithmetic:
     * malformed historical values cannot generate PHP 8 warnings or force
     * excessive iterations while building live labels. Validates SQL match
     * dates before calling strtotime(), rejecting relative or impossible
     * dates that PHP would otherwise silently normalize. Rejects unsupported
     * printf placeholders in translated suffixes and guards Joomla date
     * formatting errors rather than crashing a frontend match listing.
     * @param object $game Match record with a match_date field.
     * @param array<string,mixed> $config Match time presentation settings.
     * @param array<string,mixed> $overallConfig Global component date options.
     * @param object|null $project Optional project period configuration.
     * @return string HTML-formatted match time or live-match label.
     */
    public static function format(
        object $game,
        array $config,
        array $overallConfig,
        ?object $project = null
    ): string {
        $rawTimeFormat = $overallConfig['time_format'] ?? 'H:i';
        $timeFormat = is_string($rawTimeFormat) && $rawTimeFormat !== ''
            && strlen($rawTimeFormat) <= 100 ? $rawTimeFormat : 'H:i';
        $timeSuffix = Text::_('COM_SPORTSMANAGEMENT_GLOBAL_CLOCK');

        if ($timeSuffix === 'COM_SPORTSMANAGEMENT_GLOBAL_CLOCK') {
            $timeSuffix = '%1$s&nbsp;h';
        }

        // Translated strings are configurable. Reject incompatible printf
        // placeholders before formatting the match time in PHP 8, where an
        // unknown conversion or missing argument may throw ValueError.
        if (!is_string($timeSuffix) || strlen($timeSuffix) > 500
            || preg_match('/%(?!%|(?:1\\$)?s)/', $timeSuffix) === 1
            || preg_match_all('/%(?:1\\$)?s/', $timeSuffix) !== 1) {
            $timeSuffix = '%1$s&nbsp;h';
        }

        $rawMatchDate = $game->match_date ?? null;
        // Preserve the original SQL value: trimming first would allow
        // trailing control bytes and whitespace past strict validation.
        $matchDate = is_string($rawMatchDate) ? $rawMatchDate : '';
        $timestamp = self::sqlTimestamp($matchDate);
        if ($timestamp === false) {
            $matchTime = '--&nbsp;:&nbsp;--';

            return !empty($config['show_time_suffix'])
                ? sprintf($timeSuffix, $matchTime)
                : $matchTime;
        }

        try {
            $matchTime = HTMLHelper::date($matchDate, $timeFormat, 'UTC');
        } catch (\Throwable) {
            return '--&nbsp;:&nbsp;--';
        }
        $output = !empty($config['show_time_suffix'])
            ? sprintf($timeSuffix, $matchTime)
            : $matchTime;

        if (empty($config['mark_now_playing']) || $project === null) {
            return $output;
        }

        $now = time();
        $parts = self::boundedMinutes($project->game_parts ?? 1, 1, 12);
        $regularTime = self::boundedMinutes($project->game_regular_time ?? 0, 0, 1440);
        $halftime = self::boundedMinutes($project->halftime ?? 0, 0, 240);
        $elapsedMinutes = ($halftime * max(0, $parts - 1)) + $regularTime;
        $homeScore = $game->team1_result ?? null;
        $awayScore = $game->team2_result ?? null;

        if (!empty($project->allow_add_time)
            && is_numeric($homeScore) && is_numeric($awayScore)
            && (float) $homeScore === (float) $awayScore) {
            $elapsedMinutes += self::boundedMinutes($project->add_time ?? 0, 0, 240);
        }

        $matchStamp = $timestamp;
        if ($regularTime === 0 || $elapsedMinutes === 0
            || $now < $matchStamp || $now > $matchStamp + ($elapsedMinutes * 60)) {
            return $output;
        }

        $startText = $output . ' ';
        $rawTitle = $config['mark_now_playing_alt_text'] ?? '';
        $titleTemplate = is_string($rawTitle) ? $rawTitle : '';
        $title = str_replace(
            '%STARTTIME%',
            $startText,
            trim($titleTemplate)
        );
        $title = str_replace(
            '%ACTUALTIME%',
            self::actualTime($now, $matchStamp, $config, $project),
            $title
        );
        $style = !empty($config['mark_now_playing_blink'])
            ? ' style="text-decoration:blink"'
            : '';
        $rawText = $config['mark_now_playing_text'] ?? '';
        $text = htmlspecialchars(
            Text::_(is_string($rawText) ? $rawText : ''),
            ENT_QUOTES,
            'UTF-8'
        );

        return '<b><i><acronym title="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '"'
            . $style . '>' . $text . '</acronym></i></b>';
    }

    /**
     * Validate and parse a stored SQL match date without calendar rollover.
     *
     * Accepts YYYY-MM-DD, YYYY-MM-DD HH:MM, YYYY-MM-DD HH:MM:SS and
     * their T-separated equivalents. Verifies the actual calendar date and
     * time fields before strtotime() to reject relative date expressions,
     * malformed zero dates and impossible imported fixtures.
     *
     * @param string $value Stored match_date field.
     * @return int|false Valid Unix timestamp, or false for invalid dates.
     */
    private static function sqlTimestamp(string $value): int|false
    {
        if ($value === '' || strlen($value) > 19 || trim($value) !== $value
            || preg_match(
                '/^([0-9]{4})-([0-9]{2})-([0-9]{2})(?:[ T]([0-9]{2}):([0-9]{2})(?::([0-9]{2}))?)?$/D',
                $value,
                $parts
            ) !== 1
            || !checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1])) {
            return false;
        }

        if (isset($parts[4]) && ((int) $parts[4] > 23 || (int) $parts[5] > 59
            || (isset($parts[6]) && (int) $parts[6] > 59))) {
            return false;
        }

        return strtotime($value);
    }

    /**
     * Validate one bounded, nonnegative match duration configuration value.
     *
     * Accepts zero-padded decimal settings from legacy projects and clamps
     * complete valid integers to the supported maximum. Invalid arrays,
     * booleans, negative numbers, decimals, partial text and overflows use
     * the explicit fallback instead of PHP 8 implicit numeric conversion.
     *
     * @param mixed $value Raw project duration or part-count setting.
     * @param int $fallback Value for invalid or absent configuration.
     * @param int $maximum Maximum supported positive duration or count.
     * @return int Valid bounded duration or count.
     */
    private static function boundedMinutes(mixed $value, int $fallback, int $maximum): int
    {
        if (!is_int($value) && (!is_string($value) || $value === ''
            || strlen($value) > 64 || preg_match('/^[0-9]+$/D', $value) !== 1)) {
            return $fallback;
        }

        if (is_string($value)) {
            $digits = ltrim($value, '0');
            $value = filter_var($digits === '' ? '0' : $digits, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 0],
            ]);
        }

        if ($value === false || $value < 0) {
            return $fallback;
        }

        return min($maximum, $value);
    }

    /**
     * Extract a plain-text live-match label from optional configuration.
     *
     * Accepts strings only and returns a trimmed label with a maximum length
     * of 500 bytes. Unexpected array or object values from legacy settings
     * do not trigger PHP 8 conversion warnings.
     *
     * @param mixed $value Raw configured live match text.
     * @return string Safe label text or an empty string.
     */
    private static function safeLabel(mixed $value): string
    {
        return is_string($value) && strlen($value) <= 500 ? trim($value) : '';
    }

    /**
     * Build the translated current match period or half-time label.
     * Calculates elapsed play minutes from the configured period length
     * and break duration for a currently active fixture. Return plain text
     * for replacement into the tooltip, which is HTML-escaped by format().
     * @param int $now Current Unix timestamp.
     * @param int $matchStamp Match start timestamp.
     * @param array<string,mixed> $config Live presentation settings.
     * @param object $project Match duration and break settings.
     * @return string Formatted current-match period label.
     */
    private static function actualTime(int $now, int $matchStamp, array $config, object $project): string
    {
        $goneSinceBegin = intdiv(max(0, $now - $matchStamp), 60);
        $gameParts = max(1, self::boundedMinutes($project->game_parts ?? 1, 1, 12));
        $partsTime = intdiv(self::boundedMinutes($project->game_regular_time ?? 0, 0, 1440), $gameParts);
        $halfTime = self::boundedMinutes($project->halftime ?? 0, 0, 240);
        $extraPart = !empty($project->allow_add_time) ? 1 : 0;
        $text = Text::_('COM_SPORTSMANAGEMENT_RESULTS_LIVE_WRONG');

        for ($part = 1; $part <= $gameParts + $extraPart; $part++) {
            $partStart = ($part - 1) * ($halfTime + $partsTime);
            $partEnd = $partStart + $partsTime;
            $nextPartStart = $partEnd + $halfTime;

            if ($goneSinceBegin >= $partStart && $goneSinceBegin <= $partEnd) {
                $text = str_replace(
                    ['%PART%', '%MINUTE%'],
                    [(string) $part, (string) ($goneSinceBegin + 1 - (($part - 1) * $halfTime))],
                    self::safeLabel($config['mark_now_playing_alt_actual_time'] ?? '')
                );
                break;
            }

            if ($goneSinceBegin > $partEnd && $goneSinceBegin < $nextPartStart) {
                $text = str_replace(
                    '%PART%',
                    (string) $part,
                    self::safeLabel($config['mark_now_playing_alt_actual_break'] ?? '')
                );
                break;
            }
        }

        return $text;
    }
}
