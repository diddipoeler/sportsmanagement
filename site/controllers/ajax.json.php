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
class sportsmanagementControllerAjax extends JControllerLegacy
{
    /**
     * sportsmanagementControllerAjax::__construct()
     * 
     * @return
     */
    public function __construct()
    {
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
    public function getprojectsoptions()
    {
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
    public function getroute()
    {
        $view = Jrequest::getCmd('view');

        switch ($view) {
            case "matrix":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database', 0);
                $routeparameter['s'] = JRequest::getInt('s', 0);
                $routeparameter['p'] = JRequest::getVar('p');
                $routeparameter['division'] = JRequest::getVar('division');
                $routeparameter['r'] = JRequest::getVar('r');
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('matrix', $routeparameter);
                break;

            case "teaminfo":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database', 0);
                $routeparameter['s'] = JRequest::getInt('s', 0);
                $routeparameter['p'] = JRequest::getVar('p');
                $routeparameter['tid'] = JRequest::getVar('tid');
                $routeparameter['ptid'] = 0;
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo', $routeparameter);
                break;

            case "referees":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database', 0);
                $routeparameter['s'] = JRequest::getInt('s', 0);
                $routeparameter['p'] = JRequest::getVar('p');
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('referees', $routeparameter);
                break;

            case "results":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database', 0);
                $routeparameter['s'] = JRequest::getInt('s', 0);
                $routeparameter['p'] = JRequest::getVar('p');
                $routeparameter['r'] = JRequest::getVar('r');
                $routeparameter['division'] = JRequest::getVar('division');
                $routeparameter['mode'] = 0;
                $routeparameter['order'] = '';
                $routeparameter['layout'] = '';
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('results', $routeparameter);
                break;

            case "resultsranking":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database', 0);
                $routeparameter['s'] = JRequest::getInt('s', 0);
                $routeparameter['p'] = JRequest::getVar('p');
                $routeparameter['r'] = 0;
                $routeparameter['division'] = 0;
                $routeparameter['mode'] = 0;
                $routeparameter['order'] = '';
                $routeparameter['layout'] = '';
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('results', $routeparameter);
                break;

            case "rankingmatrix":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database', 0);
                $routeparameter['s'] = JRequest::getInt('s', 0);
                $routeparameter['p'] = JRequest::getVar('p');
                $routeparameter['type'] = 0;
                $routeparameter['r'] = JRequest::getVar('r');
                $routeparameter['from'] = 0;
                $routeparameter['to'] = 0;
                $routeparameter['division'] = JRequest::getVar('division');
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('rankingmatrix', $routeparameter);
                break;

            case "resultsrankingmatrix":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database', 0);
                $routeparameter['s'] = JRequest::getInt('s', 0);
                $routeparameter['p'] = JRequest::getVar('p');
                $routeparameter['r'] = JRequest::getVar('r');
                $routeparameter['division'] = JRequest::getVar('division');
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('resultsrankingmatrix',
                    $routeparameter);
                break;

            case "teamplan":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database', 0);
                $routeparameter['s'] = JRequest::getInt('s', 0);
                $routeparameter['p'] = JRequest::getVar('p');
                $routeparameter['tid'] = JRequest::getVar('tid');
                $routeparameter['division'] = JRequest::getVar('division');
                $routeparameter['mode'] = 0;
                $routeparameter['ptid'] = 0;
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('teamplan', $routeparameter);
                break;

            case "roster":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database', 0);
                $routeparameter['s'] = JRequest::getInt('s', 0);
                $routeparameter['p'] = JRequest::getVar('p');
                $routeparameter['tid'] = JRequest::getVar('tid');
                $routeparameter['ptid'] = 0;
                $routeparameter['division'] = JRequest::getVar('division');
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('roster', $routeparameter);
                break;

            case "eventsranking":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database', 0);
                $routeparameter['s'] = JRequest::getInt('s', 0);
                $routeparameter['p'] = JRequest::getVar('p');
                $routeparameter['division'] = JRequest::getVar('division');
                $routeparameter['tid'] = JRequest::getVar('tid');
                $routeparameter['evid'] = 0;
                $routeparameter['mid'] = 0;
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('eventsranking', $routeparameter);
                break;

            case "curve":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database', 0);
                $routeparameter['s'] = JRequest::getInt('s', 0);
                $routeparameter['p'] = JRequest::getVar('p');
                $routeparameter['tid1'] = JRequest::getVar('tid');
                $routeparameter['tid2'] = 0;
                $routeparameter['division'] = JRequest::getVar('division');
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('curve', $routeparameter);
                break;

            case "statsranking":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database', 0);
                $routeparameter['s'] = JRequest::getInt('s', 0);
                $routeparameter['p'] = JRequest::getVar('p');
                $routeparameter['division'] = JRequest::getVar('division');
                $routeparameter['tid'] = 0;
                $routeparameter['sid'] = 0;
                $routeparameter['order'] = '';
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('statsranking', $routeparameter);
                break;

            default:
            case "ranking":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database', 0);
                $routeparameter['s'] = JRequest::getInt('s', 0);
                $routeparameter['p'] = JRequest::getVar('p');
                $routeparameter['type'] = 0;
                $routeparameter['r'] = JRequest::getVar('r');
                $routeparameter['from'] = 0;
                $routeparameter['to'] = 0;
                $routeparameter['division'] = JRequest::getVar('division');
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('ranking', $routeparameter);
                break;
        }

        echo json_encode($link);
    }
}
