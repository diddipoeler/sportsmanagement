<?php
/**
 * Joomla 5/6 read service for administrator individual-match rosters.
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

/** Read-only roster access for the administrator individual-match view. */
final class IndividualMatchRosterReadService
{
    /**
     * Create the administrator individual-match roster reader.
     * Keeps the injected Joomla database service for project-team player lists and match-specific rosters.
     *
     * @param DatabaseInterface $db Joomla database driver.
     * @return void
     */
    public function __construct(private DatabaseInterface $db)
    {
    }

    /**
     * Load published players belonging to a project team in the requested season.
     * Joins person records to season-team-person and project-team relations, filters out unpublished people and orders the result by last name.
     *
     * @param int $seasonId Season containing the player registrations.
     * @param int $projectTeamId Project-team identifier.
     * @return array<int,object> Active person records with season-team-person IDs.
     */
    public function getProjectTeamPlayers(int $seasonId, int $projectTeamId): array
    {
        if ($seasonId <= 0 || $projectTeamId <= 0) {
            return [];
        }

        $db = $this->db;
        $query = $db->createQuery()
            ->select([
                $db->quoteName('p') . '.*',
                $db->quoteName('tp.id', 'season_team_person_id'),
            ])
            ->from($db->quoteName('#__sportsmanagement_person', 'p'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp') . ' ON tp.person_id = p.id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON st.team_id = tp.team_id AND st.season_id = tp.season_id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON pt.team_id = st.id')
            ->where($db->quoteName('pt.id') . ' = :projectTeamId')
            ->where($db->quoteName('tp.season_id') . ' = :seasonId')
            ->where($db->quoteName('tp.persontype') . ' = 1')
            ->where($db->quoteName('p.published') . ' = 1')
            ->order($db->quoteName('p.lastname') . ' ASC')
            ->bind(':projectTeamId', $projectTeamId, ParameterType::INTEGER)
            ->bind(':seasonId', $seasonId, ParameterType::INTEGER);

        return $db->setQuery($query)->loadObjectList() ?: [];
    }

    /**
     * Find selectable team members while excluding previously selected roster IDs.
     * Restricts the people query to project, team, season and person type, joins optional project positions and excludes only validated positive person identifiers.
     *
     * @param int $projectId Owning project identifier.
     * @param int $seasonId Season of the nominated team.
     * @param int $projectTeamId Project-team identifier.
     * @param int $personType Required person category, normally player.
     * @param array<int,mixed> $excludedIds Existing season-team-person IDs to omit.
     * @return array<int,object> Available people and their position labels.
     */
    public function getAvailableTeamPersons(
        int $projectId,
        int $seasonId,
        int $projectTeamId,
        int $personType = 1,
        array $excludedIds = []
    ): array {
        if ($projectId <= 0 || $seasonId <= 0 || $projectTeamId <= 0 || $personType <= 0) {
            return [];
        }

        $excludedIds = self::normaliseExcludedIds($excludedIds);

        $db = $this->db;
        $query = $db->createQuery()
            ->select([
                $db->quoteName('sp.id', 'value'),
                "CONCAT(" . $db->quoteName('pl.firstname') . ", ' - ', " . $db->quoteName('pl.nickname') . ", ' - ', " . $db->quoteName('pl.lastname') . ") AS " . $db->quoteName('text'),
                $db->quoteName('pl.firstname'),
                $db->quoteName('pl.nickname'),
                $db->quoteName('pl.lastname'),
                $db->quoteName('pl.info'),
                $db->quoteName('sp.jerseynumber'),
                $db->quoteName('pl.ordering'),
                $db->quoteName('pl.knvbnr'),
                $db->quoteName('pos.name', 'positionname'),
            ])
            ->from($db->quoteName('#__sportsmanagement_person', 'pl'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_person_id', 'sp')
                . ' ON ' . $db->quoteName('sp.person_id') . ' = ' . $db->quoteName('pl.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('sp.team_id')
                . ' AND ' . $db->quoteName('st.season_id') . ' = ' . $db->quoteName('sp.season_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_person_project_position', 'ppp')
                . ' ON ' . $db->quoteName('ppp.person_id') . ' = ' . $db->quoteName('sp.person_id')
                . ' AND ' . $db->quoteName('ppp.persontype') . ' = :personType'
                . ' AND ' . $db->quoteName('ppp.project_id') . ' = :projectId'
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project_position', 'ppos')
                . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('ppp.project_position_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_position', 'pos')
                . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id')
            )
            ->where($db->quoteName('pt.id') . ' = :projectTeamId')
            ->where($db->quoteName('pl.published') . ' = 1')
            ->where($db->quoteName('sp.persontype') . ' = :personType')
            ->where($db->quoteName('sp.season_id') . ' = :seasonId')
            ->order($db->quoteName('pl.lastname') . ' ASC')
            ->bind(':projectTeamId', $projectTeamId, ParameterType::INTEGER)
            ->bind(':personType', $personType, ParameterType::INTEGER)
            ->bind(':seasonId', $seasonId, ParameterType::INTEGER)
            ->bind(':projectId', $projectId, ParameterType::INTEGER);

        if ($excludedIds) {
            $query->where($db->quoteName('sp.id') . ' NOT IN (' . implode(',', $excludedIds) . ')');
        }

        return $db->setQuery($query)->loadObjectList() ?: [];
    }

    /**
     * Validate excluded person IDs before adding them to a SQL NOT IN list.
     * Accepts positive integer and decimal-string IDs, deduplicates them and rejects arrays, booleans, partial numbers and integer overflows instead of coercing them into unrelated person IDs.
     *
     * @param array<int,mixed> $excludedIds User-supplied excluded person identifiers.
     * @return array<int,int> Unique valid positive season-team-person IDs.
     */
    private static function normaliseExcludedIds(array $excludedIds): array
    {
        $valid = [];

        foreach ($excludedIds as $candidate) {
            if (!is_int($candidate) && !is_string($candidate)) {
                continue;
            }

            $raw = trim((string) $candidate);
            if ($raw === '' || preg_match('/^[0-9]+$/D', $raw) !== 1) {
                continue;
            }

            $digits = ltrim($raw, '0');
            if ($digits === '') {
                continue;
            }

            $id = filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id !== false) {
                $valid[$id] = $id;
            }
        }

        return array_values($valid);
    }

    /**
     * List roster entries already nominated for a particular match and team.
     * Joins match players to their project-team registrations and position names, retaining starting and substituted players in ordering sequence.
     *
     * @param int $seasonId Season containing the nominated people.
     * @param int $projectTeamId Project-team identifier.
     * @param int $matchId Parent fixture identifier.
     * @return array<int,object> Match roster rows with person and position references.
     */
    public function getMatchRosterPlayers(int $seasonId, int $projectTeamId, int $matchId): array
    {
        if ($seasonId <= 0 || $projectTeamId <= 0 || $matchId <= 0) {
            return [];
        }

        $db = $this->db;
        $query = $db->createQuery()
            ->select([
                $db->quoteName('mp.teamplayer_id'),
                $db->quoteName('mp.project_position_id'),
                $db->quoteName('pos.name', 'project_position_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match_player', 'mp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp') . ' ON tp.id = mp.teamplayer_id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON st.team_id = tp.team_id AND st.season_id = tp.season_id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON pt.team_id = st.id')
            ->join('INNER', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON pos.id = mp.project_position_id')
            ->where($db->quoteName('pt.id') . ' = :projectTeamId')
            ->where($db->quoteName('tp.season_id') . ' = :seasonId')
            ->where($db->quoteName('mp.match_id') . ' = :matchId')
            ->where($db->quoteName('mp.came_in') . ' IN (0,1)')
            ->order($db->quoteName('mp.ordering') . ' ASC')
            ->bind(':projectTeamId', $projectTeamId, ParameterType::INTEGER)
            ->bind(':seasonId', $seasonId, ParameterType::INTEGER)
            ->bind(':matchId', $matchId, ParameterType::INTEGER);

        return $db->setQuery($query)->loadObjectList() ?: [];
    }
}
