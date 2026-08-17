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
            \JLoader::register('sportsmanagementHelper', JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php');
        }

        \JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.view', JPATH_SITE);
        \JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.model', JPATH_SITE);
        \JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.controller', JPATH_SITE);
        \JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.table', JPATH_ADMINISTRATOR);
        \JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.formbehavior2', JPATH_ADMINISTRATOR);
        \JLoader::import('components.com_sportsmanagement.helpers.route', JPATH_SITE);
        \JLoader::import('components.com_sportsmanagement.helpers.html', JPATH_SITE);
        \JLoader::import('components.com_sportsmanagement.helpers.countries', JPATH_SITE);
        \JLoader::import('components.com_sportsmanagement.helpers.simpleGMapGeocoder', JPATH_SITE);
        \JLoader::import('components.com_sportsmanagement.models.project', JPATH_SITE);

        BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_sportsmanagement/models', 'sportsmanagementModel');
        BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models', 'sportsmanagementModel');
        Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/tables');

        $params = ComponentHelper::getParams('com_sportsmanagement');
        self::define('COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS', $params->get('boostrap_div_class'));
        self::define('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE', $params->get('cfg_which_database'));
        self::define('COM_SPORTSMANAGEMENT_LOAD_BOOTSTRAP', $params->get('cfg_load_bootstrap'));
        self::define('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO', $params->get('show_debug_info'));
        self::define('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO', $params->get('show_query_debug_info'));
        self::define('COM_SPORTSMANAGEMENT_PICTURE_SERVER', $params->get('cfg_dbprefix') || $params->get('cfg_which_database') ? $params->get('cfg_which_database_server') : Uri::root());
        self::define('COM_SPORTSMANAGEMENT_SHOW_HELP_SERVER', $params->get('cfg_help_server', ''));
        self::define('COM_SPORTSMANAGEMENT_SHOW_BUGTRACKER_SERVER', $params->get('cfg_bugtracker_server', ''));
    }

    public static function bootForView(string $view): void
    {
        self::boot();

        if (strtolower($view) === 'predictionrules') {
            \JLoader::import('components.com_sportsmanagement.helpers.predictionroute', JPATH_SITE);
            \JLoader::import('components.com_sportsmanagement.models.prediction', JPATH_SITE);
        }
    }

    private static function define(string $name, mixed $value): void
    {
        if (!\defined($name)) {
            \define($name, $value);
        }
    }
}
