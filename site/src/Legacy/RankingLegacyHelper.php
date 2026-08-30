<?php
namespace Diddipoeler\Component\SportsManagement\Site\Legacy;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

/**
 * Internal compatibility helper for the native ranking view.
 *
 * The historical JSMRanking engine now uses the real administrator
 * sportsmanagementHelper again. This bridge only exposes the small API needed by
 * the namespaced view and mirrors the legacy helper message state when present.
 */
final class RankingLegacyHelper
{
    /** Public names intentionally mirror sportsmanagementHelper for extensions. */
    public static array $_tips = [];
    public static array $_warnings = [];
    public static array $_notes = [];

    public static function getDBConnection($request = false, $value = false): DatabaseInterface
    {
        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);

        return SportsManagementDatabaseResolver::resolve(
            $joomlaDatabase,
            (int) $value === 1 ? 1 : 0
        );
    }

    public static function getExtensions($projectId = 0): array
    {
        $view = Factory::getApplication()->getInput()->getCmd('view', '');
        if ($view === '' || preg_match('/^[A-Za-z0-9_-]+$/', $view) !== 1) {
            return [];
        }

        $directory = JPATH_SITE . '/components/com_sportsmanagement/extensions/' . $view;

        return is_dir($directory) ? [$view] : [];
    }

    public static function setTip($tip): void
    {
        self::$_tips[] = $tip;
    }

    public static function setWarning($warning): void
    {
        self::$_warnings[] = $warning;
    }

    public static function setNote($note): void
    {
        self::$_notes[] = $note;
    }

    public static function getTips(): array
    {
        return class_exists('sportsmanagementHelper', false)
            ? (array) \sportsmanagementHelper::$_tips
            : self::$_tips;
    }

    public static function getWarnings(): array
    {
        return class_exists('sportsmanagementHelper', false)
            ? (array) \sportsmanagementHelper::$_warnings
            : self::$_warnings;
    }

    public static function getNotes(): array
    {
        return class_exists('sportsmanagementHelper', false)
            ? (array) \sportsmanagementHelper::$_notes
            : self::$_notes;
    }

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
