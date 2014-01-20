<?php 
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
class sportsmanagementViewRoster extends JView
{

	function display($tpl=null)
	{
		// Get a refrence of the page instance in joomla
		$document = JFactory::getDocument();
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
		$model = $this->getModel();
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