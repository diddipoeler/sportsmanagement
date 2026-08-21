<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage admin
 * @file       controller.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

if (!defined('JSM_PATH'))
{
	define('JSM_PATH', 'components/com_sportsmanagement');
}

if (!class_exists('sportsmanagementHelper'))
{
	$helperFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php';

	if (is_file($helperFile))
	{
		require_once $helperFile;
	}
}

/**
 * SportsManagementController
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class SportsManagementController extends BaseController
{

	/**
	 * SportsManagementController::display()
	 *
	 * @param   bool  $cachable
	 * @param   bool  $urlparams
	 *
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$input = Factory::getApplication()->getInput();

		/**
		 * set default view if not set
		 */
		$input->set('view', $input->getCmd('view', 'cpanel'));
		$layout = $input->getCmd('layout', 'default');

		/**
		 * call parent behavior
		 */
		parent::display($cachable, $urlparams);

		if ($layout !== 'edit' && class_exists('sportsmanagementHelper'))
		{
			/**
			 * Set the submenu
			 */
			sportsmanagementHelper::addSubmenu('messages');
		}
	}
}
