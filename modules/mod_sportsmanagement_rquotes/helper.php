<?php
/**
 * Legacy compatibility facade for the Joomla 5/6 random quotes helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Module\SportsManagementRquotes\Site\Helper\RquotesHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

$nativeDependencies = [
    SportsManagementDatabaseResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
    SportsManagementSiteApplicationResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
    RquotesHelper::class => __DIR__ . '/src/Helper/RquotesHelper.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

foreach ([
    SportsManagementDatabaseResolver::class,
    SportsManagementSiteApplicationResolver::class,
    RquotesHelper::class,
] as $requiredClass) {
    if (!class_exists($requiredClass)) {
        throw new \RuntimeException('SportsManagement Rquotes dependency could not be loaded: ' . $requiredClass, 500);
    }
}

if (!class_exists('modRquotesHelper', false)) {
    final class modRquotesHelper
    {
    /**
     * Render one quote using the selected Joomla module layout.
     * Normalises module settings, resolves the correct image base URL and provides the quote object to the existing _rquote layout.
     *
     * @param object $rquote Quote to render; picture_url may be filled in.
     * @param Registry|array $params Module parameters passed by reference.
     * @param object|null $module Module descriptor or a legacy fallback.
     * @return void
     */
    public static function renderRquote(&$rquote, &$params, $module = null): void
    {
        $registry = $params instanceof Registry ? $params : new Registry((array) $params);
        $module ??= (object) ['module' => 'mod_sportsmanagement_rquotes', 'id' => 0];
        $componentParams = ComponentHelper::getParams('com_sportsmanagement');
        $pictureServer = (int) $registry->get('cfg_which_database', 0)
            ? rtrim((string) $componentParams->get('cfg_which_database_server', ''), '/') . '/'
            : Uri::root();

        if (empty($rquote->picture_url)) {
            $path = trim((string) ($rquote->person_picture ?? ''));
            if ($path === '') {
                $path = trim((string) ($rquote->picture ?? ''));
            }
            if ($path !== '') {
                // When no external media server is configured, do not
                // accidentally turn relative media paths into root URLs.
                $rquote->picture_url = preg_match('#^https?://#i', $path)
                    ? $path
                    : (trim($pictureServer, '/') !== ''
                        ? rtrim($pictureServer, '/') . '/' . ltrim($path, '/')
                        : '');
            }
        }

        include ModuleHelper::getLayoutPath($module->module, '_rquote');
    }

    /**
     * Retrieve a single random quote from the configured database categories.
     * Routes the legacy API to the native helper with single_random rotation.
     *
     * @param mixed $category Category IDs or configured category values.
     * @param int $numOfRandom Maximum number of random records.
     * @param Registry|array $params Legacy module configuration.
     * @return array Selected quote records.
     */
    public static function getRandomRquote($category, $numOfRandom, &$params): array
    {
        return self::databaseResult($params, 'single_random', $category, $numOfRandom);
    }

    /**
     * Retrieve multiple randomly rotated quotes for the requested categories.
     * Preserves the misspelled legacy method name while passing multiple_random to the native implementation.
     *
     * @param mixed $category Category IDs or configured category values.
     * @param int $numOfRandom Desired number of quotes.
     * @param Registry|array $params Legacy module configuration.
     * @return array Selected quote records.
     */
    public static function getMultyRandomRquote($category, $numOfRandom, &$params): array
    {
        return self::databaseResult($params, 'multiple_random', $category, $numOfRandom);
    }

    /**
     * Read quotes in their configured sequential rotation.
     * Converts the category selection and delegates database access to the namespaced Joomla module helper.
     *
     * @param mixed $category Category selection.
     * @param Registry|array $params Legacy module parameters.
     * @return array Selected quote records.
     */
    public static function getSequentialRquote($category, &$params): array
    {
        return self::databaseResult($params, 'sequential', $category);
    }

    /**
     * Select the quote assigned to the current day.
     * Accepts both historical argument layouts and configures daily rotation before querying the native helper.
     *
     * @param mixed $category Category selection.
     * @param mixed $paramsOrUnused Legacy parameters or ignored positional argument.
     * @param mixed $maybeParams Optional parameters in the alternate legacy position.
     * @return array Daily quote records.
     */
    public static function getDailyRquote($category, $paramsOrUnused = null, $maybeParams = null): array
    {
        return self::databaseResult(self::registryArgument($paramsOrUnused, $maybeParams), 'daily', $category);
    }

    /**
     * Select a stable quote for the current week.
     * Supports older calling conventions and asks the native helper to use weekly rotation.
     *
     * @param mixed $category Category selection.
     * @param mixed $paramsOrUnused Legacy parameters or ignored argument.
     * @param mixed $maybeParams Optional alternate Registry argument.
     * @return array Weekly quote records.
     */
    public static function getWeeklyRquote($category, $paramsOrUnused = null, $maybeParams = null): array
    {
        return self::databaseResult(self::registryArgument($paramsOrUnused, $maybeParams), 'weekly', $category);
    }

    /**
     * Select a quote for the current month.
     * Maintains the old method signature while using native monthly rotation and database handling.
     *
     * @param mixed $category Category selection.
     * @param mixed $paramsOrUnused Legacy arguments or module settings.
     * @param mixed $maybeParams Optional module settings in the alternate position.
     * @return array Monthly quote records.
     */
    public static function getMonthlyRquote($category, $paramsOrUnused = null, $maybeParams = null): array
    {
        return self::databaseResult(self::registryArgument($paramsOrUnused, $maybeParams), 'monthly', $category);
    }

    /**
     * Select a quote for the current year.
     * Normalises historical parameters and requests yearly rotation from the native helper.
     *
     * @param mixed $category Category selection.
     * @param mixed $paramsOrUnused Legacy arguments or module settings.
     * @param mixed $maybeParams Optional alternate Registry argument.
     * @return array Yearly quote records.
     */
    public static function getYearlyRquote($category, $paramsOrUnused = null, $maybeParams = null): array
    {
        return self::databaseResult(self::registryArgument($paramsOrUnused, $maybeParams), 'yearly', $category);
    }

    /**
     * Retrieve today's quote using the historical today rotation mode.
     * Keeps template override calls compatible while passing the selection to the native Joomla 5/6 data provider.
     *
     * @param mixed $category Category selection.
     * @param mixed $paramsOrUnused Legacy arguments or module settings.
     * @param mixed $maybeParams Optional alternate Registry argument.
     * @return array Today's quote records.
     */
    public static function getTodayRquote($category, $paramsOrUnused = null, $maybeParams = null): array
    {
        return self::databaseResult(self::registryArgument($paramsOrUnused, $maybeParams), 'today', $category);
    }

    /**
     * Load and render a quote line from a text source.
     * Selects ordinary text-file rotation and keeps the historical textfile layout inclusion for older module overrides.
     *
     * @param Registry|array $params Module parameters passed by reference.
     * @param string $filename Configured text file name.
     * @param object $module Module descriptor used to resolve the layout.
     * @return array Selected text lines.
     */
    public static function getTextFile(&$params, $filename, $module): array
    {
        $registry = $params instanceof Registry ? $params : new Registry((array) $params);

        return self::legacyText($registry, (string) $filename, $module, false);
    }

    /**
     * Load and render a daily text-file quote.
     * Uses date-based text rotation through the native helper and retains the legacy layout contract.
     *
     * @param Registry|array $params Module parameters passed by reference.
     * @param string $filename Configured text file name.
     * @param object $module Module descriptor used to resolve the layout.
     * @return array Selected text lines.
     */
    public static function getTextFile2(&$params, $filename, $module): array
    {
        $registry = $params instanceof Registry ? $params : new Registry((array) $params);

        return self::legacyText($registry, (string) $filename, $module, true);
    }

    /**
     * Translate a legacy database quote request into native module settings.
     * Clones the original Registry, sanitises the category values and sets rotation and record limits without mutating the caller's configuration.
     *
     * @param mixed $params Legacy Registry or array configuration.
     * @param string $rotation Native quote rotation strategy.
     * @param mixed $category Legacy category selection.
     * @param int|null $numOfRandom Optional number of quote records.
     * @return array Selected quote records.
     */
    private static function databaseResult(
        mixed $params,
        string $rotation,
        mixed $category,
        ?int $numOfRandom = null
    ): array {
        $registry = $params instanceof Registry ? $params : new Registry((array) $params);
        $copy = clone $registry;
        $copy->set('source', 'db');
        $copy->set('rotate', $rotation);
        $copy->set('category', self::categoryValues($category));
        if ($numOfRandom !== null) {
            $copy->set('num_of_random', max(1, $numOfRandom));
        }

        return self::nativeData($copy)['list'];
    }

    /**
     * Render text-file quotes using the legacy module layout.
     * Limits the filename to its basename, selects daily or ordinary text rotation and exposes the old layout variables.
     *
     * @param Registry $params Module configuration.
     * @param string $filename File name without any trusted directory component.
     * @param object $module Module descriptor.
     * @param bool $daily Whether to use daily text rotation.
     * @return array Chosen text lines.
     */
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

    /**
     * Execute the namespaced quotes helper with Joomla dependencies.
     * Resolves the site application, database and component parameters, then returns the native structured quote data.
     *
     * @param Registry $params Normalised quote source and rotation parameters.
     * @return array Native quote data with list and textLine keys.
     */
    private static function nativeData(Registry $params): array
    {
        $app = self::siteApplication();
        $database = self::joomlaDatabase();

        return (new RquotesHelper())->getData(
            $params,
            ComponentHelper::getParams('com_sportsmanagement'),
            $app,
            $database
        );
    }

    /**
     * Obtain the active Joomla frontend application for quote rendering.
     * Routes through the component application resolver rather than the deprecated global application accessor.
     *
     * @return CMSApplicationInterface Active frontend application.
     */
    private static function siteApplication(): CMSApplicationInterface
    {
        $app = SportsManagementSiteApplicationResolver::resolve();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Rquotes requires the Joomla site application.', 500);
        }

        return $app;
    }

    /**
     * Resolve the database driver from Joomla's dependency injection container.
     * Provides the native quotes helper with a DatabaseInterface rather than relying on legacy database globals.
     *
     * @return DatabaseInterface Joomla database driver.
     */
    private static function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement Rquotes database service is unavailable.', 500);
        }

        return $database;
    }

    /**
     * Select the Registry from two supported historical parameter positions.
     * Prefers the third argument when provided, otherwise the second argument, and supplies an empty Registry for calls without either.
     *
     * @param mixed $first First possible Registry argument.
     * @param mixed $second Second possible Registry argument.
     * @return Registry Normalised module configuration.
     */
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

    /**
     * Normalise category IDs submitted through legacy Joomla select fields.
     * Splits comma- or whitespace-delimited values, validates positive integer IDs and deduplicates them before building database filters.
     *
     * @param mixed $category Scalar or array category selection.
     * @return array<int, int> Unique validated category IDs.
     */
    private static function categoryValues(mixed $category): array
    {
        $values = is_array($category) ? $category : [$category];
        $result = [];
        foreach ($values as $value) {
            if (!is_scalar($value)) {
                continue;
            }

            foreach (preg_split('/[\s,;]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $part) {
                // Joomla select fields may submit an ID with a value suffix,
                // e.g. "12:category". Accept the same format as the native helper.
                if (!preg_match('/^([1-9]\d*)(?::[A-Za-z0-9_-]+)?$/', $part, $match)) {
                    continue;
                }

                $id = filter_var($match[1], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                if ($id === false) {
                    continue;
                }

                $result[] = $id;
            }
        }

        return array_values(array_unique($result));
    }
    }
}
