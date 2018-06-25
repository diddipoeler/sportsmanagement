<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage teamperson
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * sportsmanagementViewTeamPerson
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewTeamPerson extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewTeamPerson::init()
	 * 
	 * @return
	 */
	public function init ()
	{
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

        $this->team_id = $this->app->getUserState( "$this->option.team_id", '0' );
        $this->_persontype = $this->app->getUserState( "$this->option.persontype", '0' );
        $this->project_team_id = $this->app->getUserState( "$this->option.project_team_id", '0' );
        

		$this->project_id = $this->app->getUserState( "$this->option.pid", '0' );
		$this->season_id = $this->app->getUserState( "$this->option.season_id", '0' );
               
        
		$mdlProject = JModelLegacy::getInstance("Project", "sportsmanagementModel");
		$project = $mdlProject->getProject($this->project_id);
		$this->project = $project;
        
		if ( isset($this->item->projectteam_id) )
		{
		$project_team = $mdlProject->getProjectTeam($this->item->projectteam_id);
		$this->project_team = $project_team;
		}
        
		$mdlPerson = JModelLegacy::getInstance("Person", "sportsmanagementModel");
		$project_person = $mdlPerson->getPerson($this->item->person_id);
        
	 //build the html options for position
        $position_id = array();        
$mdlPositions = JModelLegacy::getInstance('Positions', 'sportsmanagementModel');
$project_ref_positions = $mdlPositions->getProjectPositions($this->project_id, 1);
if ($project_ref_positions) {
            $position_id = array_merge($position_id, $project_ref_positions);
        }
$project_ref_positions = $mdlPositions->getProjectPositions($this->project_id, 2);
if ($project_ref_positions) {
            $position_id = array_merge($position_id, $project_ref_positions);
        }
      
/**
 * name für titel setzen
 */
        $this->item->name = $project_person->lastname.' - '.$project_person->firstname;
        
        $this->project_person = $project_person;
        
/**
 * personendaten setzen
 */
        $this->form->setValue('position_id', null, $project_person->position_id);
        $this->form->setValue('projectteam_id', null, $this->project_team_id);
		$this->form->setValue('injury', null, $project_person->injury);
		$this->form->setValue('injury_date', null, $project_person->injury_date);
		$this->form->setValue('injury_end', null, $project_person->injury_end);
		$this->form->setValue('injury_detail', null, $project_person->injury_detail);
		$this->form->setValue('injury_date_start', null, $project_person->injury_date_start);
		$this->form->setValue('injury_date_end', null, $project_person->injury_date_end);
        
		$this->form->setValue('suspension', null, $project_person->suspension);
		$this->form->setValue('suspension_date', null, $project_person->suspension_date);
		$this->form->setValue('suspension_end', null, $project_person->suspension_end);
		$this->form->setValue('suspension_detail', null, $project_person->suspension_detail);
		$this->form->setValue('susp_date_start', null, $project_person->susp_date_start);
		$this->form->setValue('susp_date_end', null, $project_person->susp_date_end);
        
		$this->form->setValue('away', null, $project_person->away);
		$this->form->setValue('away_date', null, $project_person->away_date);
		$this->form->setValue('away_end', null, $project_person->away_end);
		$this->form->setValue('away_detail', null, $project_person->away_detail);
		$this->form->setValue('away_date_start', null, $project_person->away_date_start);
		$this->form->setValue('away_date_end', null, $project_person->away_date_end);

$project_position_id = $this->form->getValue('project_position_id');		
if ( !$project_position_id )		
{
foreach($position_id as $items => $item) {
    if($item->position_id == $project_person->position_id) {
       $results = $item->value;
    }
} 	
$this->form->setValue('project_position_id', null, $results);
$this->app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_TEAMPERSON_PROJECT_POSITION'),'notice');	
}
        
		$extended = sportsmanagementHelper::getExtended($this->item->extended, 'teamperson');
		$this->extended = $extended;
		$this->lists = $lists;
        
		if ( JComponentHelper::getParams($this->option)->get('show_debug_info_backend') )
		{
		$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' project_id<br><pre>'.print_r($this->project_id,true).'</pre>'),'');
		$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _persontype<br><pre>'.print_r($this->_persontype,true).'</pre>'),'');
		$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' project_team_id<br><pre>'.print_r($this->project_team_id,true).'</pre>'),'');
		$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' team_id<br><pre>'.print_r($this->team_id,true).'</pre>'),'');
		$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' season_id<br><pre>'.print_r($this->season_id,true).'</pre>'),'');
        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' project_ref_positions<br><pre>'.print_r($project_ref_positions,true).'</pre>'),'');
		}
 
  
 
	}
    
    /**
	* Add the page title and toolbar.
	*
	* @since	1.6
	*/
	protected function addToolbar()
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
	   
		$jinput->set('hidemainmenu', true);
        
		if ( isset($this->item->projectteam_id) )
		{
		$app->setUserState( "$option.project_team_id", $this->item->projectteam_id );
		}
        
		$app->setUserState( "$option.pid", $this->project_id );
		$app->setUserState( "$option.team_id", $this->team_id );
		$app->setUserState( "$option.season_id", $this->season_id );
        
		$user = JFactory::getUser();
		$userId = $user->id;
		$isNew = $this->item->id == 0;
		$canDo = sportsmanagementHelper::getActions($this->item->id);
        
		if ( $this->_persontype == 1 )
        {
        JToolbarHelper::title($isNew ? JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMPLAYER_NEW') : JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMPLAYER_EDIT'), 'teamplayer');
		}
        elseif ( $this->_persontype == 2 )
        {
        JToolbarHelper::title($isNew ? JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMSTAFF_NEW') : JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMSTAFF_EDIT'), 'teamstaff');
		}

parent::addToolbar();
	}

}
?>
