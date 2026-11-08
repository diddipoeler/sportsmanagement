<?php
/**
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 *
 * Joomla 5/6 migration.
 */

namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

/** Native Joomla 5/6 controller for the events ranking view. */
final class EventsrankingController extends BaseController
{
    /**
     * Display the event rankings using Joomla's native MVC controller.
     *
     * Forwards Joomla's cache decision and approved URL parameters to the
     * inherited display implementation; the native view renders the result.
     *
     * @param bool $cachable Whether the response can use Joomla page caching.
     * @param array $urlparams Permitted parameters for cacheable page output.
     * @return mixed The return value from the parent display controller.
     */
    public function display($cachable = false, $urlparams = [])
    {
        return parent::display($cachable, $urlparams);
    }
}
