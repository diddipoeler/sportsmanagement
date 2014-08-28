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

//require_once(JPATH_COMPONENT.DS.'helpers'.DS.'pagination.php');

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
class sportsmanagementViewRoster extends JViewLegacy
{

	/**
	 * sportsmanagementViewRoster::display()
	 * 
	 * @param mixed $tpl
	 * @return
	 */
	function display($tpl=null)
	{
		// Get a refrence of the page instance in joomla
		$document = JFactory::getDocument();
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
		$model = $this->getModel();
        
        sportsmanagementModelProject::setProjectID(JRequest::getInt('p',0));
		$config = sportsmanagementModelProject::getTemplateConfig($this->getName());

		$this->assign('project',sportsmanagementModelProject::getProject());
        $model->seasonid = $this->project->season_id;
		$this->assign('overallconfig',sportsmanagementModelProject::getOverallConfig());
		//$this->assignRef('staffconfig',$model->getTemplateConfig('teamstaff'));
		$this->assignRef('config',$config);
		$this->assign('projectteam',$model->getProjectTeam());
        
        $this->assign('lastseasondate',$model->getLastSeasonDate());
        
        $type = JRequest::getVar("type", 0);
        $typestaff = JRequest::getVar("typestaff", 0);
        if ( !$type )
        {
            $type = $this->config['show_players_layout'];
        }
        if ( !$typestaff )
        {
            $typestaff = $this->config['show_staff_layout'];
        }
        $this->assignRef('type',$type);
        $this->assignRef('typestaff',$typestaff);
        
        $this->config['show_players_layout'] = $type;
        $this->config['show_staff_layout'] = $typestaff;
        
		if ($this->projectteam)
		{
			$this->assign('team',$model->getTeam());
			$this->assign('rows',$model->getTeamPlayers(1));
			// events
			if ($this->config['show_events_stats'])
			{
				$this->assign('positioneventtypes',$model->getPositionEventTypes());
				$this->assign('playereventstats',$model->getPlayerEventStats());
			}
			//stats
			if ($this->config['show_stats'])
			{
				$this->assign('stats',sportsmanagementModelProject::getProjectStats());
				$this->assign('playerstats',$model->getRosterStats());
			}

			//$this->assign('stafflist',$model->getStaffList());
            $this->assign('stafflist',$model->getTeamPlayers(2));
            
            //$mainframe->enqueueMessage(JText::_('getTeamPlayers stafflist<br><pre>'.print_r($this->stafflist,true).'</pre>'),'');

			// Set page title
			$document->setTitle(JText::sprintf('COM_SPORTSMANAGEMENT_ROSTER_TITLE',$this->team->name));
		}
		else
		{
			// Set page title
			$document->setTitle(JText::sprintf('COM_SPORTSMANAGEMENT_ROSTER_TITLE', "Project team does not exist"));
		}
        
        $view = JRequest::getVar( "view") ;
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$option.'/assets/css/'.$view.'.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        

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
	$this->assignRef('lists', $lists);

//$this->assign('show_debug_info', JComponentHelper::getParams($option)->get('show_debug_info',0) );

		parent::display($tpl);
	}

}
?>