<?php
/**
 * Joomla 5/6 SportsManagement database resolver.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseFactory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Resolve the SportsManagement database without loading the legacy component helper.
 *
 * The external-database access checks intentionally preserve the historical
 * sportsmanagementHelper::getDBConnection() contract while using Joomla 5/6 APIs.
 */
final class SportsManagementDatabaseResolver
{
    /**
     * Resolve an authorised Joomla or external SportsManagement connection.
     *
     * Only explicit database selection, an enabled profile, a matching
     * nonempty serial and a valid active access interval can authorise
     * external storage. Invalid settings fall back to Joomla.
     *
     * @param mixed $whichDatabase Legacy database selection.
     * @param DatabaseInterface|null $fallback Existing Joomla driver.
     * @return DatabaseInterface Approved connection or Joomla fallback.
     */
    public function resolve(mixed $whichDatabase = null, ?DatabaseInterface $fallback = null): DatabaseInterface
    {
        if ($fallback === null) {
            self::administratorApplication();
            $fallback = self::joomlaDatabase();
        }

        $params = ComponentHelper::getParams('com_sportsmanagement');
        $forceExternal = $whichDatabase === 1 || $whichDatabase === '1';

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

        if ($configuredSerial === '' || $profileSerial === ''
            || !hash_equals($configuredSerial, $profileSerial)) {
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

        if ($from === null || $to === null || $from > $to || $now < $from || $now > $to) {
            Log::add('SportsManagement external database access range is not active.', Log::ERROR, 'jsmerror');

            return $fallback;
        }

        Log::add('SportsManagement external database access granted.', Log::INFO, 'jsmerror');

        return $external;
    }

    /**
     * Resolve Joomla's administrator client for the default connection.
     * @return CMSApplicationInterface Active administrator application.
     */
    private static function administratorApplication(): CMSApplicationInterface
    {
        return SportsManagementAdministratorApplicationResolver::resolve();
    }

    /**
     * Retrieve the native Joomla database service from the container.
     * @return DatabaseInterface Joomla database connection.
     */
    private static function joomlaDatabase(): DatabaseInterface
    {
        /** @var DatabaseInterface $database */
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        return $database;
    }

    /**
     * Query the external profile values for the configured server user.
     * @param DatabaseInterface $database External driver.
     * @param int $userId Server user identifier.
     * @return array<string, array<string, mixed>> User profile values.
     */
    private function loadAccessProfile(DatabaseInterface $database, int $userId): array
    {
        $profilePattern = 'jsmprofile.%';
        $query = $database->createQuery()
            ->select([
                $database->quoteName('up.profile_key'),
                $database->quoteName('up.profile_value'),
            ])
            ->from($database->quoteName('#__user_profiles', 'up'))
            ->where($database->quoteName('up.user_id') . ' = :userId')
            ->where($database->quoteName('up.profile_key') . ' LIKE :profilePattern')
            ->bind(':userId', $userId, ParameterType::INTEGER)
            ->bind(':profilePattern', $profilePattern, ParameterType::STRING);

        $database->setQuery($query);

        return $database->loadAssocList('profile_key') ?: [];
    }

    /**
     * Read the explicit external database entitlement from a Joomla profile.
     * @param array<string, array<string, mixed>> $profiles Saved profile rows.
     * @return bool True only for an explicit enabled setting.
     */
    private function hasDatabaseAccess(array $profiles): bool
    {
        $raw = $profiles['jsmprofile.databaseaccess']['profile_value'] ?? null;
        if (!is_string($raw) && !is_int($raw) && !is_bool($raw)) {
            return false;
        }

        $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
        $value = $decoded === null ? $raw : $decoded;
        return $value === true || $value === 1 || $value === '1'
            || (is_string($value) && strtolower(trim($value)) === 'true');
    }

    /**
     * Parse a strict UTC external database entitlement boundary.
     * @param string $value ISO date or database datetime.
     * @return int|null Timestamp or null for malformed and zero dates.
     */
    private function timestamp(string $value): ?int
    {
        $value = trim($value);

        if ($value === '' || str_starts_with($value, '0000-00-00')
            || preg_match('/^\d{4}-\d{2}-\d{2}(?: \d{2}:\d{2}:\d{2})?$/D', $value) !== 1) {
            return null;
        }

        $format = strlen($value) === 10 ? '!Y-m-d' : '!Y-m-d H:i:s';
        $date = \DateTimeImmutable::createFromFormat($format, $value, new \DateTimeZone('UTC'));
        return $date instanceof \DateTimeImmutable
            && $date->format(strlen($value) === 10 ? 'Y-m-d' : 'Y-m-d H:i:s') === $value
                ? $date->getTimestamp()
                : null;
    }

    /**
     * Map legacy external database driver aliases to Joomla identifiers.
     * @param string $driver Legacy driver name.
     * @return string Joomla database driver identifier.
     */
    private function normaliseDriver(string $driver): string
    {
        $driver = strtolower(trim($driver));

        return match ($driver) {
            'postgresql' => 'pgsql',
            '' => 'mysqli',
            default => $driver,
        };
    }

    /**
     * Log external connection failures without passwords or SQL messages.
     * @param \Throwable $e Driver or entitlement failure.
     * @return void
     */
    private function logFailure(\Throwable $e): void
    {
        Log::add('SportsManagement external database failed (' . get_class($e) . ').', Log::ERROR, 'jsmerror');
    }
}
