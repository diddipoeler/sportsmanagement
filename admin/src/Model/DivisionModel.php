<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 *
 * Joomla 5/6 migration adapter.
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

if (!class_exists('JSMModelAdmin')) {
    \JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.model', JPATH_ADMINISTRATOR);
}

if (!class_exists('sportsmanagementModeldivision')) {
    \JLoader::import('components.com_sportsmanagement.models.division', JPATH_ADMINISTRATOR);
}

/**
 * Namespaced adapter for the existing Division model.
 */
class DivisionModel extends \sportsmanagementModeldivision
{
}
