<?php
namespace Diddipoeler\Component\SportsManagement\Site\Legacy;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\SportsManagementProjectModel;

/**
 * Narrow compatibility facade for the historical ranking helper.
 *
 * JSMRanking still calls the former global sportsmanagementModelProject class
 * for project context and ranking template configuration. Native Joomla 5/6
 * views bind their active project model here instead of loading the legacy
 * project model alongside the namespaced MVC implementation.
 */
final class RankingProjectFacade
{
    private static ?SportsManagementProjectModel $model = null;

    public static function setModel(SportsManagementProjectModel $model): void
    {
        self::$model = $model;
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
    }

    public static function getTemplateConfig($template, $databaseSelector = 0, $context = ''): array
    {
        return self::model()->getTemplateConfig((string) $template);
    }

    public static function getProject($databaseSelector = 0, $context = ''): ?object
    {
        return self::model()->getProject();
    }

    private static function model(): SportsManagementProjectModel
    {
        if (!self::$model instanceof SportsManagementProjectModel) {
            throw new \RuntimeException('Ranking project facade requires a native project model.', 500);
        }

        return self::$model;
    }
}
