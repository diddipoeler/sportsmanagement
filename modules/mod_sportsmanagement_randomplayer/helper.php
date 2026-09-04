<?php
/**
 * Legacy compatibility facade for the native Joomla 5/6 random player module helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementRandomPlayer\Site\Helper\RandomPlayerHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

if (!class_exists(RandomPlayerHelper::class)) {
    $nativeHelper = __DIR__ . '/src/Helper/RandomPlayerHelper.php';

    if (is_file($nativeHelper)) {
        require_once $nativeHelper;
    }
}

if (!class_exists(RandomPlayerHelper::class)) {
    throw new \RuntimeException('SportsManagement native RandomPlayer module helper could not be loaded.', 500);
}

if (!class_exists('modJSMRandomplayerHelper', false)) {
    final class modJSMRandomplayerHelper
    {
        public static function getData(&$params): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) $params);
            /** @var DatabaseInterface $database */
            $database = Factory::getContainer()->get(DatabaseInterface::class);

            return (new RandomPlayerHelper())->getData($registry, $database);
        }
    }
}
