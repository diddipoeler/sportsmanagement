<?php
/**
 * Native Joomla 5/6 project service for Inline Hockey imports.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Resolve Inline Hockey project metadata and keep team/project assignments native.
 */
final class InlineHockeyProjectService
{
    /**
     * Initialise the Inline Hockey project data service.
     * Uses the injected Joomla database for project metadata and season/project team association queries.
     *
     * @param DatabaseInterface $db Database connection supplied by Joomla.
     * @return void
     */
    public function __construct(private readonly DatabaseInterface $db)
    {
    }

    /**
     * Read the external Inline Hockey project link stored in a configured extra field.
     * Restricts the lookup to the project backend template and link field type, using bound values for project and field identifiers.
     *
     * @param int $projectId Project whose external link is requested.
     * @param string $fieldName Name of the project link extra field.
     * @return string Stored external project link, or an empty string.
     */
    public function getMatchLink(int $projectId, string $fieldName = 'jsminlinehockey'): string
    {
        if ($projectId <= 0) {
            return '';
        }

        $fieldName = trim($fieldName) !== '' ? trim($fieldName) : 'jsminlinehockey';
        $backend = 'project';
        $fieldType = 'link';
        $query = $this->db->createQuery()
            ->select($this->db->quoteName('ev.fieldvalue'))
            ->from($this->db->quoteName('#__sportsmanagement_user_extra_fields_values', 'ev'))
            ->join(
                'INNER',
                $this->db->quoteName('#__sportsmanagement_user_extra_fields', 'ef')
                . ' ON ' . $this->db->quoteName('ef.id') . ' = ' . $this->db->quoteName('ev.field_id')
            )
            ->where($this->db->quoteName('ev.jl_id') . ' = :projectId')
            ->where($this->db->quoteName('ef.name') . ' = :fieldName')
            ->where($this->db->quoteName('ef.template_backend') . ' = :backend')
            ->where($this->db->quoteName('ef.field_type') . ' = :fieldType')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->bind(':fieldName', $fieldName, ParameterType::STRING)
            ->bind(':backend', $backend, ParameterType::STRING)
            ->bind(':fieldType', $fieldType, ParameterType::STRING);

        $this->db->setQuery($query, 0, 1);

        return trim((string) $this->db->loadResult());
    }

    /**
     * Ensure a team has the required season and project association rows.
     * Reuses an existing season-team relation or inserts it, then finds or creates the matching project-team row without intentionally duplicating an association.
     *
     * @param int $teamId Underlying team record identifier.
     * @param int $projectId Owning project identifier.
     * @param int $seasonId Season into which the team should be linked.
     * @return int Project-team relation ID, or zero when required IDs are invalid.
     */
    public function ensureProjectTeam(int $teamId, int $projectId, int $seasonId): int
    {
        if ($teamId <= 0 || $projectId <= 0 || $seasonId <= 0) {
            return 0;
        }

        $seasonTeamId = $this->findSeasonTeam($teamId, $seasonId);

        if ($seasonTeamId <= 0) {
            $record = (object) [
                'team_id' => $teamId,
                'season_id' => $seasonId,
            ];
            $this->db->insertObject('#__sportsmanagement_season_team_id', $record);
            $seasonTeamId = (int) $this->db->insertid();
        }

        if ($seasonTeamId <= 0) {
            return 0;
        }

        $projectTeamId = $this->findProjectTeam($seasonTeamId, $projectId);

        if ($projectTeamId > 0) {
            return $projectTeamId;
        }

        $record = (object) [
            'team_id' => $seasonTeamId,
            'project_id' => $projectId,
        ];
        $this->db->insertObject('#__sportsmanagement_project_team', $record);

        return (int) $this->db->insertid();
    }

    /**
     * Look up an existing association between a team and a season.
     * Executes a limited query with bound team and season identifiers to locate the season-team row.
     *
     * @param int $teamId Underlying team identifier.
     * @param int $seasonId Season identifier.
     * @return int Existing season-team record ID, or zero.
     */
    private function findSeasonTeam(int $teamId, int $seasonId): int
    {
        $query = $this->db->createQuery()
            ->select($this->db->quoteName('id'))
            ->from($this->db->quoteName('#__sportsmanagement_season_team_id'))
            ->where($this->db->quoteName('team_id') . ' = :teamId')
            ->where($this->db->quoteName('season_id') . ' = :seasonId')
            ->bind(':teamId', $teamId, ParameterType::INTEGER)
            ->bind(':seasonId', $seasonId, ParameterType::INTEGER);
        $this->db->setQuery($query, 0, 1);

        return (int) ($this->db->loadResult() ?: 0);
    }

    /**
     * Find a project-team relation for a particular season-team row.
     * Uses project and season-team identifiers as bound query parameters, ensuring an association is scoped to the expected project.
     *
     * @param int $seasonTeamId Season-team relation identifier.
     * @param int $projectId Owning project identifier.
     * @return int Matching project-team record ID, or zero.
     */
    private function findProjectTeam(int $seasonTeamId, int $projectId): int
    {
        $query = $this->db->createQuery()
            ->select($this->db->quoteName('id'))
            ->from($this->db->quoteName('#__sportsmanagement_project_team'))
            ->where($this->db->quoteName('team_id') . ' = :seasonTeamId')
            ->where($this->db->quoteName('project_id') . ' = :projectId')
            ->bind(':seasonTeamId', $seasonTeamId, ParameterType::INTEGER)
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $this->db->setQuery($query, 0, 1);

        return (int) ($this->db->loadResult() ?: 0);
    }
}
