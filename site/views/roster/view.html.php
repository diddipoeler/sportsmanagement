<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
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

jimport('joomla.application.component.view');

/**
 * sportsmanagementViewRoster
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewRoster extends sportsmanagementView
{

	
	/**
	 * sportsmanagementViewRoster::init()
	 * 
	 * @return void
	 */
	function init()
	{
		
        sportsmanagementModelRoster::$seasonid = $this->project->season_id;

		$this->projectteam = $this->model->getProjectTeam();
        
        $this->lastseasondate = $this->model->getLastSeasonDate();
        
        $type = $this->jinput->getVar("type", 0);
        $typestaff = $this->jinput->getVar("typestaff", 0);
        if ( !$type )
        {
            $type = $this->config['show_players_layout'];
        }
        if ( !$typestaff )
        {
            $typestaff = $this->config['show_staff_layout'];
        }
        $this->type = $type;
        $this->typestaff = $typestaff;
        
        $this->config['show_players_layout'] = $type;
        $this->config['show_staff_layout'] = $typestaff;
        
		if ($this->projectteam)
		{
			$this->team = $this->model->getTeam();
			$this->rows = $this->model->getTeamPlayers(1);
			// events
			if ($this->config['show_events_stats'])
			{
				$this->positioneventtypes = $this->model->getPositionEventTypes();
				$this->playereventstats = $this->model->getPlayerEventStats();
			}
			//stats
			if ($this->config['show_stats'])
			{
				$this->stats = sportsmanagementModelProject::getProjectStats(0,0,sportsmanagementModelRoster::$cfg_which_database);
				$this->playerstats = $this->model->getRosterStats();
			}

            $this->stafflist = $this->model->getTeamPlayers(2);

			// Set page title
			$this->document->setTitle(JText::sprintf('COM_SPORTSMANAGEMENT_ROSTER_TITLE',$this->team->name));
		}
		else
		{
			// Set page title
			$this->document->setTitle(JText::sprintf('COM_SPORTSMANAGEMENT_ROSTER_TITLE', "Project team does not exist"));
		}
        
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$this->option.'/assets/css/'.$this->view.'.css'.'" type="text/css" />' ."\n";
        $this->document->addCustomTag($stylelink);
        

    // select roster view
    $opp_arr = array ();
    $opp_arr[] = JHTML :: _('select.option', "player_standard", JText :: _('COM_SPORTSMANAGEMENT_FES_ROSTER_PARAM_OPTION1_PLAYER_STANDARD'));
	$opp_arr[] = JHTML :: _('select.option', "player_card", JText :: _('COM_SPORTSMANAGEMENT_FES_ROSTER_PARAM_OPTION2_PLAYER_CARD'));
	$opp_arr[] = JHTML :: _('select.option', "player_johncage", JText :: _('COM_SPORTSMANAGEMENT_FES_ROSTER_PARAM_OPTION3_PLAYER_CARD'));

	$lists['type'] = $opp_arr;
  // select staff view
    $opp_arr = array ();
    $opp_arr[] = JHTML :: _('select.option', "staff_standard", JText :: _('COM_SPORTSMANAGEMENT_FES_ROSTER_PARAM_OPTION1_STAFF_STANDARD'));
	$opp_arr[] = JHTML :: _('select.option', "staff_card", JText :: _('COM_SPORTSMANAGEMENT_FES_ROSTER_PARAM_OPTION2_STAFF_CARD'));
	$opp_arr[] = JHTML :: _('select.option', "staff_johncage", JText :: _('COM_SPORTSMANAGEMENT_FES_ROSTER_PARAM_OPTION3_STAFF_CARD'));

	$lists['typestaff'] = $opp_arr;
	$this->lists = $lists;
    
    if ( !isset($this->config['table_class']) )
        {
            $this->config['table_class'] = 'table';
        }

	}

}
?>