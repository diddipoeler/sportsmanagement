<?php
/**
 * SportsManagement Joomla 5/6 file metadata.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Diddipoeler\Component\SportsManagement\Site\Legacy;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\SportsManagementProjectModel;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Narrow compatibility facade for the historical ranking helper.
 *
 * JSMRanking and some sport-specific ranking extensions still call the former
 * global sportsmanagementModelProject class and read a small set of its public
 * static state. Native Joomla 5/6 views bind their active project model here
 * instead of loading the legacy project model alongside the namespaced MVC
 * implementation.
 *
 * Team history views can ask JSMRanking to evaluate projects other than the
 * currently active MVC project. Those historical project contexts are loaded
 * directly here while the active native model remains unchanged.
 */
final class RankingProjectFacade
{
    private static ?SportsManagementProjectModel $model = null;

    // Public legacy state still read directly by historical ranking code.
    public static $_project = null;
    public static $projectid = 0;
    public static $_current_round = 0;
    public static $seasonid = 0;
    public static $cfg_which_database = 0;
    public static $projectslug = '';
    public static $divisionslug = '';
    public static $roundslug = '';
    public static $layout = '';

    /**
     * Install the native project model and synchronise the public legacy ranking state with its current project.
     * @param SportsManagementProjectModel $model Native project model.
     * @return void
     */
    public static function setModel(SportsManagementProjectModel $model): void
    {
        self::$model = $model;
        self::$projectid = $model->getProjectId();
        self::synchroniseLegacyState(self::$projectid);
    }

    /**
     * Select a legacy ranking project, reset cached project and round state and refresh the context for the requested database selector.
     * @param mixed $id Project identifier.
     * @param mixed $databaseSelector Legacy database choice.
     * @return void
     */
    public static function setProjectID($id, $databaseSelector = 0): void
    {
        self::$projectid = max(0, (int) $id);
        self::$cfg_which_database = (int) $databaseSelector;
        self::$_project = null;
        self::$_current_round = 0;
        self::synchroniseLegacyState(self::$projectid);
    }

    /**
     * Return the ranking template configuration through the native model for its current project or load the requested project's own configuration and defaults.
     * @param mixed $template Template name.
     * @param mixed $databaseSelector Legacy database choice.
     * @param mixed $context Retained compatibility argument, unused.
     * @return array Effective template options.
     */
    public static function getTemplateConfig($template, $databaseSelector = 0, $context = ''): array
    {
        self::$cfg_which_database = (int) $databaseSelector;
        $projectId = self::$projectid > 0 ? (int) self::$projectid : self::model()->getProjectId();

        if ($projectId === self::model()->getProjectId()) {
            return self::model()->getTemplateConfig((string) $template);
        }

        return self::loadTemplateConfigForProject((string) $template, $projectId);
    }

    /**
     * Refresh and return the selected project metadata while retaining the legacy database selection.
     * @param mixed $databaseSelector Legacy database choice.
     * @param mixed $context Retained compatibility argument, unused.
     * @return object|null Current project or null.
     */
    public static function getProject($databaseSelector = 0, $context = ''): ?object
    {
        self::$cfg_which_database = (int) $databaseSelector;
        $projectId = self::$projectid > 0 ? (int) self::$projectid : self::model()->getProjectId();
        self::synchroniseLegacyState($projectId);

        return self::$_project;
    }

    /**
     * Refresh public legacy project, season, round, division and layout properties from the native model, alternate project lookup and active request.
     * @param int $projectId Project identifier, or zero to use the model context.
     * @return void
     */
    private static function synchroniseLegacyState(int $projectId): void
    {
        $model = self::model();
        $projectId = $projectId > 0 ? $projectId : $model->getProjectId();
        $project = $projectId === $model->getProjectId()
            ? $model->getProject()
            : self::loadProject($projectId);
        $input = Factory::getApplication()->getInput();

        self::$projectid = $projectId;
        self::$_project = $project;
        self::$_current_round = (int) ($project->current_round ?? 0);
        self::$seasonid = (int) ($project->season_id ?? 0);
        self::$projectslug = (string) ($project->slug ?? '');
        self::$roundslug = (string) ($project->round_slug ?? '');
        self::$divisionslug = (string) $input->getString('division', '');
        self::$layout = (string) $input->getCmd('layout', '');

        if (self::$cfg_which_database === 0) {
            self::$cfg_which_database = $input->getInt('cfg_which_database', 0);
        }
    }

