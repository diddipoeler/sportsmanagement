<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\Database\DatabaseInterface;

abstract class SportsManagementListField extends ListField
{
    protected function getSportsManagementDatabase(): DatabaseInterface
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

        $database = \sportsmanagementHelper::getDBConnection();

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement database connection is unavailable.');
        }

        return $database;
    }
}
