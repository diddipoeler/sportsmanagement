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
            self::import('helpers.sportsmanagement', JPATH_ADMINISTRATOR);
        }

        self::requireFile(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/TVarDumper.php');
        self::requireFile(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/browser.php');

        foreach ([
            ['libraries.util', JPATH_ADMINISTRATOR],
            ['libraries.sportsmanagement.view', JPATH_ADMINISTRATOR],
            ['libraries.sportsmanagement.model', JPATH_ADMINISTRATOR],
            ['libraries.sportsmanagement.controller', JPATH_ADMINISTRATOR],
            ['libraries.sportsmanagement.table', JPATH_ADMINISTRATOR],
            ['libraries.sportsmanagement.formbehavior2', JPATH_ADMINISTRATOR],
            ['helpers.countries', JPATH_SITE],
            ['helpers.imageselect', JPATH_SITE],
            ['helpers.JSON', JPATH_SITE],
            ['helpers.csvhelper', JPATH_ADMINISTRATOR],
            ['models.databasetool', JPATH_ADMINISTRATOR],
        ] as [$path, $base]) {
            self::import($path, $base);
        }

        foreach ([
            'github.github',
            'github.object',
            'github.http',
            'github.commits',
            'github.milestones',
            'github.package',
            'github.package.issues',
            'github.package.activity',
            'github.package.issues.milestones',
            'github.package.activity.starring',
        ] as $library) {
            self::import('libraries.' . $library, JPATH_ADMINISTRATOR);
        }

        BaseDatabaseModel::addIncludePath(
            JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models',
            'sportsmanagementModel'
        );
        Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/tables');

        $params = ComponentHelper::getParams('com_sportsmanagement');
        self::setConstant('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE', $params->get('cfg_which_database'));
        self::setConstant('COM_SPORTSMANAGEMENT_HELP_SERVER', $params->get('cfg_help_server'));
        self::setConstant('COM_SPORTSMANAGEMENT_MODAL_POPUP_WIDTH', $params->get('modal_popup_width'));
        self::setConstant('COM_SPORTSMANAGEMENT_MODAL_POPUP_HEIGHT', $params->get('modal_popup_height'));
        self::setConstant('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO', $params->get('show_debug_info'));
        self::setConstant('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO_TEXT', '');
        self::setConstant('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO', $params->get('show_query_debug_info'));
        self::setConstant(
            'COM_SPORTSMANAGEMENT_PICTURE_SERVER',
            $params->get('cfg_dbprefix') || $params->get('cfg_which_database')
                ? $params->get('cfg_which_database_server')
                : Uri::root()
        );
        self::setConstant(
            'COM_SPORTSMANAGEMENT_FIELDSETS_TEMPLATE',
            JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/tmpl/edit_fieldsets.php'
        );
        self::setConstant(
            'COM_SPORTSMANAGEMENT_USE_NEW_TABLE',
            $params->get('cfg_which_database_table') === 'sportsmanagement'
        );
    }

    public static function bootForView(string $view): void
    {
        self::boot();
    }

    private static function import(string $path, string $base): void
    {
        if (!str_starts_with($path, 'components.com_sportsmanagement.')) {
            $path = 'components.com_sportsmanagement.' . $path;
        }

        self::requireFile(rtrim($base, '/\\') . '/' . str_replace('.', '/', $path) . '.php');
    }

    private static function requireFile(string $file): void
    {
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
