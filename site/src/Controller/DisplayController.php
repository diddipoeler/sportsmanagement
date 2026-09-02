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

/**
 * Default site controller for the modern Joomla dispatcher.
 *
 * The current legacy site controller contains no component-specific dispatch
 * logic, so it can be represented directly by a namespaced BaseController.
 * Task-specific legacy controllers are migrated separately.
 */
final class DisplayController extends BaseController
{
}
