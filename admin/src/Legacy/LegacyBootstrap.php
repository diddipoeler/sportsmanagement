<?php
/**
 * Joomla 5/6 administrator legacy bootstrap compatibility boundary.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Legacy;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseDriver as FrameworkDatabaseDriver;
use Joomla\Filesystem\File as FrameworkFile;
use Joomla\Filesystem\Folder as FrameworkFolder;
use Joomla\Filesystem\Path as FrameworkPath;

final class LegacyBootstrap
{
    private static bool $booted = false;

    /**
     * Initialise administrator compatibility imports and constants once.
     *
     * Bridge legacy Joomla filesystem and database class names, load older
     * management helpers and libraries, register supported MVC include paths,
     * then publish component options for unmigrated administrator screens.
     *
     * @return void
     */
    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }

        self::$booted = true;
        self::bridgeRemovedFilesystemClasses();
        self::bridgeRemovedDatabaseClasses();

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

        // Joomla 6 removes the legacy include-path loaders. Keep them only on
        // Joomla versions where the methods still exist while namespaced code
        // continues to resolve through the component MVCFactory.
        if (method_exists(BaseDatabaseModel::class, 'addIncludePath')) {
            BaseDatabaseModel::addIncludePath(
                JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models',
                'sportsmanagementModel'
            );
        }

        if (method_exists(Table::class, 'addIncludePath')) {
            Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/tables');
        }

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

    /**
     * Provide the legacy administrator view bootstrap entry point.
     *
     * Administrator views currently share one set of imports, so callers
     * can pass a view identifier while the common boot routine performs
     * the one-time initialisation. The argument is retained for compatibility.
     *
     * @param string $view Administrator view requesting legacy dependencies.
     * @return void
     */
    public static function bootForView(string $view): void
    {
        self::boot();
    }

    /**
     * Joomla 6 moved the CMS filesystem wrappers to the Joomla Framework.
     *
     * A few large historical import/helper classes still reference the former
     * CMS class names. Alias those names only when Joomla no longer provides
     * them, so Joomla 5 keeps using its native compatibility classes while
     * Joomla 6 transparently resolves the Framework replacements.
     */
    private static function bridgeRemovedFilesystemClasses(): void
    {
        foreach ([
            'Joomla\\CMS\\Filesystem\\File' => FrameworkFile::class,
            'Joomla\\CMS\\Filesystem\\Folder' => FrameworkFolder::class,
            'Joomla\\CMS\\Filesystem\\Path' => FrameworkPath::class,
        ] as $legacyClass => $frameworkClass) {
            if (!class_exists($legacyClass) && class_exists($frameworkClass)) {
                class_alias($frameworkClass, $legacyClass);
            }
        }
    }

    /**
     * Keep historical external-database importers independent from Joomla's
     * optional backwards-compatibility plugin.
     *
     * Joomla 6 no longer exposes the old global JDatabaseDriver class by
     * default, while Joomla\Database\DatabaseDriver still provides the
     * corresponding driver API used by these importers.
     */
    private static function bridgeRemovedDatabaseClasses(): void
    {
        if (!class_exists('JDatabaseDriver', false) && class_exists(FrameworkDatabaseDriver::class)) {
            class_alias(FrameworkDatabaseDriver::class, 'JDatabaseDriver');
        }
    }

    /**
     * Import an administrator or site compatibility library by dotted path.
     *
     * Prefix component-relative paths, convert dotted package segments to
     * filesystem separators and delegate optional file loading to requireFile.
     *
     * @param string $path Dotted SportsManagement component library path.
     * @param string $base Site or administrator filesystem root.
     * @return void
     */
    private static function import(string $path, string $base): void
    {
        if (!str_starts_with($path, 'components.com_sportsmanagement.')) {
            $path = 'components.com_sportsmanagement.' . $path;
        }

        self::requireFile(rtrim($base, '/\\') . '/' . str_replace('.', '/', $path) . '.php');
    }

    /**
     * Include an existing legacy PHP dependency once per request.
     *
     * Suppress missing optional library imports while using require_once
     * to avoid redeclaring classes loaded by another Joomla entry point.
     *
     * @param string $file Absolute path of an optional legacy PHP library.
     * @return void
     */
    private static function requireFile(string $file): void
    {
        if (is_file($file)) {
            require_once $file;
        }
    }

    /**
     * Publish an administrator compatibility constant without redefining it.
     *
     * Preserve values established by other bootstrap paths and prevent PHP
     * constant-redefinition notices when older views initialise the component.
     *
     * @param string $name Public constant required by historical code.
     * @param mixed $value Value derived from component configuration.
     * @return void
     */
    private static function setConstant(string $name, mixed $value): void
    {
        if (!\defined($name)) {
            \define($name, $value);
        }
    }
}
