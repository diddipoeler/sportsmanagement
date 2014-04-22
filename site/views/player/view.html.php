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

	/**
	 * sportsmanagementViewPlayer::display()
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
        
        $model->projectid = JRequest::getInt( 'p', 0 );
		$model->personid = JRequest::getInt( 'pid', 0 );
		$model->teamplayerid = JRequest::getInt( 'pt', 0 );
        
        sportsmanagementModelProject::setProjectID(JRequest::getInt('p',0));
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
			$currentProjectTeamId = 0;
			foreach ($this->teamPlayers as $teamPlayer)
			{
				if ($teamPlayer->published == 1)
				{
					$currentProjectTeamId = $teamPlayer->projectteam_id;
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
        
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' showediticon<br><pre>'.print_r($this->showediticon,true).'</pre>'),'Notice');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' stats<br><pre>'.print_r($this->stats,true).'</pre>'),'');
        }

		// Get events and stats for current project
		if ($config['show_gameshistory'])
		{
			$this->assign('games',$model->getGames());
			$this->assign('teams',sportsmanagementModelProject::getTeamsIndexedByPtid());
			$this->assign('gamesevents',$model->getGamesEvents());
			$this->assign('gamesstats',$model->getPlayerStatsByGame());
		}
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' gamesstats<br><pre>'.print_r($this->gamesstats,true).'</pre>'),'');
        }

		// Get events and stats for all projects where player played in (possibly restricted to sports type of current project)
		if ($config['show_career_stats'])
		{
			$this->assign('stats',$model->getStats());
			$this->assign('projectstats',$model->getPlayerStatsByProject($sportstype));
		}

		$extended = sportsmanagementHelper::getExtended($person->extended, 'person');
		$this->assignRef( 'extended', $extended );
        unset($form_value);
        $form_value = $this->extended->getValue('COM_SPORTSMANAGEMENT_EXT_PERSON_PARENT_POSITIONS');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' extended<br><pre>'.print_r($this->extended,true).'</pre>'),'');
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' COM_SPORTSMANAGEMENT_EXT_PERSON_PARENT_POSITIONS<br><pre>'.print_r($form_value,true).'</pre>'),'');
        }
        
        // nebenposition vorhanden ?
        $this->assignRef ('person_parent_positions', $form_value );
//        if ( $form_value )
//        {
//            $this->assignRef ('person_parent_positions', explode(",",$form_value) );
//        }
        
        unset($form_value);
        $form_value = $this->extended->getValue('COM_SPORTSMANAGEMENT_EXT_PERSON_POSITION');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' COM_SPORTSMANAGEMENT_EXT_PERSON_POSITION<br><pre>'.print_r($form_value,true).'</pre>'),'');
        }
        
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
    	
    //$this->assign('show_debug_info', JComponentHelper::getParams($option)->get('show_debug_info',0) );
    //$this->assign('use_joomlaworks', JComponentHelper::getParams($option)->get('use_joomlaworks',0) );
        
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