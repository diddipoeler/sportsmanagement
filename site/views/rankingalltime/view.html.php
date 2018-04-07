<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage rankingalltime
 */
 
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

/**
 * sportsmanagementViewRankingAllTime
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewRankingAllTime extends JViewLegacy {

    /**
     * sportsmanagementViewRankingAllTime::display()
     * 
     * @param mixed $tpl
     * @return void
     */
    function display($tpl = null) {
        // Get a refrence of the page instance in joomla
        $document = JFactory::getDocument();

        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Get a refrence of the page instance in joomla
        $document = JFactory::getDocument();
        if (version_compare(JSM_JVERSION, '4', 'eq')) {
            $uri = JUri::getInstance();
        } else {
            $uri = JFactory::getURI();
        }

        $document->addScript(JUri::root(true) . '/components/' . $option . '/assets/js/smsportsmanagement.js');

        $model = $this->getModel();

        $this->projectids = $model->getAllProject();
        $project_ids = implode(",", $this->projectids);

        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' project_ids<br><pre>'.print_r($project_ids,true).'</pre>'),'');

        $this->project_ids = $project_ids;
        $this->teams = $model->getAllTeamsIndexedByPtid($project_ids);

        $this->matches = $model->getAllMatches($project_ids);
        $this->ranking = $model->getAllTimeRanking();
        $this->tableconfig = $model->getAllTimeParams();
        $this->config = $model->getAllTimeParams();

        $this->currentRanking = $model->getCurrentRanking();

        $this->action = $uri->toString();
        $this->colors = $model->getColors($this->config['colors']);



        // Set page title
        $pageTitle = JText::_('COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE');

        $document->setTitle($pageTitle);

        parent::display($tpl);
    }

}

?>