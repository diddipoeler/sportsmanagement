<?php
/**
 * @version     5.6.0
 * @author      diddipoeler
 * @copyright   Copyright (C) diddipoeler
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
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
