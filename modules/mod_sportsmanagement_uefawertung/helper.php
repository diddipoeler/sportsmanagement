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

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Module\SportsManagementUefaWertung\Site\Helper\UefaWertungHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

if (!class_exists(SportsManagementSiteApplicationResolver::class)) {
    $resolverFile = JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php';

    if (is_file($resolverFile)) {
        require_once $resolverFile;
    }
}

if (!class_exists(UefaWertungHelper::class)) {
    $nativeHelper = __DIR__ . '/src/Helper/UefaWertungHelper.php';

    if (is_file($nativeHelper)) {
        require_once $nativeHelper;
    }
}

if (!class_exists(UefaWertungHelper::class)) {
    throw new \RuntimeException('SportsManagement native UEFA ranking helper could not be loaded.', 500);
}

class modJSMUefaWERTUNG
{
    public static function getData($params): array
    {
        return self::result($params)['rankings'];
    }

    public static function getSeasonNames($params): array
    {
        return self::result($params)['seasons'];
    }

    private static function result($params): array
    {
        $registry = $params instanceof Registry ? $params : new Registry((array) $params);
        $app = SportsManagementSiteApplicationResolver::resolve();
        /** @var DatabaseInterface $database */
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        return (new UefaWertungHelper())->getData($registry, $app, $database);
    }
}
