<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Registry\Registry;

abstract class SportsManagementProjectModel extends SportsManagementModel
{
    protected int $projectId = 0;
    protected int $divisionId = 0;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);
        $input = Factory::getApplication()->input;
        $this->projectId = $input->getInt('p', 0);
        $this->divisionId = $input->getInt('division', 0);
    }

    public function getProjectId(): int
    {
        return $this->projectId;
    }

    public function getDivisionId(): int
    {
        return $this->divisionId;
    }

    public function getProject(): ?object
    {
        if ($this->projectId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                'p.*',
                $db->quoteName('l.country'),
                $db->quoteName('st.id', 'sport_type_id'),
                $db->quoteName('st.name', 'sport_type_name'),
                $db->quoteName('st.icon', 'sport_type_picture'),
                $db->quoteName('st.eventtime', 'useeventtime'),
                $db->quoteName('l.picture', 'leaguepicture'),
                $db->quoteName('l.name', 'league_name'),
                $db->quoteName('s.name', 'season_name'),
                $db->quoteName('r.name', 'round_name'),
                $db->quoteName('l.cr_picture', 'cr_leaguepicture'),
                $db->quoteName('l.champions_complete'),
                $db->quoteName('asso.name', 'assoname'),
                "CONCAT_WS(':', p.id, p.alias) AS slug",
                "CONCAT_WS(':', l.id, l.alias) AS league_slug",
                "CONCAT_WS(':', s.id, s.alias) AS season_slug",
                "CONCAT_WS(':', r.id, r.alias) AS round_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_sports_type', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('p.sports_type_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_league', 'l') . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('p.league_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('p.season_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('p.current_round'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_associations', 'asso') . ' ON ' . $db->quoteName('asso.id') . ' = ' . $db->quoteName('l.associations'))
            ->where($db->quoteName('p.id') . ' = ' . $this->projectId);
        $db->setQuery($query, 0, 1);
        $project = $db->loadObject();

        if (!$project) {
            return null;
        }

        $sportName = (string) ($project->sport_type_name ?? '');
        $prefix = 'COM_SPORTSMANAGEMENT_ST_';
        $project->fs_sport_type_name = strtolower(str_starts_with($sportName, $prefix) ? substr($sportName, strlen($prefix)) : $sportName);

        $logoQuery = $db->getQuery(true)
            ->select($db->quoteName('logo_big'))
            ->from($db->quoteName('#__sportsmanagement_league_logos'))
            ->where($db->quoteName('league_id') . ' = ' . (int) $project->league_id)
            ->where($db->quoteName('season_id') . ' = ' . (int) $project->season_id);
        $db->setQuery($logoQuery, 0, 1);
        $seasonLogo = $db->loadResult();

        if ($seasonLogo) {
            $project->leaguepicture = $seasonLogo;
        }

        return $project;
    }

    public function getOverallConfig(): array
    {
        return $this->getTemplateConfig('overall');
    }

    public function getTemplateConfig(string $template): array
    {
        $defaults = $this->loadDefaultTemplateConfig($template);

        if ($this->projectId <= 0) {
            return $defaults;
        }

        $params = $this->loadSavedTemplateParams($template, $this->projectId);
        if ($params === null) {
            $project = $this->getProject();
            $masterId = (int) ($project->master_template ?? 0);
            if ($masterId > 0 && $masterId !== $this->projectId) {
                $params = $this->loadSavedTemplateParams($template, $masterId);
            }
        }

        if ($params === null || $params === '') {
            return $defaults;
        }

        try {
            $registry = new Registry();
            $registry->loadString((string) $params);
            return array_merge($defaults, $registry->toArray());
        } catch (\Throwable) {
            return $defaults;
        }
    }

    public function getDivision(?int $divisionId = null): ?object
    {
        $divisionId ??= $this->divisionId;
        if ($divisionId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_division'))
            ->where($db->quoteName('id') . ' = ' . $divisionId);
        $db->setQuery($query, 0, 1);
        return $db->loadObject() ?: null;
    }

    protected function getDivisionTreeIds(?int $divisionId = null): array
    {
        $divisionId ??= $this->divisionId;
        if ($divisionId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('id'), $db->quoteName('parent_id')])
            ->from($db->quoteName('#__sportsmanagement_division'))
            ->where($db->quoteName('project_id') . ' = ' . $this->projectId);
        $db->setQuery($query);
        $divisions = $db->loadObjectList() ?: [];

        $ids = [$divisionId];
        $changed = true;
        while ($changed) {
            $changed = false;
            foreach ($divisions as $division) {
                $id = (int) $division->id;
                $parentId = (int) $division->parent_id;
                if (in_array($parentId, $ids, true) && !in_array($id, $ids, true)) {
                    $ids[] = $id;
                    $changed = true;
                }
            }
        }

        return $ids;
    }

    private function loadSavedTemplateParams(string $template, int $projectId): ?string
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__sportsmanagement_template_config'))
            ->where($db->quoteName('template') . ' = ' . $db->quote($template))
            ->where($db->quoteName('project_id') . ' = ' . $projectId);
        $db->setQuery($query, 0, 1);
        $value = $db->loadResult();
        return $value === null ? null : (string) $value;
    }

    private function loadDefaultTemplateConfig(string $template): array
    {
        $file = JPATH_SITE . '/components/com_sportsmanagement/settings/default/' . basename($template) . '.xml';
        if (!is_file($file)) {
            return [];
        }

        try {
            $xml = simplexml_load_file($file);
        } catch (\Throwable) {
            return [];
        }
        if ($xml === false) {
            return [];
        }

        $defaults = [];
        foreach ($xml->xpath('//field[@name]') ?: [] as $field) {
            $attributes = $field->attributes();
            if (isset($attributes['default'])) {
                $defaults[(string) $attributes['name']] = (string) $attributes['default'];
            }
        }
        return $defaults;
    }
}
