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
defined( '_JEXEC' ) or die( 'Restricted access' );

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
                parent::__construct();
        }
        
        
        /**
         * sportsmanagementControllerAjax::personlistoptions()
         * 
         * @return void
         */
        public function personlistoptions()
        {
                $model = $this->getModel('ajax');
                $req = JRequest::getVar('required', false);
                $required = ($req == 'true' || $req == '1') ? true : false;
                echo json_encode((array) $model->getpersonlistoptions(JRequest::getInt( 'person_art' ), $required));
                JFactory::getApplication()->close();
        }
        
        /**
         * sportsmanagementControllerAjax::personpositionoptions()
         * 
         * @return void
         */
        public function personpositionoptions()
        {
                $model = $this->getModel('ajax');
                $req = JRequest::getVar('required', false);
                $required = ($req == 'true' || $req == '1') ? true : false;
                echo json_encode((array) $model->getpersonpositionoptions(JRequest::getInt( 'sports_type_id' ), $required));
                JFactory::getApplication()->close();
        }
        
        /**
         * sportsmanagementControllerAjax::personagegroupoptions()
         * 
         * @return void
         */
        public function personagegroupoptions()
        {
                $model = $this->getModel('ajax');
                $req = JRequest::getVar('required', false);
                $required = ($req == 'true' || $req == '1') ? true : false;
                echo json_encode((array) $model->getpersonagegroupoptions(JRequest::getInt( 'sports_type_id' ), $required));
                JFactory::getApplication()->close();
        }

        /**
         * sportsmanagementControllerAjax::projectdivisionsoptions()
         * 
         * @return
         */
        public function projectdivisionsoptions()
        {
                $model = $this->getModel('ajax');
                $req = JRequest::getVar('required', false);
                $required = ($req == 'true' || $req == '1') ? true : false;
                echo json_encode((array) $model->getProjectDivisionsOptions(JRequest::getInt( 'p' ), $required));
                JFactory::getApplication()->close();
        }

        /**
         * sportsmanagementControllerAjax::projecteventsoptions()
         * 
         * @return
         */
        public function projecteventsoptions()
        {
                $model = $this->getModel('ajax');
                $req = JRequest::getVar('required', false);
                $required = ($req == 'true' || $req == '1') ? true : false;
                echo json_encode((array) $model->getProjectEventsOptions(JRequest::getInt( 'p' ), $required));
                JFactory::getApplication()->close();
        }

        /**
         * sportsmanagementControllerAjax::projectteamsbydivisionoptions()
         * 
         * @return
         */
        public function projectteamsbydivisionoptions()
        {
                $model = $this->getModel('ajax');
                $req = JRequest::getVar('required', false);
                $required = ($req == 'true' || $req == '1') ? true : false;
                echo json_encode((array) $model->getProjectTeamsByDivisionOptions(JRequest::getInt( 'p' ), JRequest::getInt( 'division' ), $required));
                JFactory::getApplication()->close();
        }

        /**
         * sportsmanagementControllerAjax::projectsbysportstypesoptions()
         * 
         * @return
         */
        public function projectsbysportstypesoptions()
        {
                $model = $this->getModel('ajax');
                $req = JRequest::getVar('required', false);
                $required = ($req == 'true' || $req == '1') ? true : false;
                echo json_encode((array) $model->getProjectsBySportsTypesOptions(JRequest::getInt('sportstype'), $required));
                JFactory::getApplication()->close();
        }
        
        /**
         * sportsmanagementControllerAjax::agegroupsbysportstypesoptions()
         * 
         * @return
         */
        public function agegroupsbysportstypesoptions()
        {
                $model = $this->getModel('ajax');
                $req = JRequest::getVar('required', false);
                $required = ($req == 'true' || $req == '1') ? true : false;
                echo json_encode((array) $model->getAgeGroupsBySportsTypesOptions(JRequest::getInt('sportstype'), $required));
                JFactory::getApplication()->close();
        }

        /**
         * sportsmanagementControllerAjax::projectsbycluboptions()
         * 
         * @return
         */
        public function projectsbycluboptions()
        {
                $model = $this->getModel('ajax');
                $req = JRequest::getVar('required', false);
                $required = ($req == 'true' || $req == '1') ? true : false;
                echo json_encode((array) $model->getProjectsByClubOptions(JRequest::getInt( 'cid' ), $required));
                JFactory::getApplication()->close();
        }

        /**
         * sportsmanagementControllerAjax::projectteamsoptions()
         * 
         * @return
         */
        public function projectteamsoptions()
        {
                $model = $this->getModel('ajax');
                $req = JRequest::getVar('required', false);
                $required = ($req == 'true' || $req == '1') ? true : false;
                echo json_encode((array) $model->getProjectTeamOptions(JRequest::getInt( 'p' ),$required));
                JFactory::getApplication()->close();
        }
        
        /**
         * sportsmanagementControllerAjax::projectteamsptidoptions()
         * 
         * @return
         */
        public function projectteamsptidoptions()
        {
                $model = $this->getModel('ajax');
                $req = JRequest::getVar('required', false);
                $required = ($req == 'true' || $req == '1') ? true : false;
                echo json_encode((array) $model->getProjectTeamPtidOptions(JRequest::getInt( 'p' ),$required));
                JFactory::getApplication()->close();
        }
        
        /**
         * sportsmanagementControllerAjax::projectplayeroptions()
         * 
         * @return
         */
        public function projectplayeroptions()
        {
                $model = $this->getModel('ajax');
                $req = JRequest::getVar('required', false);
                $required = ($req == 'true' || $req == '1') ? true : false;
                echo json_encode((array) $model->getProjectPlayerOptions(JRequest::getInt( 'p' ),$required));
                JFactory::getApplication()->close();
        }

        /**
         * sportsmanagementControllerAjax::projectstaffoptions()
         * 
         * @return
         */
        public function projectstaffoptions()
        {
                $model = $this->getModel('ajax');
                $req = JRequest::getVar('required', false);
                $required = ($req == 'true' || $req == '1') ? true : false;
                echo json_encode((array) $model->getProjectStaffOptions(JRequest::getInt( 'p' ),$required));
                JFactory::getApplication()->close();
        }

        /**
         * sportsmanagementControllerAjax::projectclubsoptions()
         * 
         * @return
         */
        public function projectclubsoptions()
        {
                $model = $this->getModel('ajax');
                $req = JRequest::getVar('required', false);
                $required = ($req == 'true' || $req == '1') ? true : false;
                echo json_encode((array) $model->getProjectClubOptions(JRequest::getInt( 'p' ), $required));
                JFactory::getApplication()->close();
        }

        /**
         * sportsmanagementControllerAjax::projectstatsoptions()
         * 
         * @return
         */
        public function projectstatsoptions()
        {
                $model = $this->getModel('ajax');
                $req = JRequest::getVar('required', false);
                $required = ($req == 'true' || $req == '1') ? true : false;
                echo json_encode((array) $model->getProjectStatOptions(JRequest::getInt( 'p' ), $required));
                JFactory::getApplication()->close();
        }

        /**
         * sportsmanagementControllerAjax::matchesoptions()
         * 
         * @return
         */
        public function matchesoptions()
        {
                $model = $this->getModel('ajax');
                $req = JRequest::getVar('required', false);
                $required = ($req == 'true' || $req == '1') ? true : false;
                echo json_encode((array) $model->getMatchesOptions(JRequest::getInt( 'p' ), $required));
                JFactory::getApplication()->close();
        }

        /**
         * sportsmanagementControllerAjax::refereesoptions()
         * 
         * @return
         */
        public function refereesoptions()
        {
                $model = $this->getModel('ajax');
                $req = JRequest::getVar('required', false);
                $required = ($req == 'true' || $req == '1') ? true : false;
                echo json_encode((array) $model->getRefereesOptions(JRequest::getInt( 'p' ), $required));
                JFactory::getApplication()->close();
        }

        /**
         * sportsmanagementControllerAjax::roundsoptions()
         * 
         * @return
         */
        public function projectroundoptions()
        {
            $model = $this->getModel('ajax');
                $req = JRequest::getVar('required', false);
                $required = ($req == 'true' || $req == '1') ? true : false;
                echo json_encode((array) $model->getProjectRoundOptions(JRequest::getInt( 'p' ), 'ASC', $req));
                JFactory::getApplication()->close();
        }

        /**
         * sportsmanagementControllerAjax::projecttreenodeoptions()
         * 
         * @return
         */
        public function projecttreenodeoptions()
        {
                $model = $this->getModel('ajax');
                $req = JRequest::getVar('required', false);
                $required = ($req == 'true' || $req == '1') ? true : false;
                echo json_encode((array) $model->getProjectTreenodeOptions(JRequest::getInt( 'p' ), $required));
                JFactory::getApplication()->close();
        }
        
        /**
         * sportsmanagementControllerAjax::sportstypesoptions()
         * 
         * @return
         */
        public function sportstypesoptions()
        {
                echo json_encode((array) JoomleagueModelSportsTypes::getSportsTypes());
                JFactory::getApplication()->close();
        }

}
// Register the error handler.
//JError::setErrorHandling(E_ALL, 'callback', array('JoomleagueControllerAjax', 'sendResponse'));

?>
