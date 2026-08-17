<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 *
 * Joomla 5/6 migration adapter.
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

if (!class_exists('sportsmanagementHelper')) {
    \JLoader::register(
        'sportsmanagementHelper',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php'
    );
}

if (!class_exists('JSMModelAdmin')) {
    \JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.model', JPATH_ADMINISTRATOR);
}

if (!class_exists('sportsmanagementModelclub')) {
    \JLoader::import('components.com_sportsmanagement.models.club', JPATH_ADMINISTRATOR);
}

/**
 * Namespaced adapter for the existing Club model.
 */
class ClubModel extends \sportsmanagementModelclub
{
}
