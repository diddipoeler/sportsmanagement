<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version                1.0.05
 * @file                   agegroup.php
 * @author                 diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright              Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
 * OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
 * Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
 * Siehe die GNU General Public License für weitere Details.
 *
 * Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
 * Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
 *
 * Note : All ini files need to be saved as UTF-8 without BOM
 */

// no direct access
defined('_JEXEC') or die('Restricted access');


/**
 * modJSMSportsHelper
 *
 * @package
 * @author    abcde
 * @copyright 2015
 * @version   $Id$
 * @access    public
 */
class modJSMSportsHelper
{


    /**
     * modJSMSportsHelper::getData()
     *
     * @param mixed $params
     *
     * @return array
     */
    public static function getData(&$params)
    {
        if ( !class_exists('sportsmanagementModelSportsTypes') )
        {
            require_once( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_sportsmanagement' . DS . 'models' . DS . 'sportstypes.php' );
        }
        $model = JModelLegacy::getInstance('SportsTypes', 'sportsmanagementModel');

        return array('sportstype'                => $model->getSportsTypes(),
                     'projectscount'             => $model->getProjectsCount($params->get('sportstypes')),
                     'playgroundscount'          => $model->getPlaygroundsOnlyCount($params->get('sportstypes')),
                     'leaguescount'              => $model->getLeaguesCount($params->get('sportstypes')),
                     'seasonscount'              => $model->getSeasonsCount($params->get('sportstypes')),
                     'clubscount'                => $model->getClubsOnlyCount($params->get('sportstypes')),
                     'personscount'              => $model->getPersonsOnlyCount($params->get('sportstypes')),
                     'projectteamscount'         => $model->getProjectTeamsCount($params->get('sportstypes')),
                     'projectteamsplayerscount'  => $model->getProjectTeamsPlayersCount($params->get('sportstypes')),
                     'projectdivisionscount'     => $model->getProjectDivisionsCount($params->get('sportstypes')),
                     'projectroundscount'        => $model->getProjectRoundsCount($params->get('sportstypes')),
                     'projectmatchescount'       => $model->getProjectMatchesCount($params->get('sportstypes')),
                     'projectmatcheseventscount' => $model->getProjectMatchesEventsCount($params->get('sportstypes')),
                     'projectmatchesstatscount'  => $model->getProjectMatchesStatsCount($params->get('sportstypes')),
        );
    }
}