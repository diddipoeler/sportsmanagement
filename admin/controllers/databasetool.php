<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      databasetool.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage controllers
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * sportsmanagementControllerDatabaseTool
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllerDatabaseTool extends JControllerLegacy
{

/**
 * sportsmanagementControllerDatabaseTool::repair()
 * 
 * @return void
 */
function repair()  
{  
$app = JFactory::getApplication();  
$model = $this->getModel('databasetool');  
$jsm_tables = $model->getSportsManagementTables();  
  
foreach( $jsm_tables as $key => $value )  
{  
$model->setSportsManagementTableQuery($value , $this->getTask() );  
}  
  
  
$msg = 'Alle Tabellen repariert';   
$this->setRedirect('index.php?option=com_sportsmanagement&view=databasetools',$msg);   
   
}  

/**
 * sportsmanagementControllerDatabaseTool::optimize()
 * 
 * @return void
 */
function optimize()  
{  
$app = JFactory::getApplication();  
$model = $this->getModel('databasetool');  
$jsm_tables = $model->getSportsManagementTables();  

  
foreach( $jsm_tables as $key => $value )  
{  
$model->setSportsManagementTableQuery($value , $this->getTask() );  
}  
  
$msg = 'Alle Tabellen optimiert';   
$this->setRedirect('index.php?option=com_sportsmanagement&view=databasetools',$msg);   	  
}  


/**
 * sportsmanagementControllerDatabaseTool::truncate()
 * 
 * @return void
 */
function truncate()  
{  
$app = JFactory::getApplication();  
$model = $this->getModel('databasetool');  
$jsm_tables = $model->getSportsManagementTables();  
  
foreach( $jsm_tables as $key => $value )  
{  
$model->setSportsManagementTableQuery($value , $this->getTask() );  
}  
  
$msg = 'Alle Tabellen geleert';   
$this->setRedirect('index.php?option=com_sportsmanagement&view=databasetools',$msg);      
}  


/**
 * sportsmanagementControllerDatabaseTool::truncatejl()
 * 
 * @return void
 */
function truncatejl()
{
$app = JFactory::getApplication();  
$model = $this->getModel('databasetool');      
$jl_tables = $model->getJoomleagueTablesTruncate();  

foreach( $jl_tables as $key => $value )  
{  
$model->setSportsManagementTableQuery($value , 'TRUNCATE' );  
}      
$msg = 'Alle JL Tabellen geleert';   
$this->setRedirect('index.php?option=com_sportsmanagement&view=databasetools',$msg);       
}


/**
 * sportsmanagementControllerDatabaseTool::updatetemplatemasters()
 * 
 * @return void
 */
function updatetemplatemasters()
{

    
$msg = 'Alle Templates angepasst';   
$this->setRedirect('index.php?option=com_sportsmanagement&view=databasetools',$msg);    
}

/**
 * sportsmanagementControllerDatabaseTool::picturepath()
 * 
 * @return void
 */
function picturepath()
{
$app = JFactory::getApplication();  
$model = $this->getModel('databasetool');    
$model->setNewPicturePath();
$msg = 'Alle Bilderpfade angepasst';   
$this->setRedirect('index.php?option=com_sportsmanagement&view=databasetools',$msg);               
}

}
?>
