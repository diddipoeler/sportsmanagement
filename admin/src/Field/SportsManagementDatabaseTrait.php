<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

trait SportsManagementDatabaseTrait
{
    protected function getSportsManagementDatabase(mixed $whichDatabase = null): DatabaseInterface
    {
        if (!class_exists('sportsmanagementHelper', false)) {
            $helperFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php';

            if (is_file($helperFile)) {
                require_once $helperFile;
            }
        }

        if (!class_exists('sportsmanagementHelper', false)) {
            throw new \RuntimeException('SportsManagement database helper is unavailable.');
        }

        $database = $whichDatabase === null || $whichDatabase === '' || $whichDatabase === false || (int) $whichDatabase === 0
            ? \sportsmanagementHelper::getDBConnection()
            : \sportsmanagementHelper::getDBConnection(true, $whichDatabase);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement database connection is unavailable.');
        }

        return $database;
    }
}
