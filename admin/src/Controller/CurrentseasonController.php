<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 *
 * Joomla 5/6 migration.
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

/**
 * Controller for the current-season view.
 */
class CurrentseasonController extends SportsManagementAdminController
{
    protected $view_list = 'currentseasons';
}
