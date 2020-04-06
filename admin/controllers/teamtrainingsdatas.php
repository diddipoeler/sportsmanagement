<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       teamtrainingsdatas.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage controllers
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;

/**
 * sportsmanagementControllerteamtrainingsdata
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllerteamtrainingsdata extends JSMControllerAdmin
{

	/**
	 * Save the manual order inputs from the categories list page.
	 *
	 * @return void
	 * @since  1.6
	 */
	public function saveorder()
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Get the arrays from the Request
		$order    = Factory::getApplication()->input->getVar('order',    null, 'post', 'array');
		$originalOrder = explode(',', Factory::getApplication()->input->getString('original_order_values'));

		// Make sure something has changed
		if (!($order === $originalOrder))
		{
			parent::saveorder();
		}
		else
		{
			// Nothing to reorder
			$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));

			return true;
		}
	}


	/**
	 * Proxy for getModel.
	 *
	 * @since 1.6
	 */
	public function getModel($name = 'teamtrainingsdata', $prefix = 'sportsmanagementModel', $config = Array() )
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}




}
