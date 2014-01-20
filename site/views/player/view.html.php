<?php 
defined('_JEXEC') or die('Restricted access');
 
jimport('joomla.application.component.view');

/**
 * sportsmanagementViewPlayer
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewPlayer extends JView
{

	function display($tpl=null)
	{
		// Get a refrence of the page instance in joomla
		$document = JFactory::getDocument();
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
		$model = $this->getModel();
        
        $model->projectid = JRequest::getInt( 'p', 0 );
		$model->personid = JRequest::getInt( 'pid', 0 );
		$model->teamplayerid = JRequest::getInt( 'pt', 0 );
        
		$config = sportsmanagementModelProject::getTemplateConfig($this->getName());
        

		$person = sportsmanagementModelPerson::getPerson();
		$nickname = isset($person->nickname) ? $person->nickname : "";
		if(!empty($nickname)){$nickname="'".$nickname."'";}
		$this->assign('isContactDataVisible',sportsmanagementModelPerson::isContactDataVisible($config['show_contact_team_member_only']));
		$project = sportsmanagementModelProject::getProject();
		$this->assignRef('project', $project);
		$this->assign('overallconfig',sportsmanagementModelProject::getOverallConfig());
		$this->assignRef('config',$config);
		$this->assignRef('person',$person);
		$this->assignRef('nickname',$nickname);
		$this->assign('teamPlayers',$model->getTeamPlayers());
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementViewPlayer teamPlayers<br><pre>'.print_r($this->teamPlayers,true).'</pre>'),'');
        
        $this->assign( 'checkextrafields', sportsmanagementHelper::checkUserExtraFields() );
//        $mainframe->enqueueMessage(JText::_('player checkextrafields -> '.'<pre>'.print_r($this->checkextrafields,true).'</pre>' ),'');
        if ( $this->checkextrafields )
        {
            $this->assignRef( 'extrafields', sportsmanagementHelper::getUserExtraFields($person->id) );
        }

		// Select the teamplayer that is currently published (in case the player played in multiple teams in the project)
		$teamPlayer = null;
		if (count($this->teamPlayers))
		{
			$currentProjectTeamId=0;
			foreach ($this->teamPlayers as $teamPlayer)
			{
				if ($teamPlayer->published == 1)
				{
					$currentProjectTeamId=$teamPlayer->projectteam_id;
					break;
				}
			}
			if ($currentProjectTeamId)
			{
				$teamPlayer = $this->teamPlayers[$currentProjectTeamId];
			}
		}
		$sportstype = $config['show_plcareer_sportstype'] ? sportsmanagementModelProject::getSportsType() : 0;
		$this->assignRef('teamPlayer',$teamPlayer);
		
        $this->assign('historyPlayer',$model->getPlayerHistory($sportstype, 'ASC'));
		$this->assign('historyPlayerStaff',$model->getPlayerHistoryStaff($sportstype, 'ASC'));
        
		$this->assign('AllEvents',$model->getAllEvents($sportstype));
		$this->assign('showediticon',sportsmanagementModelPerson::getAllowed($config['edit_own_player']));
		$this->assign('stats',sportsmanagementModelProject::getProjectStats());

		// Get events and stats for current project
		if ($config['show_gameshistory'])
		{
			$this->assign('games',$model->getGames());
			$this->assign('teams',sportsmanagementModelProject::getTeamsIndexedByPtid());
			$this->assign('gamesevents',$model->getGamesEvents());
			$this->assign('gamesstats',$model->getPlayerStatsByGame());
		}

		// Get events and stats for all projects where player played in (possibly restricted to sports type of current project)
		if ($config['show_career_stats'])
		{
			$this->assign('stats',$model->getStats());
			$this->assign('projectstats',$model->getPlayerStatsByProject($sportstype));
		}

		$extended = sportsmanagementHelper::getExtended($person->extended, 'person');
		$this->assignRef( 'extended', $extended );
        $form_value = $this->extended->getValue('COM_SPORTSMANAGEMENT_EXT_PERSON_PARENT_POSITIONS');
        // nebenposition vorhanden ?
        if ( $form_value )
        {
            $this->assignRef ('person_parent_positions', explode(",",$form_value) );
        }
        
        
        $form_value = $this->extended->getValue('COM_SPORTSMANAGEMENT_EXT_PERSON_POSITION');
        if ( $form_value )
        {
        $this->assignRef ('person_position', $form_value );
        }
        else
        {
        $mainframe->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_PERSON_NO_POSITION'),'Error');
        }
        
        $this->assignRef( 'hasDescription',$this->teamPlayer->notes);
        
        foreach ($this->extended->getFieldsets() as $fieldset)
	{
	$hasData = false;
    $fields = $this->extended->getFieldset($fieldset->name);
			foreach ($fields as $field)
			{
				// TODO: backendonly was a feature of JLGExtraParams, and is not yet available.
				//       (this functionality probably has to be added later)
				$value = $field->value;	// Remark: empty($field->value) does not work, using an extra local var does
				if (!empty($value)) // && !$field->backendonly
				{
					$hasData = true;
					break;
				}
			}   
	}
    $this->assignRef( 'hasExtendedData',$hasData);
    
    $hasStatus = false;
    if (	( isset($this->teamPlayer->injury) && $this->teamPlayer->injury > 0 ) ||
		( isset($this->teamPlayer->suspension) && $this->teamPlayer->suspension > 0 ) ||
		( isset($this->teamPlayer->away) && $this->teamPlayer->away > 0 ) )
    {
    $hasStatus = true;
    }
    $this->assignRef( 'hasStatus',$hasStatus);
    	
    $this->assign('show_debug_info', JComponentHelper::getParams($option)->get('show_debug_info',0) );
    $this->assign('use_joomlaworks', JComponentHelper::getParams($option)->get('use_joomlaworks',0) );
        
		if (isset($person))
		{
			$name = sportsmanagementHelper::formatName(null, $this->person->firstname, $this->person->nickname,  $this->person->lastname,  $this->config["name_format"]);
		}
		$this->assignRef('playername', $name);
		$document->setTitle(JText::sprintf('COM_SPORTSMANAGEMENT_PLAYER_INFORMATION', $name));
        $view = JRequest::getVar( "view") ;
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$option.'/assets/css/'.$view.'.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);

		parent::display($tpl);
	}

}
?>