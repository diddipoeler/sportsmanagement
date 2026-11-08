<?php
/**
 * Joomla 5/6 read-only project list data service.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

/** Read-only data provider for the administrator projects list. */
final class ProjectsViewDataService
{
    /**
     * Create the Joomla administrator's project-list data provider.
     * Retains the database abstraction used by dropdown data and project relationship counters.
     *
     * @param DatabaseInterface $db Joomla database driver.
     * @return void
     */
    public function __construct(private DatabaseInterface $db)
    {
    }

    /**
     * List the configured user extra-field definitions for a backend template.
     * Selects the matching extra-field identifiers and display names, ordered alphabetically, for project forms and list settings.
     *
     * @param string $template Backend template identifier; project by default.
     * @return array<int,object> Extra-field definition records.
     */
    public function getExtraFields(string $template = 'project'): array
    {
        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('id'),
                $this->db->quoteName('name'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_user_extra_fields'))
            ->where($this->db->quoteName('template_backend') . ' = ' . $this->db->quote($template))
            ->order($this->db->quoteName('name') . ' ASC');
        $this->db->setQuery($query);

        return $this->db->loadObjectList() ?: [];
    }

    /**
     * Render the names of a project's populated extra fields for a list column.
     * Looks up project-specific nonempty values and joins corresponding field definitions, then separates their names with HTML line breaks for administrator views.
     *
     * @param int $projectId Project whose assigned custom fields should be shown.
     * @return string HTML-safe field names separated by break tags, or an empty string.
     */
    public function getProjectExtraFieldNames(int $projectId): string
    {
        if ($projectId <= 0) {
            return '';
        }

        $query = $this->db->createQuery()
            ->select($this->db->quoteName('ef.name'))
            ->from($this->db->quoteName('#__sportsmanagement_user_extra_fields_values', 'ev'))
            ->join(
                'INNER',
                $this->db->quoteName('#__sportsmanagement_user_extra_fields', 'ef')
                . ' ON ' . $this->db->quoteName('ef.id') . ' = ' . $this->db->quoteName('ev.field_id')
            )
            ->where($this->db->quoteName('ev.jl_id') . ' = ' . $projectId)
            ->where($this->db->quoteName('ef.template_backend') . ' = ' . $this->db->quote('project'))
            ->where($this->db->quoteName('ev.fieldvalue') . ' <> ' . $this->db->quote(''))
            ->order($this->db->quoteName('ef.name') . ' ASC');
        $this->db->setQuery($query);

        $names = $this->db->loadColumn() ?: [];

        // Field names are administrator-managed strings and must not become
        // executable markup in the project list; only the separator is HTML.
        $safeNames = array_map(
            static fn ($name): string => htmlspecialchars((string) $name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            $names
        );

        return implode('<br>', $safeNames);
    }

    /**
     * Load league choices displayed in administrator project forms.
     * Reuses the shared option reader to return identifiers and names in alphabetical order.
     *
     * @return array<int,object> League option rows with ID, name, value and text.
     */
    public function getLeagues(): array
    {
        return $this->simpleOptions('#__sportsmanagement_league', 'id', 'name', 'name');
    }

    /**
     * Load sport-type choices displayed in administrator project forms.
     * Returns configured sport types as value/text option records through the common Joomla query helper.
     *
     * @return array<int,object> Sorted sport-type options.
     */
    public function getSportsTypes(): array
    {
        return $this->simpleOptions('#__sportsmanagement_sports_type', 'id', 'name', 'name');
    }

    /**
     * Load season choices displayed in administrator project forms.
     * Reads season identifiers and names in name order for use by project editors and filters.
     *
     * @return array<int,object> Sorted season option records.
     */
    public function getSeasons(): array
    {
        return $this->simpleOptions('#__sportsmanagement_season', 'id', 'name', 'name');
    }

    /**
     * Retrieve age-group options for project configuration.
     * Selects age-group identifiers and names using Joomla's query builder and sorts the resulting list alphabetically.
     *
     * @return array<int,object> Age-group value/text option rows.
     */
    public function getAgeGroups(): array
    {
        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('id', 'value'),
                $this->db->quoteName('name', 'text'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_agegroup'))
            ->order($this->db->quoteName('name') . ' ASC');
        $this->db->setQuery($query);

        return $this->db->loadObjectList() ?: [];
    }

    /**
     * Load the base project templates used to create new projects.
     * Restricts source projects to the master-template flag used by the legacy project UI and orders available source names.
     *
     * @return array<int,object> Template project identifiers and labels.
     */
    public function getMasterTemplates(): array
    {
        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('id', 'value'),
                $this->db->quoteName('name', 'text'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_project'))
            ->where($this->db->quoteName('master_template') . ' = 0')
            ->order($this->db->quoteName('name') . ' ASC');
        $this->db->setQuery($query);

        return $this->db->loadObjectList() ?: [];
    }

    /**
     * List associations grouped by country and sorted by name.
     * Includes the country on each association row so the administrator can distinguish identically named associations.
     *
     * @return array<int,object> Association options including their country field.
     */
    public function getAssociations(): array
    {
        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('id', 'value'),
                $this->db->quoteName('name', 'text'),
                $this->db->quoteName('country'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_associations'))
            ->order([
                $this->db->quoteName('country') . ' ASC',
                $this->db->quoteName('name') . ' ASC',
            ]);
        $this->db->setQuery($query);

        return $this->db->loadObjectList() ?: [];
    }

    /**
     * Read the available countries for project configuration.
     * Uses ISO alpha-3 country codes as option values and translated database names as their displayed labels.
     *
     * @return array<int,object> Country selection rows with code and name.
     */
    public function getCountries(): array
    {
        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('alpha3', 'value'),
                $this->db->quoteName('name', 'text'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_countries'))
            ->order($this->db->quoteName('name') . ' ASC');
        $this->db->setQuery($query);

        return $this->db->loadObjectList() ?: [];
    }

    /**
     * Count the rounds assigned to a particular project.
     * Delegates to the shared count query and returns zero for nonpositive project IDs.
     *
     * @param int $projectId Owning project identifier.
     * @return int Number of associated rounds.
     */
    public function getRoundsCount(int $projectId): int
    {
        return $this->countByProject('#__sportsmanagement_round', 'project_id', $projectId);
    }

    /**
     * Count the divisions assigned to a particular project.
     * Uses the shared project count helper so empty or invalid projects display zero divisions.
     *
     * @param int $projectId Owning project identifier.
     * @return int Number of associated divisions.
     */
    public function getDivisionsCount(int $projectId): int
    {
        return $this->countByProject('#__sportsmanagement_division', 'project_id', $projectId);
    }

    /**
     * Count the matches reachable through a project's rounds.
     * Joins match rows to round rows to restrict the count to the selected project and returns zero for invalid IDs.
     *
     * @param int $projectId Owning project identifier.
     * @return int Number of matches in the project.
     */
    public function getMatchesCount(int $projectId): int
    {
        if ($projectId <= 0) {
            return 0;
        }

        $query = $this->db->createQuery()
            ->select('COUNT(' . $this->db->quoteName('m.id') . ')')
            ->from($this->db->quoteName('#__sportsmanagement_match', 'm'))
            ->join(
                'INNER',
                $this->db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $this->db->quoteName('r.id') . ' = ' . $this->db->quoteName('m.round_id')
            )
            ->where($this->db->quoteName('r.project_id') . ' = ' . $projectId);
        $this->db->setQuery($query);

        return (int) $this->db->loadResult();
    }

    /**
     * Count child rows in a selected table for a parent project.
     * Builds a quoted count query for the trusted internal table and foreign-key names provided by public count methods.
     *
     * @param string $table Internal project-owned database table name.
     * @param string $field Foreign-key column referencing the project.
     * @param int $projectId Identifier of the parent project.
     * @return int Number of matching child rows.
     */
    private function countByProject(string $table, string $field, int $projectId): int
    {
        if ($projectId <= 0) {
            return 0;
        }

        $query = $this->db->createQuery()
            ->select('COUNT(*)')
            ->from($this->db->quoteName($table))
            ->where($this->db->quoteName($field) . ' = ' . $projectId);
        $this->db->setQuery($query);

        return (int) $this->db->loadResult();
    }

    /**
     * Convert an internal lookup table to Joomla form option records.
     * Selects both raw id/name fields and their value/text aliases using quoted identifiers and a caller-specified sort column.
     *
     * @param string $table Internal lookup table name.
     * @param string $valueField Identifier column.
     * @param string $textField Display-label column.
     * @param string $orderField Column used to sort available choices.
     * @return array<int,object> Sorted ID/name and value/text option rows.
     */
    private function simpleOptions(string $table, string $valueField, string $textField, string $orderField): array
    {
        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName($valueField, 'id'),
                $this->db->quoteName($textField, 'name'),
                $this->db->quoteName($valueField, 'value'),
                $this->db->quoteName($textField, 'text'),
            ])
            ->from($this->db->quoteName($table))
            ->order($this->db->quoteName($orderField) . ' ASC');
        $this->db->setQuery($query);

        return $this->db->loadObjectList() ?: [];
    }
}
