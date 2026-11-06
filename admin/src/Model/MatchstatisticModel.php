<?php
/**
 * Native Joomla 5/6 administrator model for match statistics.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\MatchstatisticTable;
use Joomla\Database\ParameterType;

final class MatchstatisticModel extends SportsManagementAdminModel
{
    /**
     * Read player statistics recorded for the selected match.
     *
     * Loads match-statistic rows using a bound integer match ID and groups
     * values by project team, team player and statistic identifier.
     * Returns the team-keyed empty structure when no match is selected
     * or the database query fails.
     *
     * @param int $matchId Match identifier.
     * @param int $projectTeam1Id First project team ID.
     * @param int $projectTeam2Id Second project team ID.
     * @return array<int,array<int,array<int,mixed>>> Match player statistics.
     */
    public function getMatchStatsInput($matchId = 0, $projectTeam1Id = 0, $projectTeam2Id = 0): array
    {
        $matchId = (int) $matchId;
        $projectTeam1Id = (int) $projectTeam1Id;
        $projectTeam2Id = (int) $projectTeam2Id;
        $stats = [
            $projectTeam1Id => [],
            $projectTeam2Id => [],
        ];

        if ($matchId <= 0) {
            return $stats;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('projectteam_id'),
                $db->quoteName('teamplayer_id'),
                $db->quoteName('statistic_id'),
                $db->quoteName('value'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match_statistic'))
            ->where($db->quoteName('match_id') . ' = :matchId')
            ->bind(':matchId', $matchId, ParameterType::INTEGER);

        try {
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return $stats;
        }

        foreach ($rows as $row) {
            $projectTeamId = (int) ($row->projectteam_id ?? 0);
            $teamPlayerId = (int) ($row->teamplayer_id ?? 0);
            $statisticId = (int) ($row->statistic_id ?? 0);

            if ($projectTeamId <= 0 || $teamPlayerId <= 0 || $statisticId <= 0) {
                continue;
            }

            $stats[$projectTeamId] ??= [];
            $stats[$projectTeamId][$teamPlayerId] ??= [];
            $stats[$projectTeamId][$teamPlayerId][$statisticId] = $row->value;
        }

        return $stats;
    }

    /**
     * Resolve the native match-statistic table through Joomla MVC.
     * @param string $type Requested table name.
     * @param string $prefix Legacy table namespace.
     * @param array $config Optional Joomla table factory settings.
     * @return mixed MatchstatisticTable or parent Joomla table.
     */
    public function getTable($type = 'matchstatistic', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'matchstatistic') === 0) {
            return new MatchstatisticTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }
}
