<?php
/**
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
