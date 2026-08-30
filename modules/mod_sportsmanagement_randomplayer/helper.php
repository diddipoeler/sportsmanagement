<?php
/**
 * Legacy helper bridge for third-party overrides.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementRandomPlayer\Site\Helper\RandomPlayerHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

if (!class_exists(RandomPlayerHelper::class)) {
    require_once __DIR__ . '/src/Helper/RandomPlayerHelper.php';
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
