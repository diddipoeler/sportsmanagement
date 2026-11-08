<?php
/**
 * Joomla 5/6 project relation lookup service.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

final class ProjectRelationService
{
    /**
     * Construct a project relation reader using Joomla's database service.
     * Stores the injected database dependency shared by all project, team, division, position and playground lookups.
     *
     * @param DatabaseInterface $db Database driver supplied by Joomla's dependency container.
     * @return void
     */
    public function __construct(private DatabaseInterface $db)
    {
    }

    /**
     * Load one project's identity, season, league and sport-related settings.
     * Joins the project to optional league and season records and returns the selected relation data; nonpositive project IDs return no row.
     *
     * @param int $projectId Identifier of the requested project.
     * @return object|null Project attributes with season and league data, or null.
     */
    public function getProject(int $projectId): ?object
    {
        if ($projectId <= 0) {
            return null;
        }

        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('p.id'),
                $this->db->quoteName('p.name'),
                $this->db->quoteName('p.season_id'),
                $this->db->quoteName('p.league_id'),
                $this->db->quoteName('p.project_art_id'),
                $this->db->quoteName('p.project_type'),
                $this->db->quoteName('p.sports_type_id'),
                $this->db->quoteName('p.fast_projektteam'),
                $this->db->quoteName('l.country'),
                $this->db->quoteName('s.name', 'season_name'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_project', 'p'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_league', 'l') . ' ON ' . $this->db->quoteName('l.id') . ' = ' . $this->db->quoteName('p.league_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_season', 's') . ' ON ' . $this->db->quoteName('s.id') . ' = ' . $this->db->quoteName('p.season_id'))
            ->where($this->db->quoteName('p.id') . ' = ' . $projectId);
        $this->db->setQuery($query, 0, 1);

        return $this->db->loadObject() ?: null;
    }

    /**
     * Load a team associated with a particular project.
     * Restricts the project-team identifier to the expected parent project and joins season-team and team details to avoid returning teams from unrelated projects.
     *
     * @param int $projectTeamId Identifier of the requested project-team relation.
     * @param int $projectId Expected parent project identifier.
     * @return object|null Related team information, or null when the relation is missing.
     */
    public function getProjectTeam(int $projectTeamId, int $projectId): ?object
    {
        if ($projectTeamId <= 0 || $projectId <= 0) {
            return null;
        }

        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('pt.id', 'project_team_id'),
                $this->db->quoteName('pt.project_id'),
                $this->db->quoteName('pt.team_id', 'season_team_id'),
                $this->db->quoteName('st.team_id'),
                $this->db->quoteName('st.season_id'),
                $this->db->quoteName('t.name', 'team_name'),
                $this->db->quoteName('t.club_id'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $this->db->quoteName('st.id') . ' = ' . $this->db->quoteName('pt.team_id'))
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $this->db->quoteName('t.id') . ' = ' . $this->db->quoteName('st.team_id'))
            ->where($this->db->quoteName('pt.id') . ' = ' . $projectTeamId)
            ->where($this->db->quoteName('pt.project_id') . ' = ' . $projectId);
        $this->db->setQuery($query, 0, 1);

        return $this->db->loadObject() ?: null;
    }

    /**
     * List the published positions available to a project's person type.
     * Joins project positions to position definitions, filters by person type and publication state, and sorts by display name for Joomla form options.
     *
     * @param int $projectId Project identifier.
     * @param int $personType Required person type, such as player or referee.
     * @return array<int,object> Position option rows with value and text fields.
     */
    public function getProjectPositions(int $projectId, int $personType): array
    {
        if ($projectId <= 0 || $personType <= 0) {
            return [];
        }

        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('pp.id', 'value'),
                $this->db->quoteName('pos.name', 'text'),
                $this->db->quoteName('pos.id', 'position_id'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_project_position', 'pp'))
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $this->db->quoteName('pos.id') . ' = ' . $this->db->quoteName('pp.position_id'))
            ->where($this->db->quoteName('pp.project_id') . ' = ' . $projectId)
            ->where($this->db->quoteName('pos.persontype') . ' = ' . $personType)
            ->where($this->db->quoteName('pp.published') . ' = 1')
            ->order($this->db->quoteName('pos.name') . ' ASC');
        $this->db->setQuery($query);

        return $this->db->loadObjectList() ?: [];
    }

    /**
     * Find a position association that belongs to the expected project and person type.
     * Ensures that submitted position IDs cannot be resolved from another project or from a different person category.
     *
     * @param int $projectPositionId Project-position relation identifier.
     * @param int $projectId Project that must own the position.
     * @param int $personType Person category the position must represent.
     * @return object|null Project-position relation, or null if it does not match.
     */
    public function getProjectPosition(int $projectPositionId, int $projectId, int $personType): ?object
    {
        if ($projectPositionId <= 0 || $projectId <= 0 || $personType <= 0) {
            return null;
        }

        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('pp.id'),
                $this->db->quoteName('pp.position_id'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_project_position', 'pp'))
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $this->db->quoteName('pos.id') . ' = ' . $this->db->quoteName('pp.position_id'))
            ->where($this->db->quoteName('pp.id') . ' = ' . $projectPositionId)
            ->where($this->db->quoteName('pp.project_id') . ' = ' . $projectId)
            ->where($this->db->quoteName('pos.persontype') . ' = ' . $personType);
        $this->db->setQuery($query, 0, 1);

        return $this->db->loadObject() ?: null;
    }

    /**
     * Retrieve the divisions configured for one project.
     * Builds name-sorted value/text options for the administrator division selector without querying for invalid project identifiers.
     *
     * @param int $projectId Parent project identifier.
     * @return array<int,object> Division records formatted as select options.
     */
    public function getDivisions(int $projectId): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('id', 'value'),
                $this->db->quoteName('name', 'text'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_division'))
            ->where($this->db->quoteName('project_id') . ' = ' . $projectId)
            ->order($this->db->quoteName('name') . ' ASC');
        $this->db->setQuery($query);

        return $this->db->loadObjectList() ?: [];
    }

    /**
     * Validate a selected division against its parent project.
     * Allows division ID zero as the unassigned option; otherwise it checks that the division exists under the specified project.
     *
     * @param int $divisionId Selected division identifier, or zero for no division.
     * @param int $projectId Expected owning project identifier.
     * @return bool True for an unassigned division or a matching project relation.
     */
    public function divisionBelongsToProject(int $divisionId, int $projectId): bool
    {
        if ($divisionId === 0) {
            return true;
        }

        $query = $this->db->createQuery()
            ->select('1')
            ->from($this->db->quoteName('#__sportsmanagement_division'))
            ->where($this->db->quoteName('id') . ' = ' . $divisionId)
            ->where($this->db->quoteName('project_id') . ' = ' . $projectId);
        $this->db->setQuery($query, 0, 1);

        return (bool) $this->db->loadResult();
    }

    /**
     * List published playgrounds available to the administrator.
     * Loads globally published venue records ordered by name and formats them as value/text selections.
     *
     * @return array<int,object> Published playground option records.
     */
    public function getPlaygrounds(): array
    {
        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('id', 'value'),
                $this->db->quoteName('name', 'text'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_playground'))
            ->where($this->db->quoteName('published') . ' = 1')
            ->order($this->db->quoteName('name') . ' ASC');
        $this->db->setQuery($query);

        return $this->db->loadObjectList() ?: [];
    }

    /**
     * Check whether a selected playground record exists.
     * Accepts a zero identifier as the legacy unassigned venue option and otherwise verifies that the referenced record is present.
     *
     * @param int $playgroundId Selected playground identifier, or zero for none.
     * @return bool Whether the selection refers to an existing or unassigned venue.
     */
    public function playgroundExists(int $playgroundId): bool
    {
        if ($playgroundId === 0) {
            return true;
        }

        $query = $this->db->createQuery()
            ->select('1')
            ->from($this->db->quoteName('#__sportsmanagement_playground'))
            ->where($this->db->quoteName('id') . ' = ' . $playgroundId);
        $this->db->setQuery($query, 0, 1);

        return (bool) $this->db->loadResult();
    }
}
