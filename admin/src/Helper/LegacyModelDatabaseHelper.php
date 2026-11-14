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
     * reads the explicit request selector (or stored administrator choice),
     * and delegates entitlement verification plus fallback behaviour to the
     * existing native SportsManagementDatabaseResolver. Does not load or
     * reference the removed global sportsmanagementHelper class.
     *
     * @return DatabaseInterface Selected Joomla or external database driver.
     * @throws \RuntimeException If no Joomla administrator/DB service exists.
     */
    public static function resolve(): DatabaseInterface
    {
        $app = SportsManagementAdministratorApplicationResolver::resolve();
        $selector = $app->getInput()->getInt(
            'cfg_which_database',
            (int) $app->getUserState('com_sportsmanagement.cfg_which_database', 0)
        );
        $fallback = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$fallback instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement administrator database service is unavailable.', 500);
        }

        return (new SportsManagementDatabaseResolver())->resolve($selector, $fallback);
    }
}
