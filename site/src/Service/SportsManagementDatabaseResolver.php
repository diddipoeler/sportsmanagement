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
        if ($selector !== 1) {
            return $joomlaDatabase;
        }

        $params = ComponentHelper::getParams('com_sportsmanagement');

        try {
            $external = self::connectExternal($params);
        } catch (\Throwable) {
            return $joomlaDatabase;
        }

        return self::hasExternalAccess($external, $params) ? $external : $joomlaDatabase;
    }

    private static function connectExternal(Registry $params): DatabaseInterface
    {
        $driver = trim((string) $params->get('jsm_dbtype', 'mysqli')) ?: 'mysqli';
        $factory = new DatabaseFactory();

        return $factory->getDriver($driver, [
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

        if ($profileValue('jsmprofile.databaseaccess') === '') {
            return false;
        }

        $expectedSerial = trim((string) $params->get('jsm_user_serialnumber', ''));
        $actualSerial = $profileValue('jsmprofile.serialnumber');

        if ($expectedSerial === '' || $actualSerial === '' || !hash_equals($expectedSerial, $actualSerial)) {
            return false;
        }

        $from = self::date($profileValue('jsmprofile.access_from'));
        $to = self::date($profileValue('jsmprofile.access_to'));

        if (!$from || !$to) {
            return false;
        }

        $now = new \DateTimeImmutable('now', $from->getTimezone());

        return $now >= $from && $now <= $to;
    }

    private static function date(string $value): ?\DateTimeImmutable
    {
        if ($value === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
