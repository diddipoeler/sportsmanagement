<?php
/**
 * Shared Joomla 5/6 database resolver trait for administrator form fields.
 *
 * @version   5.6.0
 * @author    diddipoeler
 * @copyright Copyright (C) diddipoeler
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Joomla\Database\DatabaseInterface;

trait SportsManagementDatabaseTrait
{
    /**
     * Resolve the configured SportsManagement database for a form field.
     * Delegates primary or alternate connection selection to the shared
     * administrator resolver and requires a Joomla database interface.
     * @param mixed $whichDatabase Optional database selector.
     * @return DatabaseInterface Resolved Joomla database connection.
     * @throws \RuntimeException If no compatible connection is available.
     */
    protected function getSportsManagementDatabase(mixed $whichDatabase = null): DatabaseInterface
    {
        $database = (new SportsManagementDatabaseResolver())->resolve($whichDatabase);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement database connection is unavailable.');
        }

        return $database;
    }
}
