<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage rounds
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementViewRounds
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewRounds extends sportsmanagementView {

    /**
     * sportsmanagementViewRounds::init()
     * 
     * @return
     */
    public function init() {
        $app = JFactory::getApplication();
        $this->massadd = 0;
        $this->populate = 0;
        $tpl = NULL;
        /**
         * dadurch werden die spaltenbreiten optimiert
         */
        $this->document->addStyleSheet(JUri::root() . 'administrator/components/com_sportsmanagement/assets/css/form_control.css', 'text/css');

        if ($this->getLayout() == 'default' || $this->getLayout() == 'default_3' || $this->getLayout() == 'default_4') {
            $this->_displayDefault($tpl);
            return;
        } else if ($this->getLayout() == 'populate' || $this->getLayout() == 'populate_3' || $this->getLayout() == 'populate_4') {
            $this->_displayPopulate($tpl);
            return;
        } else if ($this->getLayout() == 'massadd' || $this->getLayout() == 'massadd_3' || $this->getLayout() == 'massadd_4') {
            $this->_displayMassadd($tpl);
            return;
        }
    }

    /**
     * sportsmanagementViewRounds::_displayMassadd()
     * 
     * @param mixed $tpl
     * @return void
     */
    function _displayMassadd($tpl) {
        $app = JFactory::getApplication();
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $db = sportsmanagementHelper::getDBConnection();
        if (version_compare(JSM_JVERSION, '4', 'eq')) {
            $uri = JUri::getInstance();
        } else {
            $uri = JFactory::getURI();
        }
        $this->project_id = $app->getUserState("$option.pid", '0');

        $mdlProject = JModelLegacy::getInstance("Project", "sportsmanagementModel");
        $project = $mdlProject->getProject($this->project_id);
        $this->project = $project;
        $this->setLayout('massadd');
    }

    /**
     * sportsmanagementViewRounds::_displayDefault()
     * 
     * @param mixed $tpl
     * @return void
     */
    function _displayDefault($tpl) {

        $starttime = microtime();
        $matchday = $this->get('Items');
        if (COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO) {
            $this->app->enqueueMessage(JText::_(__METHOD__ . ' ' . __LINE__ . ' Ausfuehrungszeit query<br><pre>' . print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()), true) . '</pre>'), 'Notice');
        }

        $this->table = JTable::getInstance('round', 'sportsmanagementTable');

        //$project_id	= JFactory::getApplication()->input->getVar('pid');
        $this->project_id = $this->app->getUserState("$this->option.pid", '0');

        $mdlProject = JModelLegacy::getInstance('Project', 'sportsmanagementModel');
        $project = $mdlProject->getProject($this->project_id);
        //$massadd=JFactory::getApplication()->input->getVar('massadd');
        $myoptions = array();
        $myoptions[] = JHtml::_('select.option', '0', JText::_('JNO'));
        $myoptions[] = JHtml::_('select.option', '1', JText::_('JYES'));
        $lists['tournementround'] = $myoptions;

        $this->lists = $lists;
        $this->matchday = $this->items;
        $this->project = $project;
    }

    /**
     * sportsmanagementViewRounds::_displayPopulate()
     * 
     * @param mixed $tpl
     * @return void
     */
    function _displayPopulate($tpl) {
        $app = JFactory::getApplication();
        $document = Jfactory::getDocument();
        $uri = JFactory::getURI();

        $model = $this->getModel();

        $document->setTitle(JText::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_TITLE'));
        //$version = urlencode(sportsmanagementHelper::getVersion());
        //$document->addScript('components/com_sportsmanagement/assets/js/populate.js?v='.$version);

        $lists = array();

        $options = array(JHtml::_('select.option', 0, Jtext::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_TYPE_SINGLE_ROUND_ROBIN')),
            JHtml::_('select.option', 1, Jtext::_('COM_SPORTSMANAGEMENTADMIN_ROUNDS_POPULATE_TYPE_DOUBLE_ROUND_ROBIN')),
            JHtml::_('select.option', 2, Jtext::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_TYPE_TOURNAMENT_ROUND_ROBIN'))
        );
        $lists['scheduling'] = JHtml::_('select.genericlist', $options, 'scheduling', '', 'value', 'text');

        //TODO-add error message - what if there are no teams assigned to the project
        $this->project_id = $this->app->getUserState("$this->option.pid", '0');
        $mdlProject = JModelLegacy::getInstance("Project", "sportsmanagementModel");
        $teams = $mdlProject->getProjectTeamsOptions($this->project_id);
        $projectws = $mdlProject->getProject($this->project_id);

        $options = array();
        foreach ($teams as $t) {
            $options[] = JHtml::_('select.option', $t->value, $t->text);
        }
        $lists['teamsorder'] = JHtml::_('select.genericlist', $options, 'teamsorder[]', 'multiple="multiple" size="20"');

        $this->projectws = $projectws;
        $this->request_url = $uri->toString();
        $this->lists = $lists;
$this->populate = 1;
        $this->setLayout('populate');
    }

    /**
     * Add the page title and toolbar.
     *
     * @since	1.6
     */
    protected function addToolbar() {
        // Set toolbar items for the page
        $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_TITLE');

        if (!$this->massadd) {

if ( $this->populate ) {
        $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_TITLE');
JToolbarHelper::apply('round.startpopulate');
        JToolbarHelper::back();
        parent::addToolbar();
}
else
{
            //JLToolBarHelper::custom('round.roundrobin','purge.png','purge_f2.png',JText::_('COM_SPORTSMANAGEMENT_ADMIN_ROUND_ROBIN_MASSADD_BUTTON'),false);
            JToolbarHelper::publishList('rounds.publish');
            JToolbarHelper::unpublishList('rounds.unpublish');
            JToolbarHelper::divider();
            JToolbarHelper::custom('rounds.populate', 'purge.png', 'purge_f2.png', JText::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_BUTTON'), false);
            JToolbarHelper::divider();
            JToolbarHelper::apply('rounds.saveshort');
            JToolbarHelper::divider();

            //JToolbarHelper::custom('round.massadd','new.png','new_f2.png',JText::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_MASSADD_BUTTON'),false);
            sportsmanagementHelper::ToolbarButton('massadd', 'new', JText::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_MASSADD_BUTTON'));

            JToolbarHelper::addNew('round.save');
            JToolbarHelper::divider();

            JToolbarHelper::deleteList('', 'rounds.deleteroundmatches', JText::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_MASSDEL_BUTTON'));
            //JToolbarHelper::custom('rounds.deletematches','delete.png','delete.png',JText::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_MASSDEL_BUTTON'),false);
            parent::addToolbar();
            }
        } else {
            JToolbarHelper::custom('round.cancelmassadd', 'cancel.png', 'cancel_f2.png', JText::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_MASSADD_CANCEL'), false);
            
        }

    }

   

}

?>
