<?php
namespace Diddipoeler\Component\SportsManagement\Site\Legacy;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;

final class LegacyBootstrap
{
    private static bool $booted = false;
    private static bool $presentationBooted = false;

    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }

        self::$booted = true;

        if (!\defined('JSM_PATH')) {
            \define('JSM_PATH', 'components/com_sportsmanagement');
        }

        if (!class_exists('sportsmanagementHelper')) {
            self::import('helpers.sportsmanagement', JPATH_ADMINISTRATOR);
        }

        foreach ([
            ['libraries.sportsmanagement.view', JPATH_SITE],
            ['libraries.sportsmanagement.model', JPATH_SITE],
            ['libraries.sportsmanagement.controller', JPATH_SITE],
            ['libraries.sportsmanagement.table', JPATH_ADMINISTRATOR],
            ['libraries.sportsmanagement.formbehavior2', JPATH_ADMINISTRATOR],
            ['helpers.route', JPATH_SITE],
            ['helpers.html', JPATH_SITE],
            ['helpers.countries', JPATH_SITE],
            ['helpers.simpleGMapGeocoder', JPATH_SITE],
            ['models.project', JPATH_SITE],
        ] as [$path, $base]) {
            self::import($path, $base);
        }

        self::registerLegacyIncludePaths();
        self::initialiseConstants();
    }

    /**
     * Load only the historical presentation helpers needed by a native view.
     *
     * Teamplan owns its project/model data natively, so loading models.project
     * here would reintroduce a large Joomla 3-era data layer solely because
     * the historical tmpl files still use a few global helper class names.
     */
    public static function bootPresentationForView(string $view): void
    {
        $view = strtolower($view);

        if ($view !== 'teamplan') {
            self::bootForView($view);
            return;
        }

        if (!self::$presentationBooted) {
            self::$presentationBooted = true;

            if (!\defined('JSM_PATH')) {
                \define('JSM_PATH', 'components/com_sportsmanagement');
            }

            if (!class_exists('sportsmanagementHelper')) {
                self::import('helpers.sportsmanagement', JPATH_ADMINISTRATOR);
            }

            foreach ([
                ['libraries.sportsmanagement.view', JPATH_SITE],
                ['libraries.sportsmanagement.model', JPATH_SITE],
                ['libraries.sportsmanagement.controller', JPATH_SITE],
                ['libraries.sportsmanagement.table', JPATH_ADMINISTRATOR],
                ['libraries.sportsmanagement.formbehavior2', JPATH_ADMINISTRATOR],
                ['helpers.route', JPATH_SITE],
                ['helpers.html', JPATH_SITE],
                ['helpers.countries', JPATH_SITE],
                ['helpers.simpleGMapGeocoder', JPATH_SITE],
            ] as [$path, $base]) {
                self::import($path, $base);
            }

            self::registerLegacyIncludePaths();
            self::initialiseConstants();
        }

        self::import('helpers.comments', JPATH_SITE);
        self::setConstant('COM_SPORTSMANAGEMENT_SHOW_VIEW', ucfirst($view));
    }

    public static function bootForView(string $view): void
    {
        self::boot();

        $view = strtolower($view);
        $imports = [];

        switch ($view) {
            case 'allprojects':
                $imports = [['models.leagues', JPATH_ADMINISTRATOR], ['models.seasons', JPATH_ADMINISTRATOR]];
                break;

            case 'ranking':
            case 'curve':
            case 'leaguechampionoverview':
                $imports = [
                    ['helpers.ranking', JPATH_SITE],
                    ['models.clubnames', JPATH_ADMINISTRATOR],
                    ['models.rounds', JPATH_ADMINISTRATOR],
                    ['models.projectteams', JPATH_ADMINISTRATOR],
                    ['models.club', JPATH_ADMINISTRATOR],
                    ['models.league', JPATH_ADMINISTRATOR],
                ];
                break;

            case 'results':
                $imports = [
                    ['helpers.comments', JPATH_SITE],
                    ['models.rounds', JPATH_ADMINISTRATOR],
                    ['models.round', JPATH_ADMINISTRATOR],
                    ['models.match', JPATH_ADMINISTRATOR],
                    ['models.pagination', JPATH_SITE],
                ];
                break;

            case 'editmatch':
            case 'jltournamenttree':
                $imports = [['models.match', JPATH_ADMINISTRATOR], ['models.jlextindividualsport', JPATH_ADMINISTRATOR]];
                break;

            case 'matchreport':
            case 'rankingplayerbillard':
                $imports = [
                    ['helpers.comments', JPATH_SITE],
                    ['models.playground', JPATH_ADMINISTRATOR],
                    ['models.match', JPATH_ADMINISTRATOR],
                    ['models.round', JPATH_ADMINISTRATOR],
                    ['models.player', JPATH_SITE],
                ];
                break;

            case 'resultsranking':
            case 'rankingmatrix':
                $imports = [
                    ['models.ranking', JPATH_SITE],
                    ['models.results', JPATH_SITE],
                    ['helpers.ranking', JPATH_SITE],
                    ['models.rounds', JPATH_ADMINISTRATOR],
                    ['models.round', JPATH_ADMINISTRATOR],
                    ['models.projectteams', JPATH_ADMINISTRATOR],
                    ['models.clubnames', JPATH_ADMINISTRATOR],
                    ['helpers.comments', JPATH_SITE],
                    ['models.club', JPATH_ADMINISTRATOR],
                    ['models.league', JPATH_ADMINISTRATOR],
                    ['models.pagination', JPATH_SITE],
                ];
                break;

            case 'resultsmatrix':
                $imports = [
                    ['models.projectteams', JPATH_ADMINISTRATOR],
                    ['models.matrix', JPATH_SITE],
                    ['models.results', JPATH_SITE],
                    ['helpers.comments', JPATH_SITE],
                    ['models.round', JPATH_ADMINISTRATOR],
                    ['models.pagination', JPATH_SITE],
                ];
                break;

            case 'roster':
            case 'rosteralltime':
                $imports = [['models.player', JPATH_SITE], ['models.jlextindividualsport', JPATH_ADMINISTRATOR]];
                break;

            case 'clubinfo':
                $imports = [['models.club', JPATH_ADMINISTRATOR]];
                break;

            case 'teamplan':
                $imports = [['helpers.comments', JPATH_SITE]];
                break;

            case 'editclub':
                $imports = [['models.clubinfo', JPATH_SITE], ['helpers.imageselect', JPATH_SITE], ['helpers.JSON', JPATH_SITE]];
                break;

            case 'editperson':
                $imports = [['models.person', JPATH_SITE], ['helpers.imageselect', JPATH_SITE]];
                break;

            case 'player':
            case 'staff':
            case 'referee':
                $imports = [['models.person', JPATH_SITE], ['models.eventtypes', JPATH_ADMINISTRATOR]];
                break;

            case 'teaminfo':
                $imports = [['helpers.ranking', JPATH_SITE]];
                break;

            case 'playground':
                $imports = [
                    ['models.playground', JPATH_ADMINISTRATOR],
                    ['models.teams', JPATH_ADMINISTRATOR],
                    ['models.team', JPATH_ADMINISTRATOR],
                ];
                break;

            case 'nextmatch':
                $imports = [
                    ['helpers.comments', JPATH_SITE],
                    ['helpers.ranking', JPATH_SITE],
                    ['models.playground', JPATH_ADMINISTRATOR],
                    ['models.match', JPATH_ADMINISTRATOR],
                ];
                break;

            case 'ical':
                $imports = [['helpers.iCalcreator', JPATH_SITE]];
                break;

            case 'scoresheet':
                $imports = [['helpers.scoresheet', JPATH_SITE]];
                break;

            case 'predictionrules':
            case 'predictionranking':
            case 'predictionusers':
            case 'predictionuser':
            case 'predictionentry':
            case 'predictionresults':
                $imports = [['helpers.predictionroute', JPATH_SITE], ['models.prediction', JPATH_SITE]];
                break;
        }

        foreach ($imports as [$path, $base]) {
            self::import($path, $base);
        }

        self::setConstant('COM_SPORTSMANAGEMENT_SHOW_VIEW', ucfirst($view));
    }

    private static function registerLegacyIncludePaths(): void
    {
        // Joomla 6 removes the legacy include-path loaders. Keep them only on
        // Joomla versions where they still exist; namespaced code resolves via
        // the component MVCFactory on both site and administrator clients.
        if (method_exists(BaseDatabaseModel::class, 'addIncludePath')) {
            BaseDatabaseModel::addIncludePath(
                JPATH_SITE . '/components/com_sportsmanagement/models',
                'sportsmanagementModel'
            );
            BaseDatabaseModel::addIncludePath(
                JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models',
                'sportsmanagementModel'
            );
        }

        if (method_exists(Table::class, 'addIncludePath')) {
            Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/tables');
        }
    }

    private static function initialiseConstants(): void
    {
        $params = ComponentHelper::getParams('com_sportsmanagement');
        self::setConstant('COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS', $params->get('boostrap_div_class'));
        self::setConstant('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE', $params->get('cfg_which_database'));
        self::setConstant('COM_SPORTSMANAGEMENT_LOAD_BOOTSTRAP', $params->get('cfg_load_bootstrap'));
        self::setConstant('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO', $params->get('show_debug_info'));
        self::setConstant('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO', $params->get('show_query_debug_info'));
        self::setConstant(
            'COM_SPORTSMANAGEMENT_PICTURE_SERVER',
            $params->get('cfg_dbprefix') || $params->get('cfg_which_database')
                ? $params->get('cfg_which_database_server')
                : Uri::root()
        );
        self::setConstant('COM_SPORTSMANAGEMENT_SHOW_HELP_SERVER', $params->get('cfg_help_server', ''));
        self::setConstant('COM_SPORTSMANAGEMENT_SHOW_BUGTRACKER_SERVER', $params->get('cfg_bugtracker_server', ''));
    }

    private static function import(string $path, string $base): void
    {
        if (!str_starts_with($path, 'components.com_sportsmanagement.')) {
            $path = 'components.com_sportsmanagement.' . $path;
        }

        $file = rtrim($base, '/\\') . '/' . str_replace('.', '/', $path) . '.php';

        if (is_file($file)) {
            require_once $file;
        }
    }

    private static function setConstant(string $name, mixed $value): void
    {
        if (!\defined($name)) {
            \define($name, $value);
        }
    }
}
