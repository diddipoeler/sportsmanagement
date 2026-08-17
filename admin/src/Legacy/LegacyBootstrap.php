<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Legacy;

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

        if (!class_exists('sportsmanagementHelper')) {
            \JLoader::register('sportsmanagementHelper', JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php');
        }

        \JLoader::register('TVarDumper', JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/TVarDumper.php');
        \JLoader::register('Browser', JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/browser.php');
        \JLoader::import('components.com_sportsmanagement.libraries.util', JPATH_ADMINISTRATOR);
        \JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.view', JPATH_ADMINISTRATOR);
        \JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.model', JPATH_ADMINISTRATOR);
        \JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.controller', JPATH_ADMINISTRATOR);
        \JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.table', JPATH_ADMINISTRATOR);
        \JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.formbehavior2', JPATH_ADMINISTRATOR);
        \JLoader::import('components.com_sportsmanagement.helpers.countries', JPATH_SITE);
        \JLoader::import('components.com_sportsmanagement.helpers.imageselect', JPATH_SITE);
        \JLoader::import('components.com_sportsmanagement.helpers.JSON', JPATH_SITE);
        \JLoader::import('components.com_sportsmanagement.helpers.csvhelper', JPATH_ADMINISTRATOR);

        BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models', 'sportsmanagementModel');
        Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/tables');

        $params = ComponentHelper::getParams('com_sportsmanagement');
        self::define('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE', $params->get('cfg_which_database'));
        self::define('COM_SPORTSMANAGEMENT_HELP_SERVER', $params->get('cfg_help_server'));
        self::define('COM_SPORTSMANAGEMENT_MODAL_POPUP_WIDTH', $params->get('modal_popup_width'));
        self::define('COM_SPORTSMANAGEMENT_MODAL_POPUP_HEIGHT', $params->get('modal_popup_height'));
        self::define('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO', $params->get('show_debug_info'));
        self::define('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO_TEXT', '');
        self::define('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO', $params->get('show_query_debug_info'));
        self::define('COM_SPORTSMANAGEMENT_PICTURE_SERVER', $params->get('cfg_dbprefix') || $params->get('cfg_which_database') ? $params->get('cfg_which_database_server') : Uri::root());
        self::define('COM_SPORTSMANAGEMENT_FIELDSETS_TEMPLATE', JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/tmpl/edit_fieldsets.php');
        self::define('COM_SPORTSMANAGEMENT_USE_NEW_TABLE', $params->get('cfg_which_database_table') === 'sportsmanagement');
    }

    private static function define(string $name, mixed $value): void
    {
        if (!\defined($name)) {
            \define($name, $value);
        }
    }
}
