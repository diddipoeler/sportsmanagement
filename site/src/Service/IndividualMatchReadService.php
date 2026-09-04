<?php
/**
 * Read-only access to individual-match rows for frontend roster statistics.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

final class IndividualMatchReadService
{
    public function __construct(private DatabaseInterface $db)
    {
    }

    /** @return array<int,object> */
    public function getPlayerMatches(
        int $projectId,
        int $projectTeamId,
        int $seasonTeamPersonId,
        string $matchType = 'SINGLE',
        string $homeAway = 'HOME'
    ): array {
        if ($projectId <= 0 || $projectTeamId <= 0 || $seasonTeamPersonId <= 0) {
            return [];
        }

        $side = strtoupper($homeAway) === 'AWAY' ? 2 : 1;
        $db = $this->db;
        $query = $db->createQuery()
            ->select($db->quoteName('ms') . '.*')
            ->from($db->quoteName('#__sportsmanagement_match_single', 'ms'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('ms.round_id'))
            ->where($db->quoteName('r.project_id') . ' = :projectId')
            ->where($db->quoteName('ms.projectteam' . $side . '_id') . ' = :projectTeamId')
            ->where($db->quoteName('ms.teamplayer' . $side . '_id') . ' = :seasonTeamPersonId')
            ->where($db->quoteName('ms.match_type') . ' = :matchType')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->bind(':projectTeamId', $projectTeamId, ParameterType::INTEGER)
            ->bind(':seasonTeamPersonId', $seasonTeamPersonId, ParameterType::INTEGER)
            ->bind(':matchType', $matchType, ParameterType::STRING);
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }
}
