<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 playground plan module helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Module\SportsManagementPlaygroundPlan\Site\Helper\PlaygroundPlanHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

if (!class_exists(SportsManagementSiteApplicationResolver::class)) {
    $resolverFile = JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php';

    if (is_file($resolverFile)) {
        require_once $resolverFile;
    }
}

if (!class_exists(PlaygroundPlanHelper::class)) {
    $nativeHelper = __DIR__ . '/src/Helper/PlaygroundPlanHelper.php';

    if (is_file($nativeHelper)) {
        require_once $nativeHelper;
    }
}

if (!class_exists(PlaygroundPlanHelper::class)) {
    throw new \RuntimeException('SportsManagement native PlaygroundPlan module helper could not be loaded.', 500);
}

if (!class_exists('modSportsmanagementPlaygroundplanHelper', false)) {
    final class modSportsmanagementPlaygroundplanHelper
    {
        public static function getData(&$params): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) $params);
            $app = self::siteApplication();

            return (new PlaygroundPlanHelper())->getData(
                $registry,
                $app,
                (object) ['module' => 'mod_sportsmanagement_playgroundplan', 'id' => 0]
            );
        }

        public static function getTeams($teamId, $teamFormat): string
        {
            $field = in_array((string) $teamFormat, ['name', 'middle_name', 'short_name'], true)
                ? (string) $teamFormat
                : 'name';
            $db = self::database();
            $query = $db->createQuery()
                ->select($db->quoteName($field))
                ->from($db->quoteName('#__sportsmanagement_team'))
                ->where($db->quoteName('id') . ' = ' . (int) $teamId);
            $db->setQuery($query, 0, 1);

            return (string) ($db->loadResult() ?? '');
        }

        public static function getTeamLogo($teamId, $logo = 'logo_big'): string
        {
            $field = in_array((string) $logo, ['logo_small', 'logo_middle', 'logo_big'], true)
                ? (string) $logo
                : 'logo_big';
            $db = self::database();
            $query = $db->createQuery()
                ->select($db->quoteName('c.' . $field))
                ->from($db->quoteName('#__sportsmanagement_team', 't'))
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_club', 'c')
                    . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id')
                )
                ->where($db->quoteName('t.id') . ' = ' . (int) $teamId);
            $db->setQuery($query, 0, 1);
            $value = trim((string) ($db->loadResult() ?? ''));

            if ($value !== '') {
                return $value;
            }

            $params = ComponentHelper::getParams('com_sportsmanagement');

            return (string) match ($field) {
                'logo_small' => $params->get('ph_logo_small', ''),
                'logo_middle' => $params->get('ph_logo_medium', ''),
                default => $params->get('ph_logo_big', ''),
            };
        }

        private static function siteApplication(): CMSApplicationInterface
        {
            return SportsManagementSiteApplicationResolver::resolve();
        }

        private static function database(): DatabaseInterface
        {
            /** @var DatabaseInterface $joomlaDatabase */
            $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
            $app = self::siteApplication();
            $selector = $app->getInput()->getInt(
                'cfg_which_database',
                (int) ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database', 0)
            );

            return SportsManagementDatabaseResolver::resolve($joomlaDatabase, $selector);
        }
    }
}
