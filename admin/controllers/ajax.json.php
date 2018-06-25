<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      ajax.json.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage controllers
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
                $this->app = JFactory::getApplication();
                // JInput object
        $this->jinput = $this->app->input;
        }
        
        
 /**
  * sportsmanagementControllerAjax::predictionpj()
  * 
  * @return void
  */
 public function predictionpj()
        {

        try
    {
 
      $result = $this->getModel('ajax')->getpredictionpj($this->jinput->get->getString('prediction_id'),
       $this->jinput->get->getString('required'),
       $this->jinput->get->getString('slug'),
       $this->jinput->get->getString('dbase') );
      
 if ( count($result) == 1 )
 {
 $this->app->enqueueMessage('Keine Projekte gefunden','Error');
 }
 else
 {
$this->app->enqueueMessage('Projekte gefunden','Message');    
 }
 
      echo new JResponseJson($result);
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }

        } 
        
 /**
  * sportsmanagementControllerAjax::predictiongroup()
  * 
  * @return void
  */
 public function predictiongroups()
        {

        try
    {
 
      $result = $this->getModel('ajax')->getpredictiongroups($this->jinput->get->getString('prediction_id'),
       $this->jinput->get->getString('required'),
       $this->jinput->get->getString('slug'),
       $this->jinput->get->getString('dbase') );
      
 if ( count($result) == 1 )
 {
 $this->app->enqueueMessage('Keine Gruppen gefunden','Error');
 }
 else
 {
$this->app->enqueueMessage('Gruppen gefunden','Message');    
 }
 
      echo new JResponseJson($result);
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }

        } 
                        
/**
 * sportsmanagementControllerAjax::getpredictionid()
 * 
 * @return void
 */
