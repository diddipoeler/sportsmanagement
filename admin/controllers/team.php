<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');
 

/**
 * sportsmanagementControllerteam
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllerteam extends JControllerForm
{

/**
	 * Class constructor.
	 *
	 * @param   array  $config  A named array of configuration variables.
	 *
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
        $this->app = JFactory::getApplication();
        $this->jinput = $this->app->input;
		$this->option = $this->jinput->getCmd('option');
        $this->club_id = $this->app->getUserState( "$this->option.club_id", '0' );
        
//        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getTask<br><pre>'.print_r($this->getTask() ,true).'</pre>'),'');
//        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' club_id<br><pre>'.print_r($this->club_id ,true).'</pre>'),'');


	}
    
    
    /**
     * sportsmanagementControllerteam::save()
     * 
     * @param mixed $key
     * @param mixed $urlVar
     * @return void
     */
    public function save ($key = null, $urlVar = null)
	{

//	   $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getTask<br><pre>'.print_r($this->getTask() ,true).'</pre>'),'');
		$data = JRequest::getVar('jform', array(), 'post', 'array');
        
    $result = parent::save($key, $urlVar);
    
    switch ($this->getTask())
				{
					case 'apply':
//						$this->setRedirect(
//								JRoute::_(
//										'index.php?option=' . $this->option . '&view=' . $this->view_item .
//												 $this->getRedirectToItemAppend($newEventId, $urlVar), false));
						break;
					case 'save2new':
//						$this->setRedirect(
//								JRoute::_(
//										'index.php?option=' . $this->option . '&view=' . $this->view_item .
//												 $this->getRedirectToItemAppend(null, $urlVar), false));
						break;
					default:
						$this->setRedirect(
								JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list. '&club_id=' .$this->club_id , false));
						break;
				}
    
    
    }    
    
    
    

}
