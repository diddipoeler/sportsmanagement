<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');




/**
 * sportsmanagementViewCurrentseasons
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewCurrentseasons extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewCurrentseasons::init()
	 * 
	 * @return void
	 */
	public function init ()
	{
        
        //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->items,true).'</pre>'),'Notice');
        
        if ( $this->items )
        {
        foreach ($this->items as $item)
	{
	   $item->count_projectdivisions = 0;
		$mdlProjectDivisions = JModelLegacy::getInstance("divisions", "sportsmanagementModel");
		$item->count_projectdivisions = $mdlProjectDivisions->getProjectDivisionsCount($item->id);
		
		$item->count_projectpositions = 0;
		$mdlProjectPositions = JModelLegacy::getInstance("Projectposition", "sportsmanagementModel");
		$item->count_projectpositions = $mdlProjectPositions->getProjectPositionsCount($item->id);
		
		$item->count_projectreferees = 0;
		$mdlProjectReferees = JModelLegacy::getInstance("Projectreferees", "sportsmanagementModel");
		$item->count_projectreferees = $mdlProjectReferees->getProjectRefereesCount($item->id);
		
		$item->count_projectteams = 0;
		$mdlProjecteams = JModelLegacy::getInstance("Projectteams", "sportsmanagementModel");
		$item->count_projectteams = $mdlProjecteams->getProjectTeamsCount($item->id);
        
        $item->count_matchdays = 0;
		$mdlRounds = JModelLegacy::getInstance("Rounds", "sportsmanagementModel");
		$item->count_matchdays = $mdlRounds->getRoundsCount($item->id);
	   
       }
       }
 
	}

	/**
	* Add the page title and toolbar.
	*
	* @since	1.6
	*/
	protected function addToolbar()
	{

	// Set toolbar items for the page
		$this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_TITLE');
$this->icon = 'currentseason';

        
        parent::addToolbar();
	}
}
?>
