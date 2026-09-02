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

use Joomla\CMS\MVC\Controller\FormController;

/**
 * Controller for cpanel-specific tasks.
 */
class CpanelController extends FormController
{
    /**
     * Kept for backwards-compatible task routing.
     */
    public function jqueryinstall(): void
    {
    }
}
