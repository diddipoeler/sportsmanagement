<?php
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Format match times without loading the legacy HTML helper.
 */
final class MatchTimeHelper
{
    public static function format(
        object $game,
        array $config,
        array $overallConfig,
        ?object $project = null
    ): string {
        $timeFormat = (string) ($overallConfig['time_format'] ?? 'H:i');
        $timeSuffix = Text::_('COM_SPORTSMANAGEMENT_GLOBAL_CLOCK');

        if ($timeSuffix === 'COM_SPORTSMANAGEMENT_GLOBAL_CLOCK') {
            $timeSuffix = '%1$s&nbsp;h';
        }

        $timestamp = strtotime((string) ($game->match_date ?? ''));
        if ($timestamp === false) {
            $matchTime = '--&nbsp;:&nbsp;--';

            return !empty($config['show_time_suffix'])
                ? sprintf($timeSuffix, $matchTime)
                : $matchTime;
        }

        $matchTime = HTMLHelper::date((string) $game->match_date, $timeFormat, 'UTC');
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

        $matchStamp = strtotime((string) $game->match_date);
        if ($matchStamp === false || $now < $matchStamp || $now > $matchStamp + ($elapsedMinutes * 60)) {
            return $output;
        }

        $startText = $output . ' ';
        $title = str_replace(
            '%STARTTIME%',
            $startText,
            trim(htmlspecialchars((string) ($config['mark_now_playing_alt_text'] ?? ''), ENT_QUOTES, 'UTF-8'))
        );
        $title = str_replace(
            '%ACTUALTIME%',
            self::actualTime($now, $matchStamp, $config, $project),
            $title
        );
        $style = !empty($config['mark_now_playing_blink'])
            ? ' style="text-decoration:blink"'
            : '';
        $text = Text::_((string) ($config['mark_now_playing_text'] ?? ''));

        return '<b><i><acronym title="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '"'
            . $style . '>' . $text . '</acronym></i></b>';
    }

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
                    trim(htmlspecialchars(
                        (string) ($config['mark_now_playing_alt_actual_time'] ?? ''),
                        ENT_QUOTES,
                        'UTF-8'
                    ))
                );
                break;
            }

            if ($goneSinceBegin > $partEnd && $goneSinceBegin < $nextPartStart) {
                $text = str_replace(
                    '%PART%',
                    (string) $part,
                    trim(htmlspecialchars(
                        (string) ($config['mark_now_playing_alt_actual_break'] ?? ''),
                        ENT_QUOTES,
                        'UTF-8'
                    ))
                );
                break;
            }
        }

        return $text;
    }
}
