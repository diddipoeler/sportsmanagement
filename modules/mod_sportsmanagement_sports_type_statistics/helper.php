<?php
/**
 * SportsManagement legacy helper bridge for third-party template overrides.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Module\SportsManagementSportsTypeStatistics\Site\Helper\SportsTypeStatisticsHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

$nativeDependencies = [
    SportsManagementDatabaseResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
    SportsTypeStatisticsHelper::class => __DIR__ . '/src/Helper/SportsTypeStatisticsHelper.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

foreach ([
    SportsManagementDatabaseResolver::class,
    SportsTypeStatisticsHelper::class,
] as $requiredClass) {
    if (!class_exists($requiredClass)) {
        throw new \RuntimeException(
            'SportsManagement Sports Type Statistics dependency could not be loaded: ' . $requiredClass,
            500
        );
    }
}

if (!class_exists('modJSMSportsHelper', false)) {
    final class modJSMSportsHelper
    {
        /**
         * Retrieve sport-type statistics in the historical module structure.
         *
         * Converts module settings to Joomla Registry, loads the native
         * statistics data and maps its count and sport-type fields back
         * onto the keys expected by older template overrides. Uses the
         * validated sport record ID rather than loosely casting untrusted
         * module settings into compatibility array keys. Empty or malformed
         * sport or database selectors return without opening Joomla's database service.
         *
         * @param Registry|array $params Legacy settings passed by reference.
         * @param DatabaseInterface|null $database Optional database override.
         * @return array Legacy sport type and counter entries.
         * @throws \RuntimeException If the database cannot be resolved.
         */
        public static function getData(&$params, ?DatabaseInterface $database = null): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) $params);

            // An unset or invalid sport cannot produce records. Return the
            // historical empty layout payload without resolving an optional
            // external or container database service.
            if (self::selectedSportId($registry->get('sportstypes', 0)) === 0
                || self::databaseSelector($registry->get('cfg_which_database', 0)) === null) {
                return ['sportstype' => [], 'projectscount' => 0];
            }

            $database = self::joomlaDatabase($database);
            $data = (new SportsTypeStatisticsHelper())->getData($registry, $database);
            $legacy = ['sportstype' => []];

            // The native helper validates the requested selector and returns
            // the actual selected sport. Never cast arbitrary legacy settings
            // to a new sport ID when building compatibility array keys.
            $sportType = $data['sportstype'] ?? null;
            $rawSportTypeId = is_object($sportType) ? ($sportType->id ?? null) : null;
            $sportTypeId = 0;

            if (is_int($rawSportTypeId) && $rawSportTypeId > 0) {
                $sportTypeId = $rawSportTypeId;
            } elseif (is_string($rawSportTypeId) && $rawSportTypeId !== ''
                && strlen($rawSportTypeId) <= 128
                && preg_match('/^[0-9]+$/D', $rawSportTypeId) === 1) {
                $sportTypeId = filter_var(ltrim($rawSportTypeId, '0'), FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 1],
                ]) ?: 0;
            }

            if ($sportTypeId > 0 && is_object($sportType)) {
                $legacy['sportstype'][$sportTypeId] = $sportType;
            }

            $map = [
                'projects' => 'projectscount',
                'leagues' => 'leaguescount',
                'seasons' => 'seasonscount',
                'playgrounds' => 'playgroundscount',
                'clubs' => 'clubscount',
                'teams' => 'projectteamscount',
                'players' => 'personscount',
                'divisions' => 'projectdivisionscount',
                'rounds' => 'projectroundscount',
                'matches' => 'projectmatchescount',
                'player_events' => 'projectmatcheseventscount',
                'player_stats' => 'projectmatchesstatscount',
            ];

            foreach ($map as $nativeKey => $legacyKey) {
                if (array_key_exists($nativeKey, $data['counts'] ?? [])) {
                    $legacy[$legacyKey] = (int) $data['counts'][$nativeKey];
                }
            }

            return $legacy;
        }

        /**
         * Parse the configured sport ID before opening a legacy database.
         *
         * Accepts complete positive decimal IDs from old module settings,
         * including zero-padded strings, and rejects boolean flags, arrays,
         * suffixes and numbers exceeding the PHP integer range.
         *
         * @param mixed $raw Legacy sport selection value.
         * @return int Positive sport ID, or zero for missing/invalid input.
         */
        private static function selectedSportId(mixed $raw): int
        {
            if (is_int($raw)) {
                return $raw > 0 ? $raw : 0;
            }

            if (!is_string($raw) || $raw === '' || strlen($raw) > 128
                || preg_match('/^[0-9]+$/D', $raw) !== 1) {
                return 0;
            }

            $digits = ltrim($raw, '0');

            if ($digits === '') {
                return 0;
            }

            $id = filter_var($digits, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1],
            ]);

            return $id === false ? 0 : $id;
        }

        /**
         * Validate the configured Joomla or external statistics database.
         *
         * Old module overrides may supply zero-padded 0/1 selectors. Reject
         * any other value before invoking the Joomla database container:
         * native statistics queries accept only the default connection (0)
         * or the explicitly configured external connection (1).
         *
         * @param mixed $raw Raw cfg_which_database module setting.
         * @return int|null Supported connection selector, or null if invalid.
         */
        private static function databaseSelector(mixed $raw): ?int
        {
            if ($raw === 0 || $raw === 1) {
                return $raw;
            }

            if (!is_string($raw) || $raw === '' || strlen($raw) > 64
                || preg_match('/^0*[01]$/D', $raw) !== 1) {
                return null;
            }

            return str_ends_with($raw, '1') ? 1 : 0;
        }

        /**
         * Resolve the Joomla database service for sport-type statistics.
         *
         * Returns the supplied database or resolves DatabaseInterface
         * through Joomla's dependency injection container, checking
         * the service before using it for queries. Wraps container errors
         * without leaking potentially sensitive exception text.
         *
         * @param DatabaseInterface|null $database Optional database override.
         * @return DatabaseInterface Valid Joomla database service.
         * @throws \RuntimeException If the service is unavailable.
         */
        private static function joomlaDatabase(?DatabaseInterface $database = null): DatabaseInterface
        {
            if ($database !== null) {
                return $database;
            }

            try {
                $database = Factory::getContainer()->get(DatabaseInterface::class);
            } catch (\Throwable $exception) {
                // Container exceptions may expose credentials and DSN details.
                // Preserve the cause for debugging, not its text for visitors.
                throw new \RuntimeException(
                    'SportsManagement Sports Type Statistics database service is unavailable.',
                    500,
                    $exception
                );
            }

            if (!$database instanceof DatabaseInterface) {
                throw new \RuntimeException('SportsManagement Sports Type Statistics database service is unavailable.', 500);
            }

            return $database;
        }
    }
}
