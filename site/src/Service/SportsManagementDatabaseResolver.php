<?php
/**
 * Joomla 5/6 SportsManagement site database resolver.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\Database\DatabaseFactory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

final class SportsManagementDatabaseResolver
{
    public static function resolve(DatabaseInterface $joomlaDatabase, int $selector = 0): DatabaseInterface
    {
        $params = ComponentHelper::getParams('com_sportsmanagement');
        $forceExternal = $selector === 1;

        // Preserve sportsmanagementHelper::getDBConnection(): the external
        // database is selected when either the component-wide setting is on
        // or the caller explicitly requests it.
        if (!(bool) $params->get('cfg_which_database', 0) && !$forceExternal) {
            return $joomlaDatabase;
        }

        try {
            $external = self::connectExternal($params);

            // A connection can succeed while the entitlement query fails
            // (missing profile table, permissions or unreachable server).
            return self::hasExternalAccess($external, $params) ? $external : $joomlaDatabase;
        } catch (\Throwable) {
            return $joomlaDatabase;
        }
    }

    private static function connectExternal(Registry $params): DatabaseInterface
    {
        $factory = new DatabaseFactory();

        return $factory->getDriver(self::normaliseDriver((string) $params->get('jsm_dbtype', '')), [
            'host' => (string) $params->get('jsm_host', ''),
            'user' => (string) $params->get('jsm_user', ''),
            'password' => (string) $params->get('jsm_password', ''),
            'database' => (string) $params->get('jsm_db', ''),
            'prefix' => (string) $params->get('jsm_dbprefix', ''),
            'select' => true,
        ]);
    }

    /**
     * Preserve the legacy external-database entitlement check without relying
     * on removed Joomla Factory/JDatabase APIs.
     */
    private static function hasExternalAccess(DatabaseInterface $db, Registry $params): bool
    {
        $userId = (int) $params->get('jsm_server_user', 0);

        if ($userId <= 0) {
            return false;
        }

        $profilePattern = 'jsmprofile.%';
        $query = $db->createQuery()
            ->select([
                $db->quoteName('profile_key'),
                $db->quoteName('profile_value'),
            ])
            ->from($db->quoteName('#__user_profiles'))
            ->where($db->quoteName('user_id') . ' = :userId')
            ->where($db->quoteName('profile_key') . ' LIKE :profilePattern')
            ->bind(':userId', $userId, ParameterType::INTEGER)
            ->bind(':profilePattern', $profilePattern, ParameterType::STRING);

        $db->setQuery($query);
        $profiles = $db->loadAssocList('profile_key') ?: [];

        $profileValue = static fn (string $key): string => trim((string) ($profiles[$key]['profile_value'] ?? ''));

        // User-profile values may be JSON-encoded; only explicit true/1 grants access.
        $rawAccess = $profileValue('jsmprofile.databaseaccess');
        $decodedAccess = json_decode($rawAccess, true);

        if (!in_array($decodedAccess, [true, 1, '1'], true)) {
            return false;
        }

        $expectedSerial = (string) $params->get('jsm_user_serialnumber', '');
        $actualSerial = $profileValue('jsmprofile.serialnumber');

        // Two missing serial numbers must never grant external database access.
        if ($expectedSerial === '' || $actualSerial === ''
            || !hash_equals($expectedSerial, $actualSerial)) {
            return false;
        }

        $accessFrom = $profileValue('jsmprofile.access_from');
        $accessTo = $profileValue('jsmprofile.access_to');

        if ($accessFrom === '' || $accessTo === '') {
            return false;
        }

        $from = self::timestamp($accessFrom);
        $to = self::timestamp($accessTo);
        $now = time();

        return $from !== null && $to !== null && $now >= $from && $now <= $to;
    }

    private static function timestamp(string $value): ?int
    {
        $value = trim($value);

        // Zero dates are placeholders, not valid entitlement boundaries.
        // Never interpret them as the current time and accidentally grant access.
        if ($value === '' || str_starts_with($value, '0000-00-00')) {
            return null;
        }

        $timestamp = strtotime($value);

        return $timestamp === false ? null : $timestamp;
    }

    private static function normaliseDriver(string $driver): string
    {
        $driver = strtolower(trim($driver));

        return match ($driver) {
            'postgresql' => 'pgsql',
            '' => 'mysqli',
            default => $driver,
        };
    }
}
