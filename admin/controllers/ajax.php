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

//jimport('joomla.application.component.controller');


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
//class sportsmanagementControllerAjax extends JControllerForm
{

/**
 * sportsmanagementControllerAjax::__construct()
 * 
 * @return void
 */
public function __construct()
	{
		// Get the document object.
		$document = JFactory::getDocument();
		// Set the MIME type for JSON output.
		$document->setMimeEncoding('application/json');
		parent::__construct();
	}
       // /**
//         * sportsmanagementControllerAjax::__construct()
//         * 
//         * @return
//         */
//        public function __construct()
//        {
//                parent::__construct();
//        }
        
        
 /**
  * sportsmanagementControllerAjax::predictionid()
  * 
  * @return void
  */
 public function predictionid()
        {
            $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
        $model = $this->getModel('ajax');
                echo json_encode((array) $model->getpredictionid($jinput->getVar('cfg_which_database','0'), $jinput->getVar('required','false') ));
                JFactory::getApplication()->close();    
        }    
        
/**
 * sportsmanagementControllerAjax::predictiongroup()
 * 
 * @return void
 */
public function predictiongroups()
        {
            $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
        $model = $this->getModel('ajax');
                echo json_encode((array) $model->getpredictiongroups($jinput->getVar('predictionid','0'), $jinput->getVar('required','false') ));
                JFactory::getApplication()->close();    
        }          
                
/**
 * sportsmanagementControllerAjax::predictionpj()
 * 
 * @return void
 */
public function predictionpj()
        {
            $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
        $model = $this->getModel('ajax');
                echo json_encode((array) $model->getpredictionpj($jinput->getVar('predictionid','0'), $jinput->getVar('required','false') ));
                JFactory::getApplication()->close();    
        }          
                        
        /**
         * sportsmanagementControllerAjax::locationzipcodeoptions()
         * 
         * @return void
         */
        public function locationzipcodeoptions()
        {
            $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
        $model = $this->getModel('ajax');
                echo json_encode((array) $model->getlocationzipcodeoptions(JRequest::getVar( 'zipcode' ), $jinput->getVar('required','false'),JRequest::getInt( 'slug' ),JRequest::getInt( 'dbase' ),JRequest::getVar( 'country' ) ));
                JFactory::getApplication()->close();    
        } 
        
        
        /**
         * sportsmanagementControllerAjax::countryzipcodeoptions()
         * 
         * @return void
         */
        public function countryzipcodeoptions()
        {
            $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
        $model = $this->getModel('ajax');
                echo json_encode((array) $model->getcountryzipcodeoptions(JRequest::getVar( 'country' ), $jinput->getVar('required','false'),JRequest::getInt( 'slug' ),JRequest::getInt( 'dbase' ) ));
                JFactory::getApplication()->close();    
        } 
        
        
        
        /**
         * sportsmanagementControllerAjax::personcontactid()
         * 
         * @return void
         */
        public function personcontactid()
        {
        $app = JFactory::getApplication();    
        $model = $this->getModel('ajax');
        $result = $model->getpersoncontactid( $this->input->get->get('show_user_profile') );
        //echo $result;
        echo json_encode($result);
        //echo new JResponseJson($result);
        //$this->input->get->get('menutype')
        //echo json_encode((array) $model->getProjects( $jinput->get->get('s'), $jinput->get->get('required'),$jinput->get->get('slug'),$jinput->get->get( 'dbase' ) ));
        
        $app->close();        
            
        }
        
        /**
         * sportsmanagementControllerAjax::projects()
         * 
         * @return void
         */
        public function projects()
        {
            $app = JFactory::getApplication();
            
//            $model = $this->getModel('Projects', '', array());
//            $results = $model->getItems();
//            // Output a JSON object
//		echo json_encode($results);
            
//            $db = sportsmanagementHelper::getDBConnection();
//            $query = $db->getQuery(true);
//            $query->select('CONCAT_WS(\':\', p.id, p.alias) AS value,p.name AS text');
//            $query->from('#__sportsmanagement_project as p');
//            $db->setQuery($query);
//            $mitems = array(JHTML::_('select.option', '', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT')));
//            //return array_merge($mitems, $elements);
//            return json_encode(array_merge($mitems, $db->loadObjectList() ) );
            //return json_encode( $db->loadObjectList() );
            
       // JInput object
        //$jinput = $app->input;
        //$menutype = $this->input->get->get('menutype');
        $model = $this->getModel('ajax');
        $result = $model->getProjects( $this->input->get->get('s'), $this->input->get->get('required'),$this->input->get->get('slug'),$this->input->get->get( 'dbase' ) );
        //echo $result;
        echo json_encode($result);
        //echo new JResponseJson($result);
        //$this->input->get->get('menutype')
        //echo json_encode((array) $model->getProjects( $jinput->get->get('s'), $jinput->get->get('required'),$jinput->get->get('slug'),$jinput->get->get( 'dbase' ) ));
        
        $app->close();    
        } 
        

        
        /**
         * sportsmanagementControllerAjax::seasons()
         * 
         * @return void
         */
        public function seasons()
        {
            $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
        $model = $this->getModel('ajax');
                echo json_encode((array) $model->getseasons($jinput->getVar('cfg_which_database','0'), $jinput->getVar('required','false'),$jinput->getVar('slug','false') ));
                JFactory::getApplication()->close();    
        }    
        
        /**
         * sportsmanagementControllerAjax::personlistoptions()
         * 
         * @return void
         */
        public function personlistoptions()
        {
            $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
                $model = $this->getModel('ajax');
                echo json_encode((array) $model->getpersonlistoptions(JRequest::getInt( 'person_art' ), $jinput->getVar('required','false'),JRequest::getInt( 'slug' ),JRequest::getInt( 'dbase' )));
                JFactory::getApplication()->close();
        }
        
        /**
         * sportsmanagementControllerAjax::personpositionoptions()
         * 
         * @return void
         */
        public function personpositionoptions()
        {
            $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
                $model = $this->getModel('ajax');
                echo json_encode((array) $model->getpersonpositionoptions(JRequest::getInt( 'sports_type_id' ), $jinput->getVar('required','false'),JRequest::getInt( 'slug' ),JRequest::getInt( 'dbase' )));
                JFactory::getApplication()->close();
        }
        
        /**
         * sportsmanagementControllerAjax::personagegroupoptions()
         * 
         * @return void
         */
        public function personagegroupoptions()
        {
            $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
                $model = $this->getModel('ajax');
                echo json_encode((array) $model->getpersonagegroupoptions(JRequest::getInt( 'sports_type_id' ), $jinput->getVar('required','false'),JRequest::getInt( 'slug' ),JRequest::getInt( 'dbase' ),JRequest::getInt( 'project' ) ) );
                JFactory::getApplication()->close();
        }

        
        
        /**
         * sportsmanagementControllerAjax::predictionmembersoptions()
         * 
         * @return void
         */
        public function predictionmembersoptions()
        {
            $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
                $model = $this->getModel('ajax');
                echo json_encode((array) $model->getpredictionmembersoptions(JRequest::getInt( 'prediction_id' ), $jinput->getVar('required','false'),$jinput->getVar('slug','false'),JRequest::getInt( 'dbase' )));
                JFactory::getApplication()->close();
        }
        
        /**
         * sportsmanagementControllerAjax::projectdivisionsoptions()
         * 
         * @return
         */
        public function projectdivisionsoptions()
        {
            $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
                $model = $this->getModel('ajax');
                echo json_encode((array) $model->getProjectDivisionsOptions($jinput->getVar('p','0'), $jinput->getVar('required','false'),$jinput->getVar('slug','false'),JRequest::getInt( 'dbase' )));
                JFactory::getApplication()->close();
        }

        /**
         * sportsmanagementControllerAjax::projecteventsoptions()
         * 
         * @return
         */
        public function projecteventsoptions()
        {
            $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
                $model = $this->getModel('ajax');
                echo json_encode((array) $model->getProjectEventsOptions($jinput->getVar('p','0'), $jinput->getVar('required','false'),$jinput->getVar('slug','false'),JRequest::getInt( 'dbase' )));
                JFactory::getApplication()->close();
        }

        /**
         * sportsmanagementControllerAjax::projectteamsbydivisionoptions()
         * 
         * @return
         */
        public function projectteamsbydivisionoptions()
        {
            $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
                $model = $this->getModel('ajax');
                echo json_encode((array) $model->getProjectTeamsByDivisionOptions($jinput->getVar('p','0'), JRequest::getInt( 'division' ), $jinput->getVar('required','false'),$jinput->getVar('slug','false'),JRequest::getInt( 'dbase' )));
                JFactory::getApplication()->close();
        }

        /**
         * sportsmanagementControllerAjax::projectsbysportstypesoptions()
         * 
         * @return
         */
        public function projectsbysportstypesoptions()
        {
            $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
                $model = $this->getModel('ajax');
                echo json_encode((array) $model->getProjectsBySportsTypesOptions(JRequest::getInt('sportstype'), $jinput->getVar('required','false'),$jinput->getVar('slug','false'),JRequest::getInt( 'dbase' )));
                JFactory::getApplication()->close();
        }
        
        /**
         * sportsmanagementControllerAjax::agegroupsbysportstypesoptions()
         * 
         * @return
         */
        public function agegroupsbysportstypesoptions()
        {
            $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
                $model = $this->getModel('ajax');
                echo json_encode((array) $model->getAgeGroupsBySportsTypesOptions(JRequest::getInt('sportstype'), $jinput->getVar('required','false'),$jinput->getVar('slug','false'),JRequest::getInt( 'dbase' )));
                JFactory::getApplication()->close();
        }

        /**
         * sportsmanagementControllerAjax::projectsbycluboptions()
         * 
         * @return
         */
        public function projectsbycluboptions()
        {
            $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
                $model = $this->getModel('ajax');
                echo json_encode((array) $model->getProjectsByClubOptions(JRequest::getInt( 'cid' ), $jinput->getVar('required','false'),$jinput->getVar('slug','false'),JRequest::getInt( 'dbase' )));
                JFactory::getApplication()->close();
        }

        /**
         * sportsmanagementControllerAjax::projectteamsoptions()
         * 
         * @return
         */
        public function projectteamoptions()
        {
            $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
                $model = $this->getModel('ajax');
                echo json_encode((array) $model->getProjectTeamOptions($jinput->getVar('p','0'),$jinput->getVar('required','false'),$jinput->getVar('slug','false'),JRequest::getInt( 'dbase' ) ));
                JFactory::getApplication()->close();
        }
        
        /**
         * sportsmanagementControllerAjax::projectteamsptidoptions()
         * 
         * @return
         */
        public function projectteamsptidoptions()
        {
            $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
                $model = $this->getModel('ajax');
                echo json_encode((array) $model->getProjectTeamPtidOptions($jinput->getVar('p','0'),$jinput->getVar('required','false'),$jinput->getVar('slug','false'),JRequest::getInt( 'dbase' ) ));
                JFactory::getApplication()->close();
        }
        
        /**
         * sportsmanagementControllerAjax::projectplayeroptions()
         * 
         * @return
         */
        public function projectplayeroptions()
        {
            $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
                $model = $this->getModel('ajax');
                echo json_encode((array) $model->getProjectPlayerOptions($jinput->getVar('p','0'),$jinput->getVar('required','false'),$jinput->getVar('slug','false'),JRequest::getInt( 'dbase' )));
                JFactory::getApplication()->close();
        }

        /**
         * sportsmanagementControllerAjax::projectstaffoptions()
         * 
         * @return
         */
        public function projectstaffoptions()
        {
            $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
                $model = $this->getModel('ajax');
                echo json_encode((array) $model->getProjectStaffOptions($jinput->getVar('p','0'),$jinput->getVar('required','false'),$jinput->getVar('slug','false'),JRequest::getInt( 'dbase' )));
                JFactory::getApplication()->close();
        }

        /**
         * sportsmanagementControllerAjax::projectclubsoptions()
         * 
         * @return
         */
        public function projectcluboptions()
        {
            $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
                $model = $this->getModel('ajax');
                echo json_encode((array) $model->getProjectClubOptions($jinput->getVar('p','0'), $jinput->getVar('required','false'),$jinput->getVar('slug','false'),JRequest::getInt( 'dbase' )));
                JFactory::getApplication()->close();
        }

        /**
         * sportsmanagementControllerAjax::projectstatsoptions()
         * 
         * @return
         */
        public function projectstatsoptions()
        {
            $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
                $model = $this->getModel('ajax');
                echo json_encode((array) $model->getProjectStatOptions($jinput->getVar('p','0'), $jinput->getVar('required','false'),$jinput->getVar('slug','false'),JRequest::getInt( 'dbase' )));
                JFactory::getApplication()->close();
        }

        /**
         * sportsmanagementControllerAjax::matchesoptions()
         * 
         * @return
         */
        public function matchesoptions()
        {
            $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
                $model = $this->getModel('ajax');
                echo json_encode((array) $model->getMatchesOptions($jinput->getVar('p','0'), $jinput->getVar('required','false'),$jinput->getVar('slug','false'),JRequest::getInt( 'dbase' )));
                JFactory::getApplication()->close();
        }

        /**
         * sportsmanagementControllerAjax::refereesoptions()
         * 
         * @return
         */
        public function refereesoptions()
        {
            $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
                $model = $this->getModel('ajax');
                echo json_encode((array) $model->getRefereesOptions($jinput->getVar('p','0'), $jinput->getVar('required','false'),$jinput->getVar('slug','false'),JRequest::getInt( 'dbase' )));
                JFactory::getApplication()->close();
        }

        /**
         * sportsmanagementControllerAjax::roundsoptions()
         * 
         * @return
         */
        public function projectroundoptions()
        {
            $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
            $model = $this->getModel('ajax');
                echo json_encode((array) $model->getProjectRoundOptions($jinput->getVar('p','0'), $jinput->getVar('required','false'),$jinput->getVar('slug','false'),'ASC',NULL,JRequest::getInt( 'dbase' )));
                JFactory::getApplication()->close();
        }

        /**
         * sportsmanagementControllerAjax::projecttreenodeoptions()
         * 
         * @return
         */
        public function projecttreenodeoptions()
        {
            $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
                $model = $this->getModel('ajax');
                echo json_encode((array) $model->getProjectTreenodeOptions($jinput->getVar('p','0'), $jinput->getVar('required','false'),$jinput->getVar('slug','false'),JRequest::getInt( 'dbase' )));
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
