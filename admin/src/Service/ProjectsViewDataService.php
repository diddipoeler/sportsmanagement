<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

/** Read-only data provider for the administrator projects list. */
final class ProjectsViewDataService
{
    public function __construct(private DatabaseInterface $db)
    {
    }

    public function getExtraFields(string $template = 'project'): array
    {
        $query = $this->db->getQuery(true)
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

    public function getProjectExtraFieldNames(int $projectId): string
    {
        if ($projectId <= 0) {
            return '';
        }

        $query = $this->db->getQuery(true)
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

        return implode('<br>', $this->db->loadColumn() ?: []);
    }

    public function getLeagues(): array
    {
        return $this->simpleOptions('#__sportsmanagement_league', 'id', 'name', 'name');
    }

    public function getSportsTypes(): array
    {
        return $this->simpleOptions('#__sportsmanagement_sports_type', 'id', 'name', 'name');
    }

    public function getSeasons(): array
    {
        return $this->simpleOptions('#__sportsmanagement_season', 'id', 'name', 'name');
    }

    public function getAgeGroups(): array
    {
        $query = $this->db->getQuery(true)
            ->select([
                $this->db->quoteName('id', 'value'),
                $this->db->quoteName('name', 'text'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_agegroup'))
            ->order($this->db->quoteName('name') . ' ASC');
        $this->db->setQuery($query);

        return $this->db->loadObjectList() ?: [];
    }

    public function getMasterTemplates(): array
    {
        $query = $this->db->getQuery(true)
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

    public function getAssociations(): array
    {
        $query = $this->db->getQuery(true)
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

    public function getCountries(): array
    {
        $query = $this->db->getQuery(true)
            ->select([
                $this->db->quoteName('alpha3', 'value'),
                $this->db->quoteName('name', 'text'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_countries'))
            ->order($this->db->quoteName('name') . ' ASC');
        $this->db->setQuery($query);

        return $this->db->loadObjectList() ?: [];
    }

    public function getRoundsCount(int $projectId): int
    {
        return $this->countByProject('#__sportsmanagement_round', 'project_id', $projectId);
    }

    public function getDivisionsCount(int $projectId): int
    {
        return $this->countByProject('#__sportsmanagement_division', 'project_id', $projectId);
    }

    public function getMatchesCount(int $projectId): int
    {
        if ($projectId <= 0) {
            return 0;
        }

        $query = $this->db->getQuery(true)
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

    private function countByProject(string $table, string $field, int $projectId): int
    {
        if ($projectId <= 0) {
            return 0;
        }

        $query = $this->db->getQuery(true)
            ->select('COUNT(*)')
            ->from($this->db->quoteName($table))
            ->where($this->db->quoteName($field) . ' = ' . $projectId);
        $this->db->setQuery($query);

        return (int) $this->db->loadResult();
    }

    private function simpleOptions(string $table, string $valueField, string $textField, string $orderField): array
    {
        $query = $this->db->getQuery(true)
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
