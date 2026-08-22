<?php
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\Database\DatabaseFactory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class SportsManagementDatabaseResolver
{
    public static function resolve(DatabaseInterface $joomlaDatabase, int $selector): DatabaseInterface
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
        } catch (\Throwable) {
            return $joomlaDatabase;
        }

        return self::hasExternalAccess($external, $params) ? $external : $joomlaDatabase;
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

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('profile_key'),
                $db->quoteName('profile_value'),
            ])
            ->from($db->quoteName('#__user_profiles'))
            ->where($db->quoteName('user_id') . ' = ' . $userId)
            ->where($db->quoteName('profile_key') . ' LIKE ' . $db->quote('jsmprofile.%'));
        $db->setQuery($query);
        $profiles = $db->loadAssocList('profile_key') ?: [];

        $profileValue = static fn (string $key): string => trim((string) ($profiles[$key]['profile_value'] ?? ''));

        if (!(bool) $profileValue('jsmprofile.databaseaccess')) {
            return false;
        }

        $expectedSerial = (string) $params->get('jsm_user_serialnumber', '');
        $actualSerial = $profileValue('jsmprofile.serialnumber');

        if (!hash_equals($expectedSerial, $actualSerial)) {
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

        if ($value === '0000-00-00 00:00:00' || $value === '0000-00-00 15:30:00') {
            return time();
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
