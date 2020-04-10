<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage updates
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

jimport('joomla.html.html.bootstrap');

/**
 * sportsmanagementViewUpdates
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewUpdates extends sportsmanagementView
{

	/**
	 * sportsmanagementViewUpdates::init()
	 *
	 * @return void
	 */
	public function init()
	{
		$this->app->setUserState($this->option . 'update_part', 0); // 0
		$filter_order     = $this->app->getUserStateFromRequest($this->option . 'updates_filter_order', 'filter_order', 'dates', 'cmd');
		$filter_order_Dir = $this->app->getUserStateFromRequest($this->option . 'updates_filter_order_Dir', 'filter_order_Dir', '', 'word');

		$db = sportsmanagementHelper::getDBConnection();

		if (version_compare(JSM_JVERSION, '4', 'eq'))
		{
			$uri = Uri::getInstance();
		}
		else
		{
			$uri = Factory::getURI();
		}

		$model       = $this->getModel();
		$versions    = $model->getVersions();
		$updateFiles = array();
		$lists       = array();
		$updateFiles = $model->loadUpdateFiles();

		// Table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order']     = $filter_order;
		$this->updateFiles  = $updateFiles;
		$this->request_url  = $uri->toString();
		$this->lists        = $lists;

	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar()
	{

		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_TITLE');
		$this->icon  = 'updates';

		parent::addToolbar();
	}

}

