<?php
/**
 * Compatibility facade for the Joomla 5/6 UEFA ranking module.
 *
 * @version    3.8.0
 * @author     diddipoeler
 * @copyright  (C) 2015-2026
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementUefaWertung\Site\Helper\UefaWertungHelper;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

if (!class_exists(UefaWertungHelper::class)) {
    require_once __DIR__ . '/src/Helper/UefaWertungHelper.php';
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
        /** @var SiteApplication $app */
        $app = Factory::getContainer()->get(SiteApplication::class);
        /** @var DatabaseInterface $database */
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        return (new UefaWertungHelper())->getData($registry, $app, $database);
    }
}
