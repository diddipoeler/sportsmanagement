<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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

jimport( 'joomla.application.component.controller' );
//require_once ( JPATH_COMPONENT . DS . 'controllers' . DS . 'joomleague.php' );


/**
 * sportsmanagementControllerjlextdfbkeyimport
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementControllerjlextdfbkeyimport extends JControllerLegacy
{

/**
 * sportsmanagementControllerjlextdfbkeyimport::__construct()
 * 
 * @return void
 */
function __construct()
    {
        parent::__construct();
        $this->registerTask( 'save' , 'Save' );
        $this->registerTask( 'apply' , 'Apply' );
        $this->registerTask( 'insert' , 'insert' );
        
    }
    
/**
 * sportsmanagementControllerjlextdfbkeyimport::display()
 * 
 * @return void
 */
function display()  
{


//global $app,$option;

$document	=& JFactory::getDocument();
		$app	=& JFactory::getApplication();
    $model = $this->getModel('jlextdfbkeyimport');
    $post = JFactory::getApplication()->input->get( 'post' );
    
    /*
    echo '<pre>';
    print_r($post);
    echo '</pre>';
    */
    
    /*
    echo '<pre>';
    print_r($this->getTask());
    echo '</pre>';
    */
/*    
    // Checkout the project
				//$model = $this->getModel('dfbkeys');
        $model	=& $this->getModel();
    
    $projectid = $model->getProject();
    echo '_loadData projekt -> '.$projektid.'<br>';
    $msg = JText::sprintf( 'DESCBEINGEDITTED', JText::_( 'The division' ), $projectid );
		$this->setRedirect( 'index.php?option=' . $option., $msg );
*/
    switch( $this->getTask() )
		{
    
    case 'apply'	 :
			{
				//JFactory::getApplication()->input->setVar( 'hidemainmenu', 1 );
				JFactory::getApplication()->input->setVar( 'layout', 'default_savematchdays' );
				JFactory::getApplication()->input->setVar( 'view', 'jlextdfbkeyimport' );
				//JFactory::getApplication()->input->setVar( 'edit', false );
				
				/*
 				$model = $this->getModel ( 'round' );
				$viewType = $document->getType();
				$view = $this->getView( 'round', $viewType );
				$view->setModel( $model, true );	// true is for the default model;

 				$projectws = $this->getModel( 'project' );
				$projectws->_name = 'projectws';
				$projectws->setId( $app->getUserState( $option . 'project', 0 ) );
				$view->setModel( $projectws );

				$model = $this->getModel( 'round' );
				$model->checkout();
				*/
			} break;
    
    }
    
        
    parent::display();
    
    }    
    
  
  /**
   * sportsmanagementControllerjlextdfbkeyimport::save()
   * 
   * @return void
   */
  function save()
	{
		$option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication ();
        $post = JFactory::getApplication()->input->get( 'post' );

    /*
    echo '<pre>';
    print_r($post);
    echo '</pre>';
		*/
		//echo 'einträge -> '.count($post[roundcode]).'<br>';
		
    	
		for ($i=0; $i < count($post[roundcode])  ; $i++)
		{
//    $table = 'round';
//		$row = JTable::getInstance( $table, 'sportsmanagementTable' );
        $mdl = JModelLegacy::getInstance("round", "sportsmanagementModel");
        $row = $mdl->getTable();
            
    $row->roundcode = $post[roundcode][$i];
    $row->project_id = $post[projectid]; 
    $row->name = $post[name][$i];
    
    // convert dates back to mysql date format
		if (isset($post[round_date_first][$i])) 
    {
    	$post[round_date_first][$i] = strtotime($post[round_date_first][$i]) ? strftime('%Y-%m-%d', strtotime($post[round_date_first][$i])) : null;
		}
		else 
    {
			$post[round_date_first][$i] = '0000-00-00';
		}
		if (isset($post[round_date_last][$i])) 
    {
    	$post[round_date_last][$i] = strtotime($post[round_date_last][$i]) ? strftime('%Y-%m-%d', strtotime($post[round_date_last][$i])) : null;
		}
		else 
    {
			$post[round_date_last][$i] = '0000-00-00';
		}
		
    $row->round_date_first = $post[round_date_first][$i];
    $row->round_date_last = $post[round_date_last][$i];
    
    if ( !$row->store() )
		{
		$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($this->_db->getErrorMsg(),true).'</pre>'),'Error');
        //$this->setError( $this->_db->getErrorMsg() );
		//return false;
		}
		
    /*
    if ( $model->store( $post ) )
		{
			$msg = JText::_( 'Matchday added' );
		}
		else
		{
			$msg = JText::_( 'Error adding Matchday' ) . $model->getError();
		}
		*/
    
    }
		/*
		echo '<pre>';
    print_r($model);
    echo '</pre>';
    */
    //JFactory::getApplication()->input->setVar( 'layout', 'default_savematchdays' );
    $msg = JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_INFO_2' );
    $link = 'index.php?option='.$option.'&view=jlextdfbkeyimport&layout=default_firstmatchday';
		$this->setRedirect( $link, $msg );
	}
    

  /**
   * sportsmanagementControllerjlextdfbkeyimport::insert()
   * 
   * @return void
   */
  function insert()
	{
		$option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication ();
        $post = JFactory::getApplication()->input->get( 'post' );
      
    /*    
    echo '<pre>';
    print_r($post);
    echo '</pre>';
    */
    
    for ($i=0; $i < count($post[roundcode])  ; $i++)
		{
//    $table = 'match';
//		$row = JTable::getInstance( $table, 'sportsmanagementTable' );
        $mdl = JModelLegacy::getInstance("match", "sportsmanagementModel");
        $row = $mdl->getTable();
		
		$row->round_id = $post[round_id][$i];
		$row->match_number = $post[match_number][$i];
		$row->projectteam1_id = $post[projectteam1_id][$i];
		$row->projectteam2_id = $post[projectteam2_id][$i];
		$row->published = 1;
		
		// convert dates back to mysql date format
		if (isset($post[match_date][$i])) 
    {
    	$post[match_date][$i] = strtotime($post[match_date][$i]) ? strftime('%Y-%m-%d', strtotime($post[match_date][$i])) : null;
		}
		else 
    {
			$post[match_date][$i] = null;
		}
		
		$row->match_date = $post[match_date][$i];
		
		if ( !$row->store() )
		{
		$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($this->_db->getErrorMsg(),true).'</pre>'),'Error');
        //$this->setError( $this->_db->getErrorMsg() );
		//return false;
		//echo 'nicht eingefügt <br>';
		}
		else
		{
    //echo 'eingefügt <br>';
    }
		
		}
    
    
    $msg = JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_INFO_1' );
    $link = 'index.php?option='.$option.'&view=rounds';
		$this->setRedirect( $link, $msg );
		
		
  }    
    
}    


?>
