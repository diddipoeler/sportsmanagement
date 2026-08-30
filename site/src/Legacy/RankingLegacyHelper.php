<?php
namespace Diddipoeler\Component\SportsManagement\Site\Legacy;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

/**
 * Internal implementation used by the historical JSMRanking compatibility layer.
 *
 * This class deliberately lives in its own PSR-4 file. RankingHelperFacade.php
 * can be reached through both Joomla's autoloader and legacy include paths, so
 * keeping the implementation out of that compatibility file prevents recursive
 * loading from compiling the same class declaration twice.
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

    /**
     * Preserve sportsmanagementHelper::getExtensions() for JSMRanking.
     *
     * The historical method does not actually filter by project id. It only
     * exposes an extension directory whose name equals the current view.
     */
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
        return self::$_tips;
    }

    public static function getWarnings(): array
    {
        return self::$_warnings;
    }

    public static function getNotes(): array
    {
        return self::$_notes;
    }

    public static function resetMessages(): void
    {
        self::$_tips = [];
        self::$_warnings = [];
        self::$_notes = [];
    }
}
