<?php
/**
 * Native Joomla 5/6 administrator model for match staff statistics.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\MatchstaffstatisticTable;
use Joomla\Database\ParameterType;

final class MatchstaffstatisticModel extends SportsManagementAdminModel
{
    /**
     * Read staff statistics recorded for the selected match.
     *
     * Loads the bound match-statistic rows and groups them by project team,
     * staff relation and statistic identifier, preserving both team keys.
     * Returns empty statistics on database failure.
     *
     * @param int $matchId Match identifier.
     * @param int $projectTeam1Id First project team ID.
     * @param int $projectTeam2Id Second project team ID.
     * @return array<int,array<int,array<int,mixed>>> Match staff statistics.
     */
    public function getMatchStaffStatsInput($matchId = 0, $projectTeam1Id = 0, $projectTeam2Id = 0): array
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
                $db->quoteName('team_staff_id'),
                $db->quoteName('statistic_id'),
                $db->quoteName('value'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match_staff_statistic'))
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
            $teamStaffId = (int) ($row->team_staff_id ?? 0);
            $statisticId = (int) ($row->statistic_id ?? 0);

            if ($projectTeamId <= 0 || $teamStaffId <= 0 || $statisticId <= 0) {
                continue;
            }

            $stats[$projectTeamId] ??= [];
            $stats[$projectTeamId][$teamStaffId] ??= [];
            $stats[$projectTeamId][$teamStaffId][$statisticId] = $row->value;
        }

        return $stats;
    }

    /**
     * Resolve the native match-staff-statistic table.
     * @param string $type Requested table name.
     * @param string $prefix Legacy Joomla table namespace.
     * @param array $config Optional Joomla table settings.
     * @return mixed MatchstaffstatisticTable or parent Joomla table.
     */
    public function getTable($type = 'matchstaffstatistic', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'matchstaffstatistic') === 0) {
            return new MatchstaffstatisticTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }
}
