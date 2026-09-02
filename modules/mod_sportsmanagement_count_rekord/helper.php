<?php
/**
 * Legacy compatibility bridge for the Joomla 5/6 SportsManagement count record module.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementCountRekord\Site\Helper\CountRekordHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

if (!class_exists(CountRekordHelper::class)) {
    require_once __DIR__ . '/src/Helper/CountRekordHelper.php';
}

if (!class_exists('modJSMStatistikRekordHelper', false)) {
    final class modJSMStatistikRekordHelper
    {
        public static function getData($params, $module): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) $params);
            Factory::getApplication();
            /** @var DatabaseInterface $database */
            $database = Factory::getContainer()->get(DatabaseInterface::class);

            return (new CountRekordHelper())->getData($registry, $module, $database);
        }
    }
}
