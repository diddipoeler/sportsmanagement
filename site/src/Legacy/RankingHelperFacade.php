<?php
namespace Diddipoeler\Component\SportsManagement\Site\Legacy;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

/**
 * Narrow compatibility surface used by the historical JSMRanking engine.
 *
 * The ranking helper only needs a database connection, extension discovery and
 * its legacy note/warning/tip collectors. Keeping those calls here prevents the
 * large administrator sportsmanagementHelper from being loaded by native views.
 */
final class RankingHelperFacade
{
    private static array $tips = [];
    private static array $warnings = [];
    private static array $notes = [];

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
        self::$tips[] = $tip;
    }

    public static function setWarning($warning): void
    {
        self::$warnings[] = $warning;
    }

    public static function setNote($note): void
    {
        self::$notes[] = $note;
    }

    public static function getTips(): array
    {
        return self::$tips;
    }

    public static function getWarnings(): array
    {
        return self::$warnings;
    }

    public static function getNotes(): array
    {
        return self::$notes;
    }

    public static function resetMessages(): void
    {
        self::$tips = [];
        self::$warnings = [];
        self::$notes = [];
    }
}
