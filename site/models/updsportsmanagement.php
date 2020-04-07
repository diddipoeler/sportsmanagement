<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       updsportsmanagement.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage models
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

// Include dependancy of the main model form
jimport('joomla.application.component.modelform');

// Import Joomla modelitem library
jimport('joomla.application.component.modelitem');

// Include dependancy of the dispatcher
jimport('joomla.event.dispatcher');


/**
 * sportsmanagementModelUpdsportsmanagement
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelUpdsportsmanagement extends JModelForm
{
	/**
	 * @var object item
	 */
	protected $item;

	/**
	 * Get the data for a new qualification
	 */
	public function getForm($data = array(), $loadData = true)
	{

		$app = Factory::getApplication('site');

		// Get the form.
		$form = $this->loadForm('com_v.updhelloworld', 'updv', array('control' => 'jform', 'load_data' => true));

		if (empty($form))
		{
			return false;
		}

		return $form;

	}

	/**
	 * Get the message
	 *
	 * @return object The message to be displayed to the user
	 */
	function &getItem()
	{

		if (!isset($this->_item))
		{
			$cache = Factory::getCache('com_sportsmanagement', '');
			$id = $this->getState('sportsmanagement.id');
			$this->_item = $cache->get($id);

			if ($this->_item === false)
			{
				// Menu parameters
				$menuitemid = Factory::getApplication()->input->getInt('Itemid');  // this returns the menu id number so you can reference parameters
				$menu = JSite::getMenu();

				if ($menuitemid)
				{
					$menuparams = $menu->getParams($menuitemid);
					$headingtxtcolor = $menuparams->get('headingtxtcolor');  // This shows how to get an individual parameter for use
					$headingbgcolor = $menuparams->get('headingbgcolor');  // This shows how to get an individual parameter for use
				}

				$this->setState('menuparams', $menuparams);  // This sets the parameter values to the state for later use
			}
		}

		return $this->_item;

	}

	/**
	 * sportsmanagementModelUpdsportsmanagement::updItem()
	 *
	 * @param   mixed $data
	 * @return
	 */
	public function updItem($data)
	{
		// Set the variables from the passed data
		$id = $data['id'];
		$greeting = $data['greeting'];

		// Set the data into a query to update the record
		$db        = $this->getDbo();
		$query    = $db->getQuery(true);
		$query->clear();
		$query->update(' #__sportsmanagement ');
		$query->set(' greeting = ' . $db->Quote($greeting));
		$query->where(' id = ' . (int) $id);

		$db->setQuery((string) $query);

		if (!$db->query())
		{
			return false;
		}
		else
		{
			return true;
		}
	}
}