public function getpredictionid()
        {
               
try
    {
 
      $result = $this->getModel('ajax')->getgetpredictionid($this->jinput->get->getString('cfg_which_database'),
       $this->jinput->get->getString('required'),
       $this->jinput->get->getString('slug') );
 
      echo new JResponseJson($result);
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }
                     
        }   
        
                
        /**
         * sportsmanagementControllerAjax::personcontactid()
         * 
         * @return void
         */
        public function personcontactid()
        {
        try
    {
 
      $result = $this->getModel('ajax')->getpersoncontactid($this->jinput->get->getString('show_user_profile'),
       $this->jinput->get->getString('required') );

      
 if ( count($result) == 1 )
 {
 $this->app->enqueueMessage('Keine Benutzer gefunden','Error');
 }
 else
 {
$this->app->enqueueMessage('Benutzer gefunden','Message');    
 }
 
      echo new JResponseJson($result);
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }         
            
            
        }    
        
        /**
         * sportsmanagementControllerAjax::locationzipcodeoptions()
         * 
         * @return void
         */
        public function locationzipcodeoptions()
        {
                   
try
    {
 
      $result = $this->getModel('ajax')->getlocationzipcodeoptions($this->jinput->get->getString('zipcode'),
       $this->jinput->get->getString('required'),
       $this->jinput->get->getString('slug'),
       $this->jinput->get->getString('dbase'),
       $this->jinput->get->getString('country') );
      
 if ( count($result) == 1 )
 {
 //$this->app->enqueueMessage('Keine Altersgruppen gefunden','Error');
 }
 else
 {
//$this->app->enqueueMessage('Altersgruppen gefunden','Message');    
 }
 
      echo new JResponseJson($result);
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }        
            
        } 
        
        
        /**
         * sportsmanagementControllerAjax::countryzipcodeoptions()
         * 
         * @return void
         */
        public function countryzipcodeoptions()
        {
                    
try
    {
 
      $result = $this->getModel('ajax')->getcountryzipcodeoptions($this->jinput->get->getString('country'),
       $this->jinput->get->getString('required'),
       $this->jinput->get->getString('slug'),
       $this->jinput->get->getString('dbase'),
       $this->jinput->get->getString('p') );
      
 if ( count($result) == 1 )
 {
 //$this->app->enqueueMessage('Keine Altersgruppen gefunden','Error');
 }
 else
 {
//$this->app->enqueueMessage('Altersgruppen gefunden','Message');    
 }
 
      echo new JResponseJson($result);
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }                
        
        } 
        
        
        
        /**
         * sportsmanagementControllerAjax::countryclubagegroupoptions()
         * 
         * @return void
         */
        public function countryclubagegroupoptions()
        {
       
        
         try
    {
 
      $result = $this->getModel('ajax')->getcountryclubagegroupoptions($this->jinput->get->getString('club_id'),
       $this->jinput->get->getString('required'),
       $this->jinput->get->getString('slug'),
       $this->jinput->get->getString('dbase') );
      
 if ( count($result) == 1 )
 {
 $this->app->enqueueMessage('Keine Altersgruppen gefunden','Error');
 }
 else
 {
$this->app->enqueueMessage('Altersgruppen gefunden','Message');    
 }
 
      echo new JResponseJson($result);
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }        
            
        }
        
        /**
         * sportsmanagementControllerAjax::associationsoptions()
         * 
         * @return void
         */
        public function associationsoptions()
        {
            

        try
    {
 
      $result = $this->getModel('ajax')->getassociationsoptions($this->jinput->get->getString('country'),
       $this->jinput->get->getString('required'),
       $this->jinput->get->getString('slug'),
       $this->jinput->get->getString('dbase') );
     
 if ( count($result) == 1 )
 {
 $this->app->enqueueMessage('Keine Landesverbände gefunden','Error');
 }
 else
 {
$this->app->enqueueMessage('Landesverbände gefunden','Message');    
 }
 
      echo new JResponseJson($result);
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }    
            
            
        }
        
        /**
         * sportsmanagementControllerAjax::projects()
         * 
         * @return void
         */
        public function projects()
        {

        try
    {
 
      $result = $this->getModel('ajax')->getProjects($this->jinput->get->getString('s'),
       $this->jinput->get->getString('required'),
       $this->jinput->get->getString('slug'),
       $this->jinput->get->getString('dbase') );
      
 if ( count($result) == 1 )
 {
 $this->app->enqueueMessage('Keine Projekte gefunden','Error');
 }
 else
 {
$this->app->enqueueMessage('Projekte gefunden','Message');    
 }
 
      echo new JResponseJson($result);
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }

        } 
        

        
        /**
         * sportsmanagementControllerAjax::seasons()
         * 
         * @return void
         */
        public function seasons()
        {
               
try
    {
 
      $result = $this->getModel('ajax')->getseasons($this->jinput->get->getString('cfg_which_database'),
       $this->jinput->get->getString('required'),
       $this->jinput->get->getString('slug') );
 
      echo new JResponseJson($result);
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }
                     
        }    
        
        /**
         * sportsmanagementControllerAjax::personlistoptions()
         * 
         * @return void
         */
        public function personlistoptions()
        {
            try
    {
 
      $result = $this->getModel('ajax')->getpersonlistoptions($this->jinput->get->getString('person_art'),
      $this->jinput->get->getString('required'),
      $this->jinput->get->getString('slug'),
      $this->jinput->get->getString('dbase') );
        echo new JResponseJson($result);
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }
            
        }
        
        /**
         * sportsmanagementControllerAjax::personpositionoptions()
         * 
         * @return void
         */
        public function personpositionoptions()
        {
                          
try
    {
 
      $result = $this->getModel('ajax')->getpersonpositionoptions($this->jinput->get->getString('sports_type_id'),
      $this->jinput->get->getString('required'),
      $this->jinput->get->getString('slug'),
      $this->jinput->get->getString('dbase') );
      
 if ( count($result) == 1 )
 {
 $this->app->enqueueMessage('Keine Positionen gefunden','Error');
 }
 else
 {
$this->app->enqueueMessage('Positionen gefunden','Message');    
 }
 
      echo new JResponseJson($result);
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }    
                    
        }
        
        /**
         * sportsmanagementControllerAjax::personagegroupoptions()
         * 
         * @return void
         */
        public function personagegroupoptions()
        {
           
        try
    {
 
      $result = $this->getModel('ajax')->getpersonagegroupoptions($this->jinput->get->getString('sports_type_id'),
      $this->jinput->get->getString('required'),
      $this->jinput->get->getString('slug'),
      $this->jinput->get->getString('dbase'),
      $this->jinput->get->getString('project'),
      $this->jinput->get->getString('country') );
      
 if ( count($result) == 1 )
 {
 $app->enqueueMessage('Keine Altersgruppe gefunden','Error');
 }
 else
 {
$app->enqueueMessage('Altersgruppe gefunden','Message');    
 }
 
      echo new JResponseJson($result);
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }  
        
        }

        
        
        /**
         * sportsmanagementControllerAjax::predictionmembersoptions()
         * 
         * @return void
         */
        public function predictionmembersoptions()
        {
                    
      try
    {
 
      $result = $this->getModel('ajax')->getpredictionmembersoptions($this->jinput->get->getString('prediction_id'),
      $this->jinput->get->getString('required'),
      $this->jinput->get->getString('slug'),
      $this->jinput->get->getString('dbase') );
 if ( count($result) == 1 )
 {
 $this->app->enqueueMessage('Keine Gruppen gefunden','Error');
 }
 else
 {
$this->app->enqueueMessage('Gruppen gefunden','Message');    
 }
      echo new JResponseJson($result);
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }  
          
        }
        
        /**
         * sportsmanagementControllerAjax::projectdivisionsoptions()
         * 
         * @return
         */
        public function projectdivisionsoptions()
        {
                          
    try
    {
 
      $result = $this->getModel('ajax')->getProjectDivisionsOptions($this->jinput->get->getString('p'),
      $this->jinput->get->getString('required'),
      $this->jinput->get->getString('slug'),
      $this->jinput->get->getString('dbase') );
 if ( count($result) == 1 )
 {
 $this->app->enqueueMessage('Keine Gruppen gefunden','Error');
 }
 else
 {
$this->app->enqueueMessage('Gruppen gefunden','Message');    
 }
      echo new JResponseJson($result);
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }                
        }

        /**
         * sportsmanagementControllerAjax::projecteventsoptions()
         * 
         * @return
         */
        public function projecteventsoptions()
        {
            
 try
    {
 
      $result = $this->getModel('ajax')->getProjectEventsOptions($this->jinput->get->getString('p'),
      $this->jinput->get->getString('required'),
      $this->jinput->get->getString('slug'),
      $this->jinput->get->getString('dbase') );
 if ( count($result) == 1 )
 {
 //$this->app->enqueueMessage('Keine Gruppen gefunden','Error');
 }
 else
 {
//$this->app->enqueueMessage('Gruppen gefunden','Message');    
 }
      echo new JResponseJson($result);
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }  
                   
        }

        /**
         * sportsmanagementControllerAjax::projectteamsbydivisionoptions()
         * 
         * @return
         */
        public function projectteamsbydivisionoptions()
        {
                           
try
    {
 
      $result = $this->getModel('ajax')->getProjectTeamsByDivisionOptions($this->jinput->get->getString('p'),
      $this->jinput->get->getString('division'),
      $this->jinput->get->getString('required'),
      $this->jinput->get->getString('slug'),
      $this->jinput->get->getString('dbase') );
 
      echo new JResponseJson($result);
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }
                    
        }

        /**
         * sportsmanagementControllerAjax::projectsbysportstypesoptions()
         * 
         * @return
         */
        public function projectsbysportstypesoptions()
        {
       
try
    {
 
      $result = $this->getModel('ajax')->getProjectsBySportsTypesOptions($this->jinput->get->getString('sportstype'),
      $this->jinput->get->getString('required'),
      $this->jinput->get->getString('slug'),
      $this->jinput->get->getString('dbase') );
 
      echo new JResponseJson($result);
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }        
        
        
        }
        
        /**
         * sportsmanagementControllerAjax::agegroupsbysportstypesoptions()
         * 
         * @return
         */
        public function agegroupsbysportstypesoptions()
        {
                  
try
    {
 
      $result = $this->getModel('ajax')->getAgeGroupsBySportsTypesOptions($this->jinput->get->getString('sportstype'),
      $this->jinput->get->getString('required'),
      $this->jinput->get->getString('slug'),
      $this->jinput->get->getString('dbase') );
 
      echo new JResponseJson($result);
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }      
            
        }

        /**
         * sportsmanagementControllerAjax::projectsbycluboptions()
         * 
         * @return
         */
        public function projectsbycluboptions()
        {
                    
try
    {
 
      $result = $this->getModel('ajax')->getProjectsByClubOptions($this->jinput->get->getString('cid'),
      $this->jinput->get->getString('required'),
      $this->jinput->get->getString('slug'),
      $this->jinput->get->getString('dbase') );
 
      echo new JResponseJson($result);
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }            
        
        }

        /**
         * sportsmanagementControllerAjax::projectteamsoptions()
         * 
         * @return
         */
        public function projectteamoptions()
        {
                           
try
    {
 
      $result = $this->getModel('ajax')->getProjectTeamOptions($this->jinput->get->getString('p'),
      $this->jinput->get->getString('required'),
      $this->jinput->get->getString('slug'),
      $this->jinput->get->getString('dbase') );
 
      echo new JResponseJson($result);
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }
                    
        }
        
        /**
         * sportsmanagementControllerAjax::projectteamsptidoptions()
         * 
         * @return
         */
        public function projectteamsptidoptions()
        {
          
 
 try
    {
 
      $result = $this->getModel('ajax')->getProjectTeamPtidOptions($this->jinput->get->getString('p'),
      $this->jinput->get->getString('required'),
      $this->jinput->get->getString('slug'),
      $this->jinput->get->getString('dbase') );
 
      echo new JResponseJson($result);
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }
           
        }
        
        /**
         * sportsmanagementControllerAjax::projectplayeroptions()
         * 
         * @return
         */
        public function projectplayeroptions()
        {
            
try
    {
 
      $result = $this->getModel('ajax')->getProjectPlayerOptions($this->jinput->get->getString('p'),
      $this->jinput->get->getString('required'),
      $this->jinput->get->getString('slug'),
      $this->jinput->get->getString('dbase') );
 
      echo new JResponseJson($result);
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }
           
       
        }

        /**
         * sportsmanagementControllerAjax::projectstaffoptions()
         * 
         * @return
         */
        public function projectstaffoptions()
        {
                    
try
    {
 
      $result = $this->getModel('ajax')->getProjectStaffOptions($this->jinput->get->getString('p'),
      $this->jinput->get->getString('required'),
      $this->jinput->get->getString('slug'),
      $this->jinput->get->getString('dbase') );
 
      echo new JResponseJson($result);
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }
            
        }

        /**
         * sportsmanagementControllerAjax::projectclubsoptions()
         * 
         * @return
         */
        public function projectcluboptions()
        {
                   
try
    {
 
      $result = $this->getModel('ajax')->getProjectClubOptions($this->jinput->get->getString('p'),
      $this->jinput->get->getString('required'),
      $this->jinput->get->getString('slug'),
      $this->jinput->get->getString('dbase') );
 
      echo new JResponseJson($result);
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }
            
        }

        /**
         * sportsmanagementControllerAjax::projectstatsoptions()
         * 
         * @return
         */
        public function projectstatsoptions()
        {
            
try
    {
 
      $result = $this->getModel('ajax')->getProjectStatOptions($this->jinput->get->getString('p'),
      $this->jinput->get->getString('required'),
      $this->jinput->get->getString('slug'),
      $this->jinput->get->getString('dbase') );
 
      echo new JResponseJson($result);
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }
            
        }

        /**
         * sportsmanagementControllerAjax::matchesoptions()
         * 
         * @return
         */
        public function matchesoptions()
        {
            
try
    {
 
      $result = $this->getModel('ajax')->getMatchesOptions($this->jinput->get->getString('p'),
      $this->jinput->get->getString('required'),
      $this->jinput->get->getString('slug'),
      $this->jinput->get->getString('dbase') );
 
      echo new JResponseJson($result);
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }
            
        }

        /**
         * sportsmanagementControllerAjax::refereesoptions()
         * 
         * @return
         */
        public function refereesoptions()
        {
                   
try
    {
 
      $result = $this->getModel('ajax')->getRefereesOptions($this->jinput->get->getString('p'),
      $this->jinput->get->getString('required'),
      $this->jinput->get->getString('slug'),
      $this->jinput->get->getString('dbase') );
 
      echo new JResponseJson($result);
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }
           
        }

        /**
         * sportsmanagementControllerAjax::roundsoptions()
         * 
         * @return
         */
        public function projectroundoptions()
        {
                           
        try
    {

      $result = $this->getModel('ajax')->getProjectRoundOptions($this->jinput->get->getString('p'),
      $this->jinput->get->getString('required'),
      $this->jinput->get->getString('slug'),
      'ASC',
      NULL,
      $this->jinput->get->getString('dbase') );
      
  if ( count($result) == 1 )
 {
 $this->app->enqueueMessage('Keine Runden gefunden','Error');
 }
 else
 {
$this->app->enqueueMessage('Runden gefunden','Message');    
 }
      echo new JResponseJson($result);
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }        
                
        }

        /**
         * sportsmanagementControllerAjax::projecttreenodeoptions()
         * 
         * @return
         */
        public function projecttreenodeoptions()
        {
            
        
try
    {
 
      $result = $this->getModel('ajax')->getProjectTreenodeOptions($this->jinput->get->getString('p'),
      $this->jinput->get->getString('required'),
      $this->jinput->get->getString('slug'),
      $this->jinput->get->getString('dbase') );
 
      echo new JResponseJson($result);
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }
            
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

?>