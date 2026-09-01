<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseFactory;
use Joomla\Database\DatabaseInterface;

/**
 * Resolve the SportsManagement database without loading the legacy component helper.
 *
 * The external-database access checks intentionally preserve the historical
 * sportsmanagementHelper::getDBConnection() contract while using Joomla 5/6 APIs.
 */
final class SportsManagementDatabaseResolver
{
    public function resolve(mixed $whichDatabase = null, ?DatabaseInterface $fallback = null): DatabaseInterface
    {
        if ($fallback === null) {
            $app = Factory::getApplication();
            /** @var DatabaseInterface $fallback */
            $fallback = $app->getContainer()->get(DatabaseInterface::class);
        }

        $params = ComponentHelper::getParams('com_sportsmanagement');
        $forceExternal = $whichDatabase !== null
            && $whichDatabase !== ''
            && $whichDatabase !== false
            && (int) $whichDatabase === 1;

        if (!(bool) $params->get('cfg_which_database', 0) && !$forceExternal) {
            return $fallback;
        }

        $serverUserId = (int) $params->get('jsm_server_user', 0);

        // Historical behaviour: an external connection was only returned after
        // validating a configured server user/profile. Without it Joomla's DB wins.
        if ($serverUserId <= 0) {
            return $fallback;
        }

        try {
            $external = (new DatabaseFactory())->getDriver(
                $this->normaliseDriver((string) $params->get('jsm_dbtype', '')),
                [
                    'host' => (string) $params->get('jsm_host', ''),
                    'user' => (string) $params->get('jsm_user', ''),
                    'password' => (string) $params->get('jsm_password', ''),
                    'database' => (string) $params->get('jsm_db', ''),
                    'prefix' => (string) $params->get('jsm_dbprefix', ''),
                ]
            );

            $profiles = $this->loadAccessProfile($external, $serverUserId);
        } catch (\Throwable $e) {
            $this->logFailure($e);

            return $fallback;
        }

        if (!$this->hasDatabaseAccess($profiles)) {
            Log::add('SportsManagement external database access is not enabled.', Log::ERROR, 'jsmerror');

            return $fallback;
        }

        $configuredSerial = (string) $params->get('jsm_user_serialnumber', '');
        $profileSerial = (string) ($profiles['jsmprofile.serialnumber']['profile_value'] ?? '');

        if ($profileSerial != $configuredSerial) {
            Log::add('SportsManagement external database serial number does not match.', Log::ERROR, 'jsmerror');

            return $fallback;
        }

        $accessFrom = (string) ($profiles['jsmprofile.access_from']['profile_value'] ?? '');
        $accessTo = (string) ($profiles['jsmprofile.access_to']['profile_value'] ?? '');

        // Historical behaviour required both range values before returning the
        // external connection.
        if ($accessFrom === '' || $accessTo === '') {
            Log::add('SportsManagement external database access range is incomplete.', Log::ERROR, 'jsmerror');

            return $fallback;
        }

        $from = $this->timestamp($accessFrom);
        $to = $this->timestamp($accessTo);
        $now = time();

        if ($from === null || $to === null || $now < $from || $now > $to) {
            Log::add('SportsManagement external database access range is not active.', Log::ERROR, 'jsmerror');

            return $fallback;
        }

        Log::add('SportsManagement external database access granted.', Log::INFO, 'jsmerror');

        return $external;
    }

    /** @return array<string, array<string, mixed>> */
    private function loadAccessProfile(DatabaseInterface $database, int $userId): array
    {
        // Joomla 5 ships joomla/database 3.x, whose portable query factory is
        // getQuery(true). Joomla 6 keeps this compatibility API as well.
        $query = $database->getQuery(true)
            ->select([
                $database->quoteName('up.profile_key'),
                $database->quoteName('up.profile_value'),
            ])
            ->from($database->quoteName('#__user_profiles', 'up'))
            ->where($database->quoteName('up.user_id') . ' = ' . $userId)
            ->where(
                $database->quoteName('up.profile_key')
                . ' LIKE ' . $database->quote('jsmprofile.%')
            );

        $database->setQuery($query);

        return $database->loadAssocList('profile_key') ?: [];
    }

    /** @param array<string, array<string, mixed>> $profiles */
    private function hasDatabaseAccess(array $profiles): bool
    {
        return (bool) ($profiles['jsmprofile.databaseaccess']['profile_value'] ?? false);
    }

    private function timestamp(string $value): ?int
    {
        $value = trim($value);

        if ($value === '' || $value === '0000-00-00 00:00:00' || $value === '0000-00-00 15:30:00') {
            return time();
        }

        $timestamp = strtotime($value);

        return $timestamp === false ? null : $timestamp;
    }

    private function normaliseDriver(string $driver): string
    {
        $driver = strtolower(trim($driver));

        return match ($driver) {
            'postgresql' => 'pgsql',
            '' => 'mysqli',
            default => $driver,
        };
    }

    private function logFailure(\Throwable $e): void
    {
        Log::add($e->getMessage(), Log::ERROR, 'jsmerror');
        Log::add((string) $e->getCode(), Log::ERROR, 'jsmerror');
    }
}
