<?php
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Resolve and render a match result from one project team's perspective.
 */
final class MatchResultHelper
{
    public static function outcome(object $game, int $projectTeamId): int|false
    {
        if ($projectTeamId <= 0 || !empty($game->cancel)) {
            return false;
        }

        $useDecision = !empty($game->alt_decision);
        $homeProperty = $useDecision ? 'team1_result_decision' : 'team1_result';
        $awayProperty = $useDecision ? 'team2_result_decision' : 'team2_result';

        if (!isset($game->{$homeProperty}, $game->{$awayProperty})) {
            return false;
        }

        $home = (float) $game->{$homeProperty};
        $away = (float) $game->{$awayProperty};

        if ($home === $away) {
            return 0;
        }

        if ((int) ($game->projectteam1_id ?? 0) === $projectTeamId) {
            return $home > $away ? 1 : -1;
        }

        if ((int) ($game->projectteam2_id ?? 0) === $projectTeamId) {
            return $away > $home ? 1 : -1;
        }

        return false;
    }

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
