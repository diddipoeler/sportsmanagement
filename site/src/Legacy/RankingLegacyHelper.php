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

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

/**
 * Internal compatibility helper for the native ranking view.
 *
 * The historical JSMRanking engine still calls the global sportsmanagementHelper
 * name. RankingCalculationAdapter aliases this class to that legacy name, so the
 * frontend ranking path can use the narrow compatibility API without loading the
 * administrator helper.
 */
final class RankingLegacyHelper
{
    /**
     * Return the active Joomla application for the legacy ranking extension lookup.
     * @return CMSApplicationInterface Active Joomla application.
     */
    private static function siteApplication(): CMSApplicationInterface
    {
        return Factory::getApplication();
    }

    /**
     * Retrieve the primary Joomla database service from the dependency injection container for use as the ranking connection fallback.
     * @return DatabaseInterface Primary database connection.
     */
    private static function joomlaDatabase(): DatabaseInterface
    {
        /** @var DatabaseInterface $database */
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        return $database;
    }

    /** Public names intentionally mirror sportsmanagementHelper for extensions. */
    public static array $_tips = [];
    public static array $_warnings = [];
    public static array $_notes = [];

    /**
     * Resolve the ranking database through the shared SportsManagement resolver, selecting the external mode only when the legacy selector converts to one.
     * @param mixed $request Retained legacy argument, currently unused.
     * @param mixed $value Legacy database selector.
     * @return DatabaseInterface Resolved ranking connection.
     */
    public static function getDBConnection($request = false, $value = false): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve(
            self::joomlaDatabase(),
            (int) $value === 1 ? 1 : 0
        );
    }

    /**
     * Read the current view name, reject invalid directory-name characters and return the view as an extension identifier only when its component extension directory exists.
     * @param mixed $projectId Retained legacy project argument, currently unused.
     * @return array<int,string> Matching extension identifiers.
     */
    public static function getExtensions($projectId = 0): array
    {
        $view = self::siteApplication()->getInput()->getCmd('view', '');
        if ($view === '' || preg_match('/^[A-Za-z0-9_-]+$/', $view) !== 1) {
            return [];
        }

        $directory = JPATH_SITE . '/components/com_sportsmanagement/extensions/' . $view;

        return is_dir($directory) ? [$view] : [];
    }

    /**
     * Append a ranking tip to the local compatibility message collection.
     * @param mixed $tip Ranking tip payload.
     * @return void
     */
    public static function setTip($tip): void
    {
        self::$_tips[] = $tip;
    }

    /**
     * Append a ranking warning to the local compatibility message collection.
     * @param mixed $warning Ranking warning payload.
     * @return void
     */
    public static function setWarning($warning): void
    {
        self::$_warnings[] = $warning;
    }

    /**
     * Append a ranking note to the local compatibility message collection.
     * @param mixed $note Ranking note payload.
     * @return void
     */
    public static function setNote($note): void
    {
        self::$_notes[] = $note;
    }

    /**
     * Retrieve ranking tips from the loaded global helper when available or from the local compatibility collection otherwise.
     * @return array Collected ranking tips.
     */
    public static function getTips(): array
    {
        return class_exists('sportsmanagementHelper', false)
            ? (array) \sportsmanagementHelper::$_tips
            : self::$_tips;
    }

    /**
     * Retrieve ranking warnings from the loaded global helper when available or from the local compatibility collection otherwise.
     * @return array Collected ranking warnings.
     */
    public static function getWarnings(): array
    {
        return class_exists('sportsmanagementHelper', false)
            ? (array) \sportsmanagementHelper::$_warnings
            : self::$_warnings;
    }

    /**
     * Retrieve ranking notes from the loaded global helper when available or from the local compatibility collection otherwise.
     * @return array Collected ranking notes.
     */
    public static function getNotes(): array
    {
        return class_exists('sportsmanagementHelper', false)
            ? (array) \sportsmanagementHelper::$_notes
            : self::$_notes;
    }

    /**
     * Clear all local ranking message collections and the corresponding global helper collections when the historical helper is already loaded.
     * @return void
     */
    public static function resetMessages(): void
    {
        self::$_tips = [];
        self::$_warnings = [];
        self::$_notes = [];

        if (class_exists('sportsmanagementHelper', false)) {
            \sportsmanagementHelper::$_tips = [];
            \sportsmanagementHelper::$_warnings = [];
            \sportsmanagementHelper::$_notes = [];
        }
    }
}

if (!class_exists(RankingHelperFacade::class, false)) {
    class_alias(RankingLegacyHelper::class, RankingHelperFacade::class);
}
