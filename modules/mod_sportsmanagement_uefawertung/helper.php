<?php
/**
 * Compatibility facade for the Joomla 5/6 UEFA ranking module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  (C) 2015-2026
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Module\SportsManagementUefaWertung\Site\Helper\UefaWertungHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

$nativeDependencies = [
    SportsManagementDatabaseResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
    SportsManagementSiteApplicationResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
    UefaWertungHelper::class => __DIR__ . '/src/Helper/UefaWertungHelper.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

foreach ([
    SportsManagementDatabaseResolver::class,
    SportsManagementSiteApplicationResolver::class,
    UefaWertungHelper::class,
] as $requiredClass) {
    if (!class_exists($requiredClass)) {
        throw new \RuntimeException(
            'SportsManagement UEFA ranking dependency could not be loaded: ' . $requiredClass,
            500
        );
    }
}

class modJSMUefaWERTUNG
{
    public static function getData($params, ?DatabaseInterface $database = null): array
    {
        return self::result($params, $database)['rankings'];
    }

    public static function getSeasonNames($params, ?DatabaseInterface $database = null): array
    {
        return self::result($params, $database)['seasons'];
    }

    private static function result($params, ?DatabaseInterface $database = null): array
    {
        $registry = $params instanceof Registry ? $params : new Registry((array) $params);
        $app = SportsManagementSiteApplicationResolver::resolve();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement UEFA ranking legacy helper requires the Joomla site application.', 500);
        }

        if ($database === null) {
            /** @var DatabaseInterface $database */
            $database = Factory::getContainer()->get(DatabaseInterface::class);
        }

        return (new UefaWertungHelper())->getData($registry, $app, $database);
    }
}
