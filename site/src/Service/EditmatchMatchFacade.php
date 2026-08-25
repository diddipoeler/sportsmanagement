<?php
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

/**
 * Narrow compatibility facade for the remaining Golf/Billard edit template.
 *
 * The template still calls sportsmanagementModelMatch::getMatchPersons(); the
 * native Editmatch HtmlView aliases that historical class name to this facade
 * only for the native edit layout.
 */
final class EditmatchMatchFacade
{
    private static ?EditmatchViewDataService $service = null;

    public static function setService(EditmatchViewDataService $service): void
    {
        self::$service = $service;
    }

    public static function getMatchPersons($projectTeamId = 0, $positionId = 0, $matchId = 0, $type = 'player'): array
    {
        if (!self::$service || strtolower((string) $type) !== 'player') {
            return [];
        }

        return self::$service->getMatchPersons((int) $projectTeamId, (int) $matchId);
    }
}
