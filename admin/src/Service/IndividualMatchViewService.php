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
    /**
     * Create a read-only facade for the administrator's individual-match views.
     * Provides an immutable database dependency to project, round, roster and single-match lookups.
     *
     * @param DatabaseInterface $db Joomla database driver.
     * @return void
     */
    public function __construct(private DatabaseInterface $db)
    {
    }

    /**
     * Load the project and sport type for an individual-match fixture.
     * Checks the project ID before executing a bound, limited Joomla query so nonexistent projects produce a null result.
     *
     * @param int $projectId Project record identifier.
     * @return object|null Project and sport type details, or null.
     */
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

    /**
     * Read the round record for an individual-match fixture.
     * Fetches a single row with a bound integer identifier and avoids a query for empty or invalid round IDs.
     *
     * @param int $roundId Round record identifier.
     * @return object|null Round record or null when not found.
     */
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

    /**
     * List the individual games belonging to a parent match.
     * Uses a bound parent-match identifier and sorts the returned single-match records by their numeric IDs.
     *
     * @param int $matchId Parent match identifier.
     * @return array<int,object> Ordered single-match records.
     */
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

    /**
     * Retrieve eligible season players for a project team.
     * Delegates roster filtering to the dedicated native read service instead of duplicating joins in the view facade.
     *
     * @param int $seasonId Active season identifier.
     * @param int $projectTeamId Project-team identifier.
     * @return array<int,object> Team roster entries.
     */
    public function getProjectTeamPlayers(int $seasonId, int $projectTeamId): array
    {
        return (new IndividualMatchRosterReadService($this->db))->getProjectTeamPlayers($seasonId, $projectTeamId);
    }

    /**
     * Find players selectable for a particular individual match.
     * Delegates to the native roster reader and excludes already selected person IDs so administrator dropdowns do not offer duplicate participants.
     *
     * @param int $projectId Current project identifier.
     * @param int $seasonId Active season identifier.
     * @param int $projectTeamId Team within the current project.
     * @param int $personType Requested person type; defaults to players.
     * @param array<int,int|string> $excludedIds Selected IDs to omit.
     * @return array<int,object> Eligible people.
     */
    public function getAvailableTeamPersons(
        int $projectId,
        int $seasonId,
        int $projectTeamId,
        int $personType = 1,
        array $excludedIds = []
    ): array {
        return (new IndividualMatchRosterReadService($this->db))->getAvailableTeamPersons(
            $projectId,
            $seasonId,
            $projectTeamId,
            $personType,
            $excludedIds
        );
    }

    /**
     * Retrieve roster members already associated with a match.
     * Uses the native roster reader to keep match-specific participant selection consistent with other administrator views.
     *
     * @param int $seasonId Active season identifier.
     * @param int $projectTeamId Project-team identifier.
     * @param int $matchId Parent match identifier.
     * @return array<int,object> Matching roster entries.
     */
    public function getMatchRosterPlayers(int $seasonId, int $projectTeamId, int $matchId): array
    {
        return (new IndividualMatchRosterReadService($this->db))->getMatchRosterPlayers($seasonId, $projectTeamId, $matchId);
    }

    /**
     * Ensure the required individual games exist for golf or billiards fixtures.
     * Delegates specialised single-match generation to the golf setup service and preserves the caller's user and modification metadata.
     *
     * @param int $matchId Parent match identifier.
     * @param int $roundId Round identifier.
     * @param int $homeTeamId Home project-team identifier.
     * @param int $awayTeamId Away project-team identifier.
     * @param int $userId Administrator who triggered generation.
     * @param string $modified SQL timestamp for the generated records.
     * @return bool Whether the specialised setup succeeded.
     */
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
