<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage projectreferees
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * HTML View class for the Sportsmanagement Component
 *
 * @static
 * @package	Sportsmanagement
 * @since	0.1
 */
class sportsmanagementViewprojectreferees extends sportsmanagementView {

    /**
     * sportsmanagementViewprojectreferees::init()
     * 
     * @return void
     */
    public function init() {
        $app = JFactory::getApplication();
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        if (version_compare(JSM_JVERSION, '4', 'eq')) {
            $uri = JUri::getInstance();
        } else {
            $uri = JFactory::getURI();
        }
        $model = $this->getModel();

        $this->state = $this->get('State');
        $this->sortDirection = $this->state->get('list.direction');
        $this->sortColumn = $this->state->get('list.ordering');

        $items = $this->get('Items');
        $total = $this->get('Total');
        $pagination = $this->get('Pagination');

        $table = JTable::getInstance('projectreferee', 'sportsmanagementTable');
        $this->table = $table;

        $this->_persontype = $jinput->get('persontype');
        if (empty($this->_persontype)) {
            $this->_persontype = $app->getUserState("$option.persontype", '0');
        }
        $this->project_id = $app->getUserState("$option.pid", '0');
        $mdlProject = JModelLegacy::getInstance('Project', 'sportsmanagementModel');
        $project = $mdlProject->getProject($this->project_id);

/**
 * build the html options for position
 */
        $position_id[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_REFEREE_FUNCTION'));
        $mdlPositions = JModelLegacy::getInstance('Positions', 'sportsmanagementModel');
        $project_ref_positions = $mdlPositions->getProjectPositions($this->project_id, $this->_persontype);
        if ($project_ref_positions) {
            $position_id = array_merge($position_id, $project_ref_positions);
            $this->project_position_id = $project_ref_positions;
        }
        $lists['project_position_id'] = $position_id;
        unset($position_id);

        $this->user = JFactory::getUser();
        $this->config = JFactory::getConfig();
        $this->lists = $lists;
        $this->items = $items;
        $this->pagination = $pagination;
        $this->request_url = $uri->toString();
        $this->project = $project;
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
        $app->setUserState("$option.persontype", $this->_persontype);

        $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_TITLE');

        JToolbarHelper::apply('projectreferees.saveshort', JText::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_APPLY'));
        //JToolbarHelper::custom('projectreferee.assign','upload.png','upload_f2.png',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_ASSIGN'),false);
        sportsmanagementHelper::ToolbarButton('assignplayers', 'upload', JText::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_ASSIGN'), 'persons', 3);
        //JToolbarHelper::custom('projectreferees.remove','cancel.png','cancel_f2.png',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_UNASSIGN'),false);
        JToolbarHelper::deleteList('', 'projectreferees.delete');
        JToolbarHelper::checkin('projectreferees.checkin');
        parent::addToolbar();
    }

}

?>
