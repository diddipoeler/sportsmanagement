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

defined('_JEXEC') or die('Restricted access');

//require_once(JPATH_COMPONENT.DS.'helpers'.DS.'pagination.php');

require_once(JPATH_SITE.DS.JSM_PATH.DS.'models'.DS.'player.php');

jimport('joomla.application.component.view');

/**
 * sportsmanagementViewRosteralltime
 * 
 * @package 
 * @author abcde
 * @copyright 2015
 * @version $Id$
 * @access public
 */
class sportsmanagementViewRosteralltime extends JViewLegacy
{

	/**
	 * sportsmanagementViewRosteralltime::display()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	function display($tpl=null)
	{
		// Get a refrence of the page instance in joomla
		$document = JFactory::getDocument();
		$model = $this->getModel();
        $user = JFactory::getUser();
		$config = sportsmanagementModelProject::getTemplateConfig($this->getName(),$model::$cfg_which_database);
        
        $state = $this->get('State');
		$items = $this->get('Items');
        
        $pagination	= $this->get('Pagination');

		$this->config = $config;
        $this->team = $model->getTeam();
    
    $this->playerposition = $model->getPlayerPosition();
    $this->project = sportsmanagementModelProject::getProject($model::$cfg_which_database,__METHOD__);
    $this->positioneventtypes = $model->getPositionEventTypes();

	$this->rows = $model->getTeamPlayers(1,$this->positioneventtypes,$items);
    	
        $this->items = $items;
		$this->state = $state;
		$this->user = $user;
		$this->pagination = $pagination;
        
		parent::display($tpl);
	}

}
?>