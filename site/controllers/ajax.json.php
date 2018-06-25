<?php

/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      ajax.json.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage 
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * sportsmanagementControllerAjax
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllerAjax extends JControllerLegacy {

    /**
     * sportsmanagementControllerAjax::__construct()
     * 
     * @return
     */
    public function __construct() {
        // Get the document object.
        $document = JFactory::getDocument();
        // Set the MIME type for JSON output.
        $document->setMimeEncoding('application/json');
        parent::__construct();
    }

    /**
     * sportsmanagementControllerAjax::getprojectsoptions()
     * 
     * @return
     */
    public function getprojectsoptions() {
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $season = $jinput->getInt('s');
        $league = $jinput->getInt('l');
        $ordering = $jinput->getInt('o');

        $model = $this->getModel('ajax');

        $res = $model->getProjectsOptions($season, $league, $ordering);

        echo json_encode($res);

        $app->close();
    }

    /**
     * sportsmanagementControllerAjax::getroute()
     * 
     * @return
     */
    public function getroute() {
        $view = JFactory::getApplication()->input->getCmd('view');

        switch ($view) {
            case "matrix":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database', 0);
                $routeparameter['s'] = JFactory::getApplication()->input->getInt('s', 0);
                $routeparameter['p'] = JFactory::getApplication()->input->getVar('p');
                $routeparameter['division'] = JFactory::getApplication()->input->getVar('division');
                $routeparameter['r'] = JFactory::getApplication()->input->getVar('r');
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('matrix', $routeparameter);
                break;

            case "teaminfo":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database', 0);
                $routeparameter['s'] = JFactory::getApplication()->input->getInt('s', 0);
                $routeparameter['p'] = JFactory::getApplication()->input->getVar('p');
                $routeparameter['tid'] = JFactory::getApplication()->input->getVar('tid');
                $routeparameter['ptid'] = 0;
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo', $routeparameter);
                break;

            case "referees":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database', 0);
                $routeparameter['s'] = JFactory::getApplication()->input->getInt('s', 0);
                $routeparameter['p'] = JFactory::getApplication()->input->getVar('p');
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('referees', $routeparameter);
                break;

            case "results":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database', 0);
                $routeparameter['s'] = JFactory::getApplication()->input->getInt('s', 0);
                $routeparameter['p'] = JFactory::getApplication()->input->getVar('p');
                $routeparameter['r'] = JFactory::getApplication()->input->getVar('r');
                $routeparameter['division'] = JFactory::getApplication()->input->getVar('division');
                $routeparameter['mode'] = 0;
                $routeparameter['order'] = '';
                $routeparameter['layout'] = '';
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('results', $routeparameter);
                break;

            case "resultsranking":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database', 0);
                $routeparameter['s'] = JFactory::getApplication()->input->getInt('s', 0);
                $routeparameter['p'] = JFactory::getApplication()->input->getVar('p');
                $routeparameter['r'] = 0;
                $routeparameter['division'] = 0;
                $routeparameter['mode'] = 0;
                $routeparameter['order'] = '';
                $routeparameter['layout'] = '';
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('results', $routeparameter);
                break;

            case "rankingmatrix":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database', 0);
                $routeparameter['s'] = JFactory::getApplication()->input->getInt('s', 0);
                $routeparameter['p'] = JFactory::getApplication()->input->getVar('p');
                $routeparameter['type'] = 0;
                $routeparameter['r'] = JFactory::getApplication()->input->getVar('r');
                $routeparameter['from'] = 0;
                $routeparameter['to'] = 0;
                $routeparameter['division'] = JFactory::getApplication()->input->getVar('division');
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('rankingmatrix', $routeparameter);
                break;

            case "resultsrankingmatrix":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database', 0);
                $routeparameter['s'] = JFactory::getApplication()->input->getInt('s', 0);
                $routeparameter['p'] = JFactory::getApplication()->input->getVar('p');
                $routeparameter['r'] = JFactory::getApplication()->input->getVar('r');
                $routeparameter['division'] = JFactory::getApplication()->input->getVar('division');
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('resultsrankingmatrix', $routeparameter);
                break;

            case "teamplan":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database', 0);
                $routeparameter['s'] = JFactory::getApplication()->input->getInt('s', 0);
                $routeparameter['p'] = JFactory::getApplication()->input->getVar('p');
                $routeparameter['tid'] = JFactory::getApplication()->input->getVar('tid');
                $routeparameter['division'] = JFactory::getApplication()->input->getVar('division');
                $routeparameter['mode'] = 0;
                $routeparameter['ptid'] = 0;
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('teamplan', $routeparameter);
                break;

            case "roster":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database', 0);
                $routeparameter['s'] = JFactory::getApplication()->input->getInt('s', 0);
                $routeparameter['p'] = JFactory::getApplication()->input->getVar('p');
                $routeparameter['tid'] = JFactory::getApplication()->input->getVar('tid');
                $routeparameter['ptid'] = 0;
                $routeparameter['division'] = JFactory::getApplication()->input->getVar('division');
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('roster', $routeparameter);
                break;

            case "eventsranking":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database', 0);
                $routeparameter['s'] = JFactory::getApplication()->input->getInt('s', 0);
                $routeparameter['p'] = JFactory::getApplication()->input->getVar('p');
                $routeparameter['division'] = JFactory::getApplication()->input->getVar('division');
                $routeparameter['tid'] = JFactory::getApplication()->input->getVar('tid');
                $routeparameter['evid'] = 0;
                $routeparameter['mid'] = 0;
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('eventsranking', $routeparameter);
                break;

            case "curve":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database', 0);
                $routeparameter['s'] = JFactory::getApplication()->input->getInt('s', 0);
                $routeparameter['p'] = JFactory::getApplication()->input->getVar('p');
                $routeparameter['tid1'] = JFactory::getApplication()->input->getVar('tid');
                $routeparameter['tid2'] = 0;
                $routeparameter['division'] = JFactory::getApplication()->input->getVar('division');
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('curve', $routeparameter);
                break;

            case "statsranking":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database', 0);
                $routeparameter['s'] = JFactory::getApplication()->input->getInt('s', 0);
                $routeparameter['p'] = JFactory::getApplication()->input->getVar('p');
                $routeparameter['division'] = JFactory::getApplication()->input->getVar('division');
                $routeparameter['tid'] = 0;
                $routeparameter['sid'] = 0;
                $routeparameter['order'] = '';
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('statsranking', $routeparameter);
                break;

            default:
            case "ranking":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database', 0);
                $routeparameter['s'] = JFactory::getApplication()->input->getInt('s', 0);
                $routeparameter['p'] = JFactory::getApplication()->input->getVar('p');
                $routeparameter['type'] = 0;
                $routeparameter['r'] = JFactory::getApplication()->input->getVar('r');
                $routeparameter['from'] = 0;
                $routeparameter['to'] = 0;
                $routeparameter['division'] = JFactory::getApplication()->input->getVar('division');
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('ranking', $routeparameter);
                break;
        }

        echo json_encode($link);
    }

}
