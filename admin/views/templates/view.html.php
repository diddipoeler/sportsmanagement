<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage templates
 */
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementViewTemplates
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewTemplates extends sportsmanagementView {

    /**
     * sportsmanagementViewTemplates::init()
     * 
     * @return void
     */
    public function init() {
        $app = JFactory::getApplication();
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $document = JFactory::getDocument();
        if (version_compare(JSM_JVERSION, '4', 'eq')) {
            $uri = JUri::getInstance();
        } else {
            $uri = JFactory::getURI();
        }
        $model = $this->getModel();
        $starttime = microtime();

        $this->state = $this->get('State');
        $this->sortDirection = $this->state->get('list.direction');
        $this->sortColumn = $this->state->get('list.ordering');

        $this->project_id = $app->getUserState("$option.pid", '0');
        $mdlProject = JModelLegacy::getInstance("Project", "sportsmanagementModel");
        $project = $mdlProject->getProject($this->project_id);
        $lists = '';
        $allTemplates = $model->checklist($this->project_id);

        // das sind die eigenen templates
        $templates = $this->get('Items');

        if (COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO) {
            $app->enqueueMessage(JText::_(__METHOD__ . ' ' . __LINE__ . ' Ausfuehrungszeit query<br><pre>' . print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()), true) . '</pre>'), 'Notice');
        }

        $total = $this->get('Total');

        if ($project->master_template) {
            // das sind die templates aus einenm anderen projekt
            $model->set('_getALL', 1);
            $allMasterTemplates = $model->getMasterTemplatesList();
            $model->set('_getALL', 0);
            $masterTemplates = $model->getMasterTemplatesList();

            // Build in JText of template title here
            foreach ($masterTemplates as $temptext) {
                $temptext->text = JText::_($temptext->text);
            }

            $importlist = array();
            $importlist[] = JHtml::_('select.option', 0, JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_SELECT_FROM_MASTER'));
            $importlist = array_merge($importlist, $masterTemplates);
            $lists['mastertemplates'] = JHtml::_('select.genericlist', $importlist, 'templateid', 'class="inputbox" onChange="Joomla.submitform(\'template.masterimport\', this.form);" ');
            $master = $model->getMasterName();
            $this->master = $master;
            $templates = array_merge($templates, $allMasterTemplates);

            $total = count($templates);
        }

        $pagination = $this->get('Pagination');
        $this->user = JFactory::getUser();
        $this->lists = $lists; //otherwise no indication of the list in default_data.php on line 64!
        $this->templates = $templates;
        $this->projectws = $project;
        $this->pagination = $pagination;
        $this->request_url = $uri->toString();
    }

    /**
     * Add the page title and toolbar.
     *
     * @since	1.7
     */
    protected function addToolbar() {
        $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_TITLE');

            JToolbarHelper::editList('template.edit');
            JToolbarHelper::save('template.save');

            if ($this->projectws->master_template) {

                JToolbarHelper::deleteList('', 'template.remove', 'JTOOLBAR_DELETE');
            } else {
                JToolbarHelper::custom('template.reset', 'restore', 'restore', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_RESET'));
            }

        JToolbarHelper::checkin('templates.checkin');
        parent::addToolbar();
    }

}

?>
