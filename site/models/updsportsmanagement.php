<?php
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// Include dependancy of the main model form
jimport('joomla.application.component.modelform');
// import Joomla modelitem library
jimport('joomla.application.component.modelitem');
// Include dependancy of the dispatcher
jimport('joomla.event.dispatcher');


/**
 * sportsmanagementModelUpdsportsmanagement
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
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

        $app = JFactory::getApplication('site');

        // Get the form.
		$form = $this->loadForm('com_v.updhelloworld', 'updv', array('control' => 'jform', 'load_data' => true));
		if (empty($form)) {
			return false;
		}
		return $form;

	}

	/**
	 * Get the message
	 * @return object The message to be displayed to the user
	 */
	function &getItem()
	{

		if (!isset($this->_item))
		{
			$cache = JFactory::getCache('com_sportsmanagement', '');
			$id = $this->getState('sportsmanagement.id');
			$this->_item =  $cache->get($id);
			if ($this->_item === false) {

                // Menu parameters
                $menuitemid = JFactory::getApplication()->input->getInt( 'Itemid' );  // this returns the menu id number so you can reference parameters
                $menu = JSite::getMenu();
                if ($menuitemid) {
                   $menuparams = $menu->getParams( $menuitemid );
                   $headingtxtcolor = $menuparams->get('headingtxtcolor');  // This shows how to get an individual parameter for use
                   $headingbgcolor = $menuparams->get('headingbgcolor');  // This shows how to get an individual parameter for use
                }
                $this->setState('menuparams', $menuparams);  // this sets the parameter values to the state for later use
			}
		}
		return $this->_item;

	}

	/**
	 * sportsmanagementModelUpdsportsmanagement::updItem()
	 * 
	 * @param mixed $data
	 * @return
	 */
	public function updItem($data)
	{
        // set the variables from the passed data
        $id = $data['id'];
        $greeting = $data['greeting'];

        // set the data into a query to update the record
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
        $query->clear();
		$query->update(' #__sportsmanagement ');
		$query->set(' greeting = '.$db->Quote($greeting) );
		$query->where(' id = ' . (int) $id );

		$db->setQuery((string)$query);

        if (!$db->query()) {
            JError::raiseError(500, $db->getErrorMsg());
        	return false;
        } else {
        	return true;
		}
	}
}
