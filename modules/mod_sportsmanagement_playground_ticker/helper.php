<?php
/**
 * Legacy helper bridge for the Joomla 5/6 SportsManagement playground ticker module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Module\SportsManagementPlaygroundTicker\Site\Helper\PlaygroundTickerHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

$nativeDependencies = [
    SportsManagementDatabaseResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
    SportsManagementSiteApplicationResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
    PlaygroundTickerHelper::class => __DIR__ . '/src/Helper/PlaygroundTickerHelper.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

foreach ([
    SportsManagementDatabaseResolver::class,
    SportsManagementSiteApplicationResolver::class,
    PlaygroundTickerHelper::class,
] as $requiredClass) {
    if (!class_exists($requiredClass)) {
        throw new \RuntimeException('SportsManagement PlaygroundTicker dependency could not be loaded: ' . $requiredClass, 500);
    }
}

if (!class_exists('modJSMPlaygroundTicker', false)) {
    final class modJSMPlaygroundTicker
    {
    /**
     * Load playground ticker data for legacy SportsManagement templates.
     *
     * Converts module options to Joomla Registry, requires the public
     * site application and a valid database service, and delegates record
     * lookup to the namespaced Joomla 5/6 PlaygroundTickerHelper.
     *
     * @param Registry|array $params Module configuration.
     * @param DatabaseInterface|null $database Optional database override.
     * @return array Playground ticker records prepared for the template.
     * @throws \RuntimeException If the site client or database is invalid.
     */
    public static function getData($params, ?DatabaseInterface $database = null): array
    {
        $registry = $params instanceof Registry ? $params : new Registry((array) $params);
        $app = self::siteApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement PlaygroundTicker legacy helper requires the Joomla site application.', 500);
        }

        $database = self::joomlaDatabase($database);

        return (new PlaygroundTickerHelper())->getData($registry, $app, $database);
    }

    /**
     * Preserve the historical playground-by-project module helper API.
     *
     * Delegates the legacy Spanish-named task to getData() so both
     * entry points use the same validated Joomla frontend services.
     *
     * @param Registry|array $params Module configuration.
     * @param DatabaseInterface|null $database Optional database override.
     * @return array Playground ticker records for the selected project.
     */
    public static function getEstadios_Proyecto($params, ?DatabaseInterface $database = null): array
    {
        return self::getData($params, $database);
    }

    /**
     * Resolve the Joomla application for the playground module adapter.
     *
     * Uses the shared SportsManagement application resolver. The data
     * entry point verifies the result belongs to Joomla's site client.
     *
     * @return CMSApplicationInterface Current Joomla application.
     */
    private static function siteApplication(): CMSApplicationInterface
    {
        return SportsManagementSiteApplicationResolver::resolve();
    }

    /**
     * Retrieve a valid Joomla database connection for playground queries.
     *
     * Uses an explicitly injected database when provided, otherwise
     * obtains DatabaseInterface from the Joomla dependency container
     * and checks its type before returning it.
     *
     * @param DatabaseInterface|null $database Optional injected service.
     * @return DatabaseInterface Valid Joomla database connection.
     * @throws \RuntimeException If the container service is invalid.
     */
    private static function joomlaDatabase(?DatabaseInterface $database = null): DatabaseInterface
    {
        if ($database !== null) {
            return $database;
        }

        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement PlaygroundTicker database service is unavailable.', 500);
        }

        return $database;
    }
    }
}


if (!class_exists('modJSMPlaygroundTickerHelper', false)) {
    class_alias('modJSMPlaygroundTicker', 'modJSMPlaygroundTickerHelper');
}

if (!class_exists('ModSportsmanagementPlaygroundTickerHelper', false)) {
    class_alias('modJSMPlaygroundTicker', 'ModSportsmanagementPlaygroundTickerHelper');
}
