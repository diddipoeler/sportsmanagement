<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage roster
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

	$this->projectteam = $this->model->getProjectTeam( $this->config['team_picture_which'] );
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