    /**
     * Query project metadata and related sport, league, season and round records. Construct legacy route slugs and override the league image with the season-specific logo if available.
     * @param int $projectId Positive project identifier.
     * @return object|null Project metadata or null.
     */
    private static function loadProject(int $projectId): ?object
    {
        if ($projectId <= 0) {
            return null;
        }

        $db = self::database();
        $query = $db->createQuery()
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
            ->where($db->quoteName('p.id') . ' = ' . $projectId);
        $db->setQuery($query, 0, 1);
        $project = $db->loadObject();

        if (!$project) {
            return null;
        }

        $sportName = (string) ($project->sport_type_name ?? '');
        $prefix = 'COM_SPORTSMANAGEMENT_ST_';
        $project->fs_sport_type_name = strtolower(str_starts_with($sportName, $prefix) ? substr($sportName, strlen($prefix)) : $sportName);

        $logoQuery = $db->createQuery()
            ->select($db->quoteName('logo_big'))
            ->from($db->quoteName('#__sportsmanagement_league_logos'))
            ->where($db->quoteName('league_id') . ' = ' . (int) ($project->league_id ?? 0))
            ->where($db->quoteName('season_id') . ' = ' . (int) ($project->season_id ?? 0));
        $db->setQuery($logoQuery, 0, 1);
        $seasonLogo = $db->loadResult();
        if ($seasonLogo) {
            $project->leaguepicture = $seasonLogo;
        }

        return $project;
    }

    /**
     * Merge XML template defaults with persisted project parameters, using the master project's configuration when no local override exists. Retain defaults if registry parsing fails.
     * @param string $template Template name.
     * @param int $projectId Project identifier.
     * @return array Effective configuration.
     */
    private static function loadTemplateConfigForProject(string $template, int $projectId): array
    {
        $defaults = self::loadDefaultTemplateConfig($template);
        if ($projectId <= 0) {
            return $defaults;
        }

        $params = self::loadSavedTemplateParams($template, $projectId);
        if ($params === null) {
            $project = self::loadProject($projectId);
            $masterId = (int) ($project->master_template ?? 0);
            if ($masterId > 0 && $masterId !== $projectId) {
                $params = self::loadSavedTemplateParams($template, $masterId);
            }
        }

        if ($params === null || $params === '') {
            return $defaults;
        }

        try {
            $registry = new Registry();
            $registry->loadString($params);
            return array_merge($defaults, $registry->toArray());
        } catch (\Throwable) {
            return $defaults;
        }
    }

    /**
     * Read the stored parameter string for a project and template from the resolved database.
     * @param string $template Template name.
     * @param int $projectId Project identifier.
     * @return string|null Stored parameters or null.
     */
    private static function loadSavedTemplateParams(string $template, int $projectId): ?string
    {
        $db = self::database();
        $query = $db->createQuery()
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__sportsmanagement_template_config'))
            ->where($db->quoteName('template') . ' = ' . $db->quote($template))
            ->where($db->quoteName('project_id') . ' = ' . $projectId);
        $db->setQuery($query, 0, 1);
        $value = $db->loadResult();

        return $value === null ? null : (string) $value;
    }

    /**
     * Read default field values from the named template XML file, returning an empty array for missing or unreadable definitions.
     * @param string $template Template name.
     * @return array Field names mapped to their defaults.
     */
    private static function loadDefaultTemplateConfig(string $template): array
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

    /**
     * Resolve the Joomla database service using the legacy ranking database selector.
     * @return DatabaseInterface Effective database connection.
     */
    private static function database(): DatabaseInterface
    {
        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);

        return SportsManagementDatabaseResolver::resolve(
            $joomlaDatabase,
            self::$cfg_which_database === 1 ? 1 : 0
        );
    }

    /**
     * Return the installed native project model required by legacy ranking calls.
     * @return SportsManagementProjectModel Active model.
     * @throws \RuntimeException If no native model was installed.
     */
    private static function model(): SportsManagementProjectModel
    {
        if (!self::$model instanceof SportsManagementProjectModel) {
            throw new \RuntimeException('Ranking project facade requires a native project model.', 500);
        }

        return self::$model;
    }
}
