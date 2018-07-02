<?php

/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage teampersons
 */
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementViewteampersons
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewteampersons extends sportsmanagementView {

    /**
     * sportsmanagementViewteampersons::init()
     * 
     * @return void
     */
    public function init() {
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        if (version_compare(JSM_JVERSION, '4', 'eq')) {
            $uri = JUri::getInstance();
        } else {
            $uri = JFactory::getURI();
        }
        $document = JFactory::getDocument();
        $model = $this->getModel();
        $starttime = microtime();
        $this->restartpage = FALSE;
        $this->state = $this->get('State');
        $this->sortDirection = $this->state->get('list.direction');
        $this->sortColumn = $this->state->get('list.ordering');

        $items = $this->get('Items');
        $this->project_id = $app->getUserState("$option.pid", '0');
        $this->_persontype = JFactory::getApplication()->input->getVar('persontype');
        if (empty($this->_persontype)) {
            $this->_persontype = $app->getUserState("$option.persontype", '0');
        }
        $this->project_team_id = JFactory::getApplication()->input->getVar('project_team_id');
        $this->team_id = $jinput->getInt('team_id');

        if (!$this->team_id) {
            $this->team_id = $app->getUserState("$option.team_id", '0');
        }

        if (!$this->project_team_id) {
            $this->project_team_id = $app->getUserState("$option.project_team_id", '0');
        }

        $mdlProject = JModelLegacy::getInstance('Project', 'sportsmanagementModel');
        $project = $mdlProject->getProject($this->project_id);

        $this->season_id = $project->season_id;

        if (!$items) {
            // fehlen im projekt die positionen ?
            // wenn ja, dann fehlende positionen hinzufügen
//        $this->restartpage = $model->checkProjectPositions($this->project_id, $this->_persontype, $this->team_id, $this->season_id);    
        } else {
            $this->restartpage = FALSE;
        }

        $total = $this->get('Total');
        $pagination = $this->get('Pagination');

        $table = JTable::getInstance('teamperson', 'sportsmanagementTable');
        $this->table = $table;

        $app->setUserState("$option.pid", $project->id);
        $app->setUserState("$option.season_id", $project->season_id);
        $app->setUserState("$option.project_art_id", $project->project_art_id);
        $app->setUserState("$option.sports_type_id", $project->sports_type_id);

        $mdlProjectTeam = JModelLegacy::getInstance('ProjectTeam', 'sportsmanagementModel');
        $project_team = $mdlProjectTeam->getProjectTeam($this->team_id);

        //build the html options for position
        $position_id = array();
        $position_id[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PLAYER_FUNCTION'));
        $mdlPositions = JModelLegacy::getInstance('Positions', 'sportsmanagementModel');

        if ($this->_persontype == 1) {
            $project_ref_positions = $mdlPositions->getProjectPositions($this->project_id, $this->_persontype);
        } elseif ($this->_persontype == 2) {
            $project_ref_positions = $mdlPositions->getProjectPositions($this->project_id, $this->_persontype);
        }

        if ($project_ref_positions) {
            $position_id = array_merge($position_id, $project_ref_positions);
        }
        $lists = array();
        $lists['project_position_id'] = $position_id;
        unset($position_id);

/**
 * build the html options for nation
 */
		$nation[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));
		if ($res = JSMCountries::getCountryOptions())
        {
            $nation = array_merge($nation,$res);
            $this->search_nation = $res;
            }
		
        $lists['nation'] = $nation;

        

        $this->user = JFactory::getUser();
        $this->config = JFactory::getConfig();
        $this->lists = $lists;
        $this->items = $items;
        $this->pagination = $pagination;
        $this->request_url = $uri->toString();
        $this->project = $project;
        $this->project_team = $project_team;


    }

    /**
     * Add the page title and toolbar.
     *
     * @since	1.7
     */
    protected function addToolbar() {
        $app = JFactory::getApplication();
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // store the variable that we would like to keep for next time
        // function syntax is setUserState( $key, $value );
        $app->setUserState("$option.project_team_id", $this->project_team_id);
        $app->setUserState("$option.team_id", $this->team_id);
        $app->setUserState("$option.persontype", $this->_persontype);
        $app->setUserState("$option.season_id", $this->season_id);

        // Set toolbar items for the page
        if ($this->_persontype == 1) {
            $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_TITLE');
        } elseif ($this->_persontype == 2) {
            $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TSTAFFS_TITLE');
        }

        //JToolbarHelper::publishList('teampersons.publish');
        //JToolbarHelper::unpublishList('teampersons.unpublish');
        JToolbarHelper::apply('teampersons.saveshort', JText::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_APPLY'));
        JToolbarHelper::divider();

        //JToolbarHelper::custom( 'teamplayer.assign', 'upload.png', 'upload_f2.png', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_ASSIGN' ), false );
        sportsmanagementHelper::ToolbarButton('assignplayers', 'upload', JText::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_ASSIGN'), 'persons', 0);
        //JToolbarHelper::custom( 'teamplayer.remove', 'cancel.png', 'cancel_f2.png', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_UNASSIGN' ), false );
        //JToolbarHelper::deleteList('', 'teampersons.delete');
        //JToolbarHelper::deleteList('', 'teampersons.remove');
        //JToolbarHelper::deleteList('', 'teamperson.delete');
        JToolbarHelper::divider();

        JToolbarHelper::back('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_BACK', 'index.php?option=com_sportsmanagement&view=projectteams');

        parent::addToolbar();
    }

}

?>
