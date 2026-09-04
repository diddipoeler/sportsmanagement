<?php
/**
 * Joomla 5/6 view-facing facade for administrator individual-match data.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/** View-facing facade for administrator individual-match data. */
final class IndividualMatchViewService
{
    public function __construct(private DatabaseInterface $db)
    {
    }

    public function getProject(int $projectId): ?object
    {
        if ($projectId <= 0) {
            return null;
        }

        $db = $this->db;
        $query = $db->createQuery()
            ->select([
                $db->quoteName('p') . '.*',
                $db->quoteName('st.name', 'sports_type_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_sports_type', 'st') . ' ON st.id = p.sports_type_id')
            ->where($db->quoteName('p.id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);

        return $db->setQuery($query, 0, 1)->loadObject() ?: null;
    }

    public function getRound(int $roundId): ?object
    {
        if ($roundId <= 0) {
            return null;
        }

        $db = $this->db;
        $query = $db->createQuery()
            ->select($db->quoteName('r') . '.*')
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->where($db->quoteName('r.id') . ' = :roundId')
            ->bind(':roundId', $roundId, ParameterType::INTEGER);

        return $db->setQuery($query, 0, 1)->loadObject() ?: null;
    }

    public function getSingleMatches(int $matchId): array
    {
        if ($matchId <= 0) {
            return [];
        }

        $db = $this->db;
        $query = $db->createQuery()
            ->select($db->quoteName('ms') . '.*')
            ->from($db->quoteName('#__sportsmanagement_match_single', 'ms'))
            ->where($db->quoteName('ms.match_id') . ' = :matchId')
            ->order($db->quoteName('ms.id') . ' ASC')
            ->bind(':matchId', $matchId, ParameterType::INTEGER);

        return $db->setQuery($query)->loadObjectList() ?: [];
    }

    public function getProjectTeamPlayers(int $seasonId, int $projectTeamId): array
    {
        return (new IndividualMatchRosterReadService($this->db))->getProjectTeamPlayers($seasonId, $projectTeamId);
    }

    public function getMatchRosterPlayers(int $seasonId, int $projectTeamId, int $matchId): array
    {
        return (new IndividualMatchRosterReadService($this->db))->getMatchRosterPlayers($seasonId, $projectTeamId, $matchId);
    }

    public function ensureGolfBillardSingles(
        int $matchId,
        int $roundId,
        int $homeTeamId,
        int $awayTeamId,
        int $userId,
        string $modified
    ): bool {
        return (new IndividualMatchGolfSetupService($this->db))->ensureSingles(
            $matchId,
            $roundId,
            $homeTeamId,
            $awayTeamId,
            $userId,
            $modified
        );
    }
}
