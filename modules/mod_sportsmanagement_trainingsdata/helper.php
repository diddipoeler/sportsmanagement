<?php
/**
 * SportsManagement TrainingsData legacy helper bridge for Joomla 5/6.
 *
 * @version    3.8.0
 * @author     diddipoeler
 * @copyright  Copyright (C) 2015 diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementTrainingsData\Site\Helper\TrainingsDataHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

if (!class_exists(TrainingsDataHelper::class)) {
    require_once __DIR__ . '/src/Helper/TrainingsDataHelper.php';
}

if (!class_exists('modJSMTrainingsData', false)) {
    final class modJSMTrainingsData
    {
        public static function getData($params): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) $params);
            /** @var DatabaseInterface $database */
            $database = Factory::getContainer()->get(DatabaseInterface::class);

            return (new TrainingsDataHelper())->getData($registry, $database);
        }
    }
}
