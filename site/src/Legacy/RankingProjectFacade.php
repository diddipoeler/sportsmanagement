<?php
namespace Diddipoeler\Component\SportsManagement\Site\Legacy;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\SportsManagementProjectModel;
use Joomla\CMS\Factory;

/**
 * Narrow compatibility facade for the historical ranking helper.
 *
 * JSMRanking and some sport-specific ranking extensions still call the former
 * global sportsmanagementModelProject class and read a small set of its public
 * static state. Native Joomla 5/6 views bind their active project model here
 * instead of loading the legacy project model alongside the namespaced MVC
 * implementation.
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

    public static function setModel(SportsManagementProjectModel $model): void
    {
        self::$model = $model;
        self::synchroniseLegacyState();
    }

    public static function setProjectID($id, $databaseSelector = 0): void
    {
        $projectId = (int) $id;

        if ($projectId !== self::model()->getProjectId()) {
            throw new \RuntimeException(
                'Ranking project facade cannot switch the active native project model.',
                500
            );
        }

        self::$cfg_which_database = (int) $databaseSelector;
        self::synchroniseLegacyState();
    }

    public static function getTemplateConfig($template, $databaseSelector = 0, $context = ''): array
    {
        self::$cfg_which_database = (int) $databaseSelector;

        return self::model()->getTemplateConfig((string) $template);
    }

    public static function getProject($databaseSelector = 0, $context = ''): ?object
    {
        self::$cfg_which_database = (int) $databaseSelector;
        self::synchroniseLegacyState();

        return self::$_project;
    }

    private static function synchroniseLegacyState(): void
    {
        $model = self::model();
        $project = $model->getProject();
        $input = Factory::getApplication()->getInput();

        self::$projectid = $model->getProjectId();
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

    private static function model(): SportsManagementProjectModel
    {
        if (!self::$model instanceof SportsManagementProjectModel) {
            throw new \RuntimeException('Ranking project facade requires a native project model.', 500);
        }

        return self::$model;
    }
}
