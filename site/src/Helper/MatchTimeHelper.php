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
     * Non-scalar legacy values and invalid dates fall back to an unavailable
     * time instead of causing PHP 8 conversion errors.
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

        $rawMatchDate = $game->match_date ?? null;
        $matchDate = is_string($rawMatchDate) ? trim($rawMatchDate) : '';
        $timestamp = $matchDate !== '' ? strtotime($matchDate) : false;
        if ($timestamp === false) {
            $matchTime = '--&nbsp;:&nbsp;--';

            return !empty($config['show_time_suffix'])
                ? sprintf($timeSuffix, $matchTime)
                : $matchTime;
        }

        $matchTime = HTMLHelper::date($matchDate, $timeFormat, 'UTC');
        $output = !empty($config['show_time_suffix'])
            ? sprintf($timeSuffix, $matchTime)
            : $matchTime;

        if (empty($config['mark_now_playing']) || $project === null) {
            return $output;
        }

        $now = time();
        $elapsedMinutes = ((int) ($project->halftime ?? 0) * max(0, (int) ($project->game_parts ?? 1) - 1))
            + (int) ($project->game_regular_time ?? 0);

        if (!empty($project->allow_add_time)
            && ($game->team1_result ?? null) == ($game->team2_result ?? null)) {
            $elapsedMinutes += (int) ($project->add_time ?? 0);
        }

        $matchStamp = $timestamp;
        if ($matchStamp === false || $now < $matchStamp || $now > $matchStamp + ($elapsedMinutes * 60)) {
            return $output;
        }

        $startText = $output . ' ';
        $title = str_replace(
            '%STARTTIME%',
            $startText,
            trim((string) ($config['mark_now_playing_alt_text'] ?? ''))
        );
        $title = str_replace(
            '%ACTUALTIME%',
            self::actualTime($now, $matchStamp, $config, $project),
            $title
        );
        $style = !empty($config['mark_now_playing_blink'])
            ? ' style="text-decoration:blink"'
            : '';
        $text = htmlspecialchars(
            Text::_((string) ($config['mark_now_playing_text'] ?? '')),
            ENT_QUOTES,
            'UTF-8'
        );

        return '<b><i><acronym title="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '"'
            . $style . '>' . $text . '</acronym></i></b>';
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
        $gameParts = max(1, (int) ($project->game_parts ?? 1));
        $partsTime = intdiv(max(0, (int) ($project->game_regular_time ?? 0)), $gameParts);
        $halfTime = max(0, (int) ($project->halftime ?? 0));
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
                    trim((string) ($config['mark_now_playing_alt_actual_time'] ?? ''))
                );
                break;
            }

            if ($goneSinceBegin > $partEnd && $goneSinceBegin < $nextPartStart) {
                $text = str_replace(
                    '%PART%',
                    (string) $part,
                    trim((string) ($config['mark_now_playing_alt_actual_break'] ?? ''))
                );
                break;
            }
        }

        return $text;
    }
}
