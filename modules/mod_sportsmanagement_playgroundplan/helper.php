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

use Joomla\CMS\Factory;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Module\SportsManagementPlaygroundPlan\Site\Helper\PlaygroundPlanHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

$nativeDependencies = [
    SiteRouteHelper::class => JPATH_SITE . '/components/com_sportsmanagement/src/Helper/SiteRouteHelper.php',
    SportsManagementDatabaseResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
    SportsManagementSiteApplicationResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
    PlaygroundPlanHelper::class => __DIR__ . '/src/Helper/PlaygroundPlanHelper.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

foreach ([
    SiteRouteHelper::class,
    SportsManagementDatabaseResolver::class,
    SportsManagementSiteApplicationResolver::class,
    PlaygroundPlanHelper::class,
] as $requiredClass) {
    if (!class_exists($requiredClass)) {
        throw new \RuntimeException(
            'SportsManagement PlaygroundPlan dependency could not be loaded: ' . $requiredClass,
            500
        );
    }
}

if (!class_exists('modSportsmanagementPlaygroundplanHelper', false)) {
    final class modSportsmanagementPlaygroundplanHelper
    {
        /**
         * Load the venue schedule using the native Joomla 5/6 playground plan helper.
         * Normalises legacy parameters and supplies the resolved site application and a fallback module descriptor.
         *
         * @param Registry|array $params Module parameters passed by reference.
         * @return array Playground plan entries and presentation data.
         */
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

        /**
         * Look up a team's requested display name by its numeric identifier.
         * Restricts the selectable name column to a fixed allowlist and binds the team ID as an integer, returning an empty string for invalid IDs.
         *
         * @param int|string $teamId Numeric team identifier.
         * @param string $teamFormat Allowed name, middle_name or short_name field.
         * @return string Team display name or an empty string.
         */
        public static function getTeams($teamId, $teamFormat): string
        {
            $field = in_array((string) $teamFormat, ['name', 'middle_name', 'short_name'], true)
                ? (string) $teamFormat
                : 'name';
            $teamId = (int) $teamId;

            if ($teamId <= 0) {
                return '';
            }

            $db = self::database();
            $query = $db->createQuery()
                ->select($db->quoteName($field))
                ->from($db->quoteName('#__sportsmanagement_team'))
                ->where($db->quoteName('id') . ' = :teamId')
                ->bind(':teamId', $teamId, ParameterType::INTEGER);
            $db->setQuery($query, 0, 1);

            return (string) ($db->loadResult() ?? '');
        }

        /**
         * Return the configured club logo for a sports management team.
         * Uses an allowlisted logo column and a bound team ID, then falls back to the component's placeholder image when no club logo exists.
         *
         * @param int|string $teamId Numeric team identifier.
         * @param string $logo Allowed club logo variant.
         * @return string Image path or configured fallback image path.
         */
        public static function getTeamLogo($teamId, $logo = 'logo_big'): string
        {
            $field = in_array((string) $logo, ['logo_small', 'logo_middle', 'logo_big'], true)
                ? (string) $logo
                : 'logo_big';
            $teamId = (int) $teamId;

            if ($teamId <= 0) {
                return '';
            }

            $db = self::database();
            $query = $db->createQuery()
                ->select($db->quoteName('c.' . $field))
                ->from($db->quoteName('#__sportsmanagement_team', 't'))
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_club', 'c')
                    . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id')
                )
                ->where($db->quoteName('t.id') . ' = :teamId')
                ->bind(':teamId', $teamId, ParameterType::INTEGER);
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

        /**
         * Resolve and validate the Joomla frontend application.
         * Prevents legacy playground-plan lookups from executing in the administrator application.
         *
         * @return CMSApplicationInterface Active site application.
         */
        private static function siteApplication(): CMSApplicationInterface
        {
            $app = SportsManagementSiteApplicationResolver::resolve();

            if (!$app->isClient('site')) {
                throw new \RuntimeException('SportsManagement PlaygroundPlan legacy bridge requires the Joomla site application.', 500);
            }

            return $app;
        }

        /**
         * Select the database connection configured for the current playground plan.
         * Reads the input override or component default and passes that selector to SportsManagementDatabaseResolver.
         *
         * @return DatabaseInterface Selected SportsManagement database connection.
         */
        private static function database(): DatabaseInterface
        {
            $app = self::siteApplication();
            $selector = $app->getInput()->getInt(
                'cfg_which_database',
                (int) ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database', 0)
            );

            return SportsManagementDatabaseResolver::resolve(self::joomlaDatabase(), $selector);
        }

        /**
         * Fetch the database driver through Joomla's dependency injection container.
         * Checks the returned service type so a missing or invalid service produces a clear error.
         *
         * @return DatabaseInterface Joomla database driver.
         */
        private static function joomlaDatabase(): DatabaseInterface
        {
            $database = Factory::getContainer()->get(DatabaseInterface::class);

            if (!$database instanceof DatabaseInterface) {
                throw new \RuntimeException(
                    'SportsManagement PlaygroundPlan database service is unavailable.',
                    500
                );
            }

            return $database;
        }
    }
}
