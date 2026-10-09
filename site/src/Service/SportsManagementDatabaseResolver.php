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

        // Joomla stores profile values as JSON, including quoted strings.
        $profileValue = static function (string $key) use ($profiles): string {
            $raw = trim((string) ($profiles[$key]['profile_value'] ?? ''));

            if ($raw === '') {
                return '';
            }

            $decoded = json_decode($raw, true);

            return is_string($decoded) || is_numeric($decoded)
                ? trim((string) $decoded)
                : $raw;
        };

        // Treat JSON booleans and the common plain-text representations
        // consistently, but never grant access for "false" or other strings.
        $accessEnabled = strtolower($profileValue('jsmprofile.databaseaccess'));

        if (!in_array($accessEnabled, ['1', 'true'], true)) {
            return false;
        }

        $expectedSerial = trim((string) $params->get('jsm_user_serialnumber', ''));
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

        // An inverted range must never grant access, even when both
        // individual timestamps are otherwise valid.
        return $from !== null && $to !== null
            && $from <= $to
            && $now >= $from && $now <= $to;
    }

    private static function timestamp(string $value): ?int
    {
        $value = trim($value);

        // Zero dates are placeholders, not valid entitlement boundaries.
        // Never interpret them as the current time and accidentally grant access.
        if ($value === '' || str_starts_with($value, '0000-00-00')) {
            return null;
        }

        // Entitlements use complete database dates; strtotime() also accepts
        // relative expressions such as "tomorrow", which are not valid here.
        if (!preg_match('/^\d{4}-\d{2}-\d{2}(?: \d{2}:\d{2}:\d{2})?$/', $value)) {
            return null;
        }

        $format = strlen($value) === 10 ? '!Y-m-d' : '!Y-m-d H:i:s';
        $date = \DateTimeImmutable::createFromFormat($format, $value, new \DateTimeZone('UTC'));

        if (!$date instanceof \DateTimeImmutable
            || $date->format(strlen($value) === 10 ? 'Y-m-d' : 'Y-m-d H:i:s') !== $value) {
            return null;
        }

        return $date->getTimestamp();
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
