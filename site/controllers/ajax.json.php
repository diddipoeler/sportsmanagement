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
                $link = sportsmanagementHelperRoute::getMatrixRoute(JRequest::getVar('p'),
                JRequest::getVar('division'), JRequest::getVar('r'));
                break;

            case "teaminfo":
                $link = sportsmanagementHelperRoute::getTeamInfoRoute(JRequest::getVar('p'),
                JRequest::getVar('tid'));
                break;

            case "referees":
                $link = sportsmanagementHelperRoute::getRefereesRoute(JRequest::getVar('p'));
                break;

            case "results":
                $link = sportsmanagementHelperRoute::getResultsRoute(JRequest::getVar('p'),
                JRequest::getVar('r'), JRequest::getVar('division'));
                break;

            case "resultsranking":
                $link = sportsmanagementHelperRoute::getResultsRankingRoute(JRequest::getVar('p'));
                break;

            case "rankingmatrix":
                $link = sportsmanagementHelperRoute::getRankingMatrixRoute(JRequest::getVar('p'),
                JRequest::getVar('r'), JRequest::getVar('division'));
                break;

            case "resultsrankingmatrix":
                $link = sportsmanagementHelperRoute::getResultsRankingMatrixRoute(JRequest::
                getVar('p'), JRequest::getVar('r'), JRequest::getVar('division'));
                break;

            case "teamplan":
                $link = sportsmanagementHelperRoute::getTeamPlanRoute(JRequest::getVar('p'),
                JRequest::getVar('tid'), JRequest::getVar('division'));
                break;

            case "roster":
                $link = sportsmanagementHelperRoute::getPlayersRoute(JRequest::getVar('p'),
                JRequest::getVar('tid'), null, JRequest::getVar('division'));
                break;

            case "eventsranking":
                $link = sportsmanagementHelperRoute::getEventsRankingRoute(JRequest::getVar('p'),
                JRequest::getVar('division'), JRequest::getVar('tid'));
                break;

            case "curve":
                $link = sportsmanagementHelperRoute::getCurveRoute(JRequest::getVar('p'),
                JRequest::getVar('tid'), 0, JRequest::getVar('division'));
                break;

            case "statsranking":
                $link = sportsmanagementHelperRoute::getStatsRankingRoute(JRequest::getVar('p'),
                JRequest::getVar('division'));
                break;

            default:
            case "ranking":
                $link = sportsmanagementHelperRoute::getRankingRoute(JRequest::getVar('p'),
                JRequest::getVar('r'), 0, 0, 0, JRequest::getVar('division'));
        }

        echo json_encode($link);
    }
}
