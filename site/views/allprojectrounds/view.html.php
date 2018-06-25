<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage allprojectrounds
 */
 
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * sportsmanagementViewallprojectrounds
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewallprojectrounds extends JViewLegacy {

    /**
     * sportsmanagementViewallprojectrounds::display()
     * 
     * @param mixed $tpl
     * @return
     */
    function display($tpl = null) {

        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        // Get a refrence of the page instance in joomla
        $document = JFactory::getDocument();
        if (version_compare(JSM_JVERSION, '4', 'eq')) {
            $uri = JUri::getInstance();
        } else {
            $uri = JFactory::getURI();
        }
        $model = $this->getModel();

        //$this->tableclass = $jinput->getVar('table_class', 'table','request','string');
        $this->tableclass = $jinput->request->get('table_class', 'table', 'STR');
        $option = $jinput->getCmd('option');
        $starttime = microtime();

        $project = sportsmanagementModelProject::getProject();

        $this->project = $project;

        $this->projectid = $this->project->id;
        $this->projectmatches = $model->getProjectMatches();
        $this->rounds = sportsmanagementModelProject::getRounds();
        $this->overallconfig = sportsmanagementModelProject::getOverallConfig();
        $this->config = array_merge($this->overallconfig, $model->_params);

//     echo '<br />getRounds<pre>~'.print_r($this->rounds,true).'~</pre><br />';

        $this->favteams = sportsmanagementModelProject::getFavTeams($this->projectid);
//     echo '<br />getFavTeams<pre>~'.print_r($this->favteams,true).'~</pre><br />';

        $this->projectteamid = $model->getProjectTeamID($this->favteams);

        $this->content = $model->getRoundsColumn($this->rounds, $this->config);

        if (COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO) {
            $app->enqueueMessage(JText::_(__METHOD__ . ' ' . __LINE__ . ' Ausfuehrungszeit query<br><pre>' . print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()), true) . '</pre>'), 'Notice');
        }

        //$this->headertitle = JText::_( 'COM_SPORTSMANAGEMENT_RESULTS_ROUND_RESULTS2' );
        $this->headertitle = JText::sprintf('COM_SPORTSMANAGEMENT_RESULTS_ROUND_RESULTS2', $this->project->name);
        parent::display($tpl);
    }

}

?>