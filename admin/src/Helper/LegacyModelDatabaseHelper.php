<?php
/**
 * Joomla 5/6 database bridge for remaining legacy administrator models.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

/**
 * Preserve legacy model database properties through native Joomla services.
 */
final class LegacyModelDatabaseHelper
{
    /**
     * Resolve the selected authorised SportsManagement administrator database.
     *
     * Gets Joomla's active database from its dependency-injection container,
     * reads the raw request selector (or the stored administrator choice),
     * and rejects malformed values instead of coercing them through getInt().
     * Delegates entitlement verification and fallback behaviour to the
     * existing native SportsManagementDatabaseResolver. Does not load or
     * reference the removed global sportsmanagementHelper class.
     *
     * @return DatabaseInterface Selected Joomla or external database driver.
     * @throws \RuntimeException If no Joomla administrator/DB service exists.
     * @throws \InvalidArgumentException If the selected database is malformed.
     */
    public static function resolve(): DatabaseInterface
    {
        $app = SportsManagementAdministratorApplicationResolver::resolve();
        // getInt() would silently convert malformed request input to another
        // database choice. Validate the raw value before using the resolver.
        $requestedSelector = $app->getInput()->get('cfg_which_database', null, 'raw');
        $rawSelector = $requestedSelector ?? $app->getUserState('com_sportsmanagement.cfg_which_database', 0);
        $selector = self::databaseSelector($rawSelector);

        if ($selector === null) {
            throw new \InvalidArgumentException('Invalid SportsManagement administrator database selector.', 400);
        }
        $fallback = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$fallback instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement administrator database service is unavailable.', 500);
        }

        return (new SportsManagementDatabaseResolver())->resolve($selector, $fallback);
    }
    /**
     * Parse the requested administrator database choice without an unsafe cast.
     *
     * Accepts integer zero/one and their zero-padded decimal string forms,
     * rejecting booleans, arrays, whitespace and unrelated numeric values.
     * An invalid request must not become a different database selection.
     *
     * @param mixed $value Raw input or stored administrator preference.
     * @return int|null Database choice 0/1, or null when malformed.
     */
    private static function databaseSelector(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value === 0 || $value === 1 ? $value : null;
        }

        if (!is_string($value) || strlen($value) > 64
            || preg_match('/^0*([01])$/D', $value, $match) !== 1) {
            return null;
        }

        return (int) $match[1];
    }
}
