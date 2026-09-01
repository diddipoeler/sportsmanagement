<?php
/** Legacy compatibility facade for the Joomla 5/6 random quotes helper. */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementRquotes\Site\Helper\RquotesHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

if (!class_exists(RquotesHelper::class)) {
    require_once __DIR__ . '/src/Helper/RquotesHelper.php';
}

class modRquotesHelper
{
    public static function renderRquote(&$rquote, &$params, $module = null): void
    {
        $module ??= (object) ['module' => 'mod_sportsmanagement_rquotes', 'id' => 0];
        $componentParams = ComponentHelper::getParams('com_sportsmanagement');
        $pictureServer = (int) $params->get('cfg_which_database', 0)
            ? rtrim((string) $componentParams->get('cfg_which_database_server', ''), '/') . '/'
            : \Joomla\CMS\Uri\Uri::root();

        if (empty($rquote->picture_url)) {
            $path = trim((string) ($rquote->person_picture ?? ''));
            if ($path === '') {
                $path = trim((string) ($rquote->picture ?? ''));
            }
            if ($path !== '') {
                $rquote->picture_url = preg_match('#^https?://#i', $path)
                    ? $path
                    : rtrim($pictureServer, '/') . '/' . ltrim($path, '/');
            }
        }

        include ModuleHelper::getLayoutPath($module->module, '_rquote');
    }

    public static function getRandomRquote($category, $numOfRandom, &$params): array
    {
        return self::databaseResult($params, 'single_random', $category, $numOfRandom);
    }

    public static function getMultyRandomRquote($category, $numOfRandom, &$params): array
    {
        return self::databaseResult($params, 'multiple_random', $category, $numOfRandom);
    }

    public static function getSequentialRquote($category, &$params): array
    {
        return self::databaseResult($params, 'sequential', $category);
    }

    public static function getDailyRquote($category, $paramsOrUnused = null, $maybeParams = null): array
    {
        return self::databaseResult(self::registryArgument($paramsOrUnused, $maybeParams), 'daily', $category);
    }

    public static function getWeeklyRquote($category, $paramsOrUnused = null, $maybeParams = null): array
    {
        return self::databaseResult(self::registryArgument($paramsOrUnused, $maybeParams), 'weekly', $category);
    }

    public static function getMonthlyRquote($category, $paramsOrUnused = null, $maybeParams = null): array
    {
        return self::databaseResult(self::registryArgument($paramsOrUnused, $maybeParams), 'monthly', $category);
    }

    public static function getYearlyRquote($category, $paramsOrUnused = null, $maybeParams = null): array
    {
        return self::databaseResult(self::registryArgument($paramsOrUnused, $maybeParams), 'yearly', $category);
    }

    public static function getTodayRquote($category, $paramsOrUnused = null, $maybeParams = null): array
    {
        return self::databaseResult(self::registryArgument($paramsOrUnused, $maybeParams), 'today', $category);
    }

    public static function getTextFile(&$params, $filename, $module): array
    {
        return self::legacyText($params, (string) $filename, $module, false);
    }

    public static function getTextFile2(&$params, $filename, $module): array
    {
        return self::legacyText($params, (string) $filename, $module, true);
    }

    private static function databaseResult(
        Registry $params,
        string $rotation,
        mixed $category,
        ?int $numOfRandom = null
    ): array {
        $copy = clone $params;
        $copy->set('source', 'db');
        $copy->set('rotate', $rotation);
        $copy->set('category', self::categoryValues($category));
        if ($numOfRandom !== null) {
            $copy->set('num_of_random', max(1, $numOfRandom));
        }

        return self::nativeData($copy)['list'];
    }

    private static function legacyText(Registry $params, string $filename, object $module, bool $daily): array
    {
        $copy = clone $params;
        $copy->set('source', 'text');
        $copy->set('filename', basename($filename));
        $copy->set('randomtext', $daily ? 1 : 0);
        $result = self::nativeData($copy);
        $rows = $result['textLine'] !== '' ? [$result['textLine']] : [];
        $num = 0;

        include ModuleHelper::getLayoutPath($module->module, 'textfile');

        return $rows;
    }

    private static function nativeData(Registry $params): array
    {
        $app = Factory::getApplication();
        /** @var DatabaseInterface $database */
        $database = $app->getContainer()->get(DatabaseInterface::class);

        return (new RquotesHelper())->getData(
            $params,
            ComponentHelper::getParams('com_sportsmanagement'),
            $app,
            $database
        );
    }

    private static function registryArgument($first, $second): Registry
    {
        if ($second instanceof Registry) {
            return $second;
        }
        if ($first instanceof Registry) {
            return $first;
        }

        return new Registry();
    }

    private static function categoryValues(mixed $category): array
    {
        $values = is_array($category) ? $category : [$category];
        $result = [];
        foreach ($values as $value) {
            foreach (preg_split('/[\s,;]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $part) {
                if ((int) $part > 0) {
                    $result[] = (int) $part;
                }
            }
        }

        return array_values(array_unique($result));
    }
}
