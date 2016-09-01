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
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jsm_tables<br><pre>'.print_r($this->getTask(),true).'</pre>'),'');	  
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jsm_tables<br><pre>'.print_r($jsm_tables,true).'</pre>'),'');	  
  
foreach( $jsm_tables as $key => $value )  
{  
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jsm_tables<br><pre>'.print_r($value,true).'</pre>'),'');	  
$model->setSportsManagementTableQuery($value , $this->getTask() );  
}  
  
  
$msg = 'Alle Tabellen repariert';   
$this->setRedirect('index.php?option=com_sportsmanagement&view=databasetools',$msg);   
//$this->setRedirect('index.php?option='.$this->option.'&view='.$this->view_list,$msg);   
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
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jsm_tables<br><pre>'.print_r($this->getTask(),true).'</pre>'),'');	  
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jsm_tables<br><pre>'.print_r($jsm_tables,true).'</pre>'),'');	  
  
foreach( $jsm_tables as $key => $value )  
{  
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jsm_tables<br><pre>'.print_r($value,true).'</pre>'),'');	  
$model->setSportsManagementTableQuery($value , $this->getTask() );  
}  
  
$msg = 'Alle Tabellen optimiert';   
//$this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component',$msg);   
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jsm_tables<br><pre>'.print_r($this->getTask(),true).'</pre>'),'');	  
//$this->setRedirect('index.php?option=com_sportsmanagement&view=close',$msg);   
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
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jsm_tables<br><pre>'.print_r($this->getTask(),true).'</pre>'),'');	  
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jsm_tables<br><pre>'.print_r($jsm_tables,true).'</pre>'),'');	  
  
foreach( $jsm_tables as $key => $value )  
{  
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jsm_tables<br><pre>'.print_r($value,true).'</pre>'),'');	  
$model->setSportsManagementTableQuery($value , $this->getTask() );  
}  
  
$msg = 'Alle Tabellen geleert';   
//$this->setRedirect('index.php?option=com_sportsmanagement&view=databasetools',$msg);   
//$this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component&info=truncate',$msg);
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
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jl_tables<br><pre>'.print_r($jl_tables,true).'</pre>'),'');	  

foreach( $jl_tables as $key => $value )  
{  
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jsm_tables<br><pre>'.print_r($value,true).'</pre>'),'');	  
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

//$jsm_tables = $model->getSportsManagementTables();      
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jsm_tables<br><pre>'.print_r($jsm_tables,true).'</pre>'),'');	      
//foreach( $jsm_tables as $key => $value )  
//{  
//if ( preg_match("/sportsmanagement/i", $value )  )
//{
//// update #__sportsmanagement_project set name = replace(name, 'Kreisligen', 'Kreisliga')";    
//}
//
//}

$msg = 'Alle Bilderpfade angepasst';   
$this->setRedirect('index.php?option=com_sportsmanagement&view=databasetools',$msg);               
}

}
?>
