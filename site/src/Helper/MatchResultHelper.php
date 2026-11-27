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

use Joomla\CMS\Language\Text;

/**
 * Resolve and render a match result from one project team's perspective.
 */
final class MatchResultHelper
{
    /**
     * Determine the match outcome from the selected project team's perspective.
     *
     * Rejects cancelled matches and missing scores, handles alternate
     * decision scores where present and compares the home/away values
     * against the selected team's home or away position. Project-team IDs
     * must be complete positive decimal values: malformed imported strings,
     * arrays, booleans and integer overflows must never cast to a related
     * team. Unrelated teams, nonnumeric results and nonfinite scores have
     * no known outcome. Both participants must have valid identifiers.
     * Rejects records where the requested team occupies
     * both home and away slots rather than inventing a result.
     *
     * @param object $game Match row with results and project team IDs.
     * @param int $projectTeamId Project team whose result is requested.
     * @return int|false 1 for a win, 0 for a draw, -1 for a loss, false if unknown.
     */
    public static function outcome(object $game, int $projectTeamId): int|false
    {
        if ($projectTeamId <= 0 || !empty($game->cancel)) {
            return false;
        }

        $useDecision = !empty($game->alt_decision);
        $homeProperty = $useDecision ? 'team1_result_decision' : 'team1_result';
        $awayProperty = $useDecision ? 'team2_result_decision' : 'team2_result';

        $isHomeTeam = self::projectTeamId($game->projectteam1_id ?? null) === $projectTeamId;
        $isAwayTeam = self::projectTeamId($game->projectteam2_id ?? null) === $projectTeamId;

        // A corrupted fixture containing the same project team on both
        // sides has no unambiguous home/away result for that team.
        // A third-party team must not receive a draw for an unrelated match.
        if (self::projectTeamId($game->projectteam1_id ?? null) === 0
            || self::projectTeamId($game->projectteam2_id ?? null) === 0
            || (!$isHomeTeam && !$isAwayTeam) || ($isHomeTeam && $isAwayTeam)) {
            return false;
        }

        if (!isset($game->{$homeProperty}, $game->{$awayProperty})
            || !is_numeric($game->{$homeProperty}) || !is_numeric($game->{$awayProperty})) {
            return false;
        }

        $home = (float) $game->{$homeProperty};
        $away = (float) $game->{$awayProperty};

        // Infinity and NaN are not valid scores and cannot be compared reliably.
        if (!is_finite($home) || !is_finite($away)) {
            return false;
        }

        if ($home === $away) {
            return 0;
        }

        return $isHomeTeam
            ? ($home > $away ? 1 : -1)
            : ($away > $home ? 1 : -1);
    }

    /**
     * Parse one complete, positive project-team identifier from an import.
     *
     * Retains historical zero-padded decimal strings, but rejects floats,
     * booleans, arrays, negative and partially numeric values. Bounds large
     * input before conversion so malformed project-team IDs cannot match a
     * different team or trigger PHP 8 array-to-integer warnings.
     *
     * @param mixed $value Raw match project-team identifier.
     * @return int Valid project-team ID, or zero when invalid.
     */
    private static function projectTeamId(mixed $value): int
    {
        if (is_int($value)) {
            return $value > 0 ? $value : 0;
        }

        if (!is_string($value) || $value === '' || strlen($value) > 128
            || preg_match('/^[0-9]+$/D', $value) !== 1) {
            return 0;
        }

        $digits = ltrim($value, '0');

        if ($digits === '') {
            return 0;
        }

        $parsed = filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $parsed === false ? 0 : $parsed;
    }

    /**
     * Render the win, draw or loss icon for a project team.
     *
     * Uses outcome() to select a legacy icon and CSS class, escapes the
     * translated tooltip label for HTML attributes and returns an empty
     * string when the result cannot be determined.
     *
     * @param object $game Match record containing score information.
     * @param int $projectTeamId Team to display the result for.
     * @return string Safe HTML icon markup, or an empty string.
     */
    public static function renderOutcomeIcon(object $game, int $projectTeamId): string
    {
        $outcome = self::outcome($game, $projectTeamId);

        if ($outcome === false) {
            return '';
        }

        [$icon, $class, $label] = match ($outcome) {
            -1 => ['fa-thumbs-down', 'lost', Text::_('COM_SPORTSMANAGEMENT_LOST')],
            0 => ['fa-handshake-o', 'draw', Text::_('COM_SPORTSMANAGEMENT_DRAW')],
            1 => ['fa-thumbs-up', 'won', Text::_('COM_SPORTSMANAGEMENT_WON')],
        };

        $title = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');

        return '<span class="fa-stack fa-xs ' . $class . '">'
            . '<i class="fa fa-square fa-stack-2x"></i>'
            . '<i class="fa ' . $icon . ' fa-stack-1x fa-inverse" title="' . $title . '"></i>'
            . '</span>';
    }
}
