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




/**
 * sportsmanagementViewjlextdfbkeyimport
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementViewjlextdfbkeyimport extends sportsmanagementView
{
    
	/**
	 * sportsmanagementViewjlextdfbkeyimport::init()
	 * 
	 * @return
	 */
	public function init ()
	{
		//global $mainframe;

    /*
    echo '<pre>';
    print_r($this->getTask());
    echo '</pre>';
    */
    
    /*
    echo '<pre>';
    print_r($this->getLayout());
    echo '</pre>';
    */
    
		if ( $this->getLayout() == 'default')
		{
			$this->_displayDefault( $tpl );
			return;
		}

    if ( $this->getLayout() == 'default_createdays')
		{
			$this->_displayDefaultCreatedays( $tpl );
			return;
		}
		
		
		if ( $this->getLayout() == 'default_firstmatchday')
		{
			$this->_displayDefaultFirstMatchday( $tpl );
			return;
		}
		
		if ( $this->getLayout() == 'default_savematchdays')
		{
			$this->_displayDefaultSaveMatchdays( $tpl );
			return;
		}
		
		
		
		
	}

	/**
	 * sportsmanagementViewjlextdfbkeyimport::_displayDefault()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	function _displayDefault( $tpl )
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication ();

		$db		= JFactory::getDBO();
		$uri 	= JFactory::getURI();
		$user 	= JFactory::getUser();
		$model	= $this->getModel();

    //get the project
//		$projectid = $model->getProject();
//		$this->assignRef( 'projectid',		$projectid );
        
        $this->project_id	= $mainframe->getUserState( "$option.pid", '0' );
		
    $istable = $model->checkTable();
    
    if ( empty($this->project_id) )
    {
    JError::raiseWarning( 500, JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_1' ) );
    $mainframe->redirect( 'index.php?option=' . $option .'&view=projects' );
    }
    else
    {
    // project selected. projectteams available ?
    //build the html options for projectteams
		if ( $res =  $model->getProjectteams($this->project_id) )
		{
		   $projectteams[] = JHtml::_( 'select.option', '0', '- ' . JText::_( 'Select projectteams' ) . ' -' );
			 $projectteams = array_merge( $projectteams, $res );
		   $lists['projectteams'] = $projectteams;
		   
       
       $dfbteams = count($projectteams) - 1;
       
       //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($dfbteams,true).'</pre>'),'');
       
       if ( $resdfbkey = $model->getDFBKey($dfbteams,'FIRST') )
		   {
		   $dfbday = array();
		   $dfbday = array_merge( $dfbday, $resdfbkey );
		   $lists['dfbday'] = $dfbday;
		   unset( $dfbday );
		   
		   // matchdays available ?
		   if ( $resmatchdays = $model->getMatchdays($this->project_id) )
		   {
		   
       // matches available
		   if ( $resmatches = $model->getMatches($this->project_id) )
		   {
		   JError::raiseNotice( 500, JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_2' ) );
		   JError::raiseWarning(500,JText::sprintf( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_7' , $resmatches ));
		   $mainframe->redirect( 'index.php?option=' . $option .'&view=rounds' );
		   }
		   else
		   {
//        JError::raiseWarning( 500, JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_3' ) );
//        JError::raiseNotice( 500, JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_4' ) );
       $mainframe->redirect( 'index.php?option=' . $option .'&view=jlextdfbkeyimport&layout=default_firstmatchday' );       
       }
		   
		   
		   }
		   else
		   {
       JError::raiseWarning( 500, JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_3' ) );
       JError::raiseNotice( 500, JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_4' ) );
       $mainframe->redirect( 'index.php?option=' . $option .'&view=jlextdfbkeyimport&layout=default_createdays' );
       }
              
       }
       else
       {
       $procountry = $model->getCountry($this->project_id);
       //JError::raiseWarning( 500, JText::_( '[DFB-Key Tool] Error: No DFB-Key for '.$dfbteams.'  Teams available!' ) );
       JError::raiseWarning(500,JText::sprintf( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_6' , $dfbteams , JSMCountries::getCountryFlag($procountry) , $procountry ));
       $mainframe->redirect( 'index.php?option=' . $option .'&view=projects' );
       }
       
       unset( $projectteams ); 
           
    }
    else
    {
//    JError::raiseNotice( 500, JText::_( '[DFB-Key Tool] Notice: No Teams assigned!' ) );
    JError::raiseError( 500, JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_5' ) );
    $mainframe->redirect( 'index.php?option=' . $option .'&view=projectteams' );
    
    }
    
    }

		
	}


  /**
   * sportsmanagementViewjlextdfbkeyimport::_displayDefaultCreatedays()
   * 
   * @param mixed $tpl
   * @return void
   */
  function _displayDefaultCreatedays( $tpl )
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication ();
		
		$db		= JFactory::getDBO();
		$uri 	= JFactory::getURI();
		$user 	= JFactory::getUser();
		$model	= $this->getModel();
    //$projectid =& $this->projectid;
    //get the project
    //echo '_displayDefaultCreatedays project -> '.$projectid.'<br>';
    
		$projectid = $mainframe->getUserState( "$option.pid", '0' );;
		$this->assignRef( 'projectid',		$projectid );
		
		if ( $res =  $model->getProjectteams($projectid) )
		{
		   $projectteams[] = JHtml::_( 'select.option', '0', '- ' . JText::_( 'Select projectteams' ) . ' -' );
			 $projectteams = array_merge( $projectteams, $res );
		   //$lists['projectteams'] = $projectteams;
		   
       
       $dfbteams = count($projectteams) - 1;
       if ( $resdfbkey = $model->getDFBKey($dfbteams,'ALL') )
		   {
		   /*
		   echo '<pre>';
		   print_r($resdfbkey);
		   echo '</pre>';
		   */
		   $this->assignRef( 'newmatchdays',		$resdfbkey );
		   }
		   unset( $projectteams ); 
		}
		
		$this->assign ( 'request_url', $uri->toString () );
        
        // Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        
        // Set toolbar items for the page
		JToolBarHelper::title( JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_MATCHDAY_INFO_1' ),'dfbkey' );
        JToolBarHelper::save('jlextdfbkeyimport.save', 'JTOOLBAR_SAVE');
        JToolBarHelper::divider();
            sportsmanagementHelper::ToolbarButtonOnlineHelp();
			JToolBarHelper::preferences($option);
        
		

		
	}
  
  /**
   * sportsmanagementViewjlextdfbkeyimport::_displayDefaultFirstMatchday()
   * 
   * @param mixed $tpl
   * @return void
   */
  function _displayDefaultFirstMatchday( $tpl )
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication ();
		
		$db		= JFactory::getDBO();
		$uri 	= JFactory::getURI();
		$user 	= JFactory::getUser();
		$model	= $this->getModel();
        
        $projectid = $mainframe->getUserState( "$option.pid", '0' );;
		
		if ( $res =  $model->getProjectteams($projectid) )
		{
		   $projectteams[] = JHtml::_( 'select.option', '0', '- ' . JText::_( 'Select projectteams' ) . ' -' );
			 $projectteams = array_merge( $projectteams, $res );
		   $lists['projectteams'] = $projectteams;
		   
       
       $dfbteams = count($projectteams) - 1;
       if ( $resdfbkey = $model->getDFBKey($dfbteams,'FIRST') )
		   {
		   $dfbday = array();
		   $dfbday = array_merge( $dfbday, $resdfbkey );
		   $lists['dfbday'] = $dfbday;
		   unset( $dfbday );
		   }
		
		}
		
		$this->assignRef( 'lists',			$lists );
    $this->assignRef( 'dfbteams',			$dfbteams );
    $this->assign ( 'request_url', $uri->toString () );
		
        // Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        
        // Set toolbar items for the page
		JToolBarHelper::title( JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_FIRST_MATCHDAY_INFO_1' ),'dfbkey' );
        JToolBarHelper::apply('jlextdfbkeyimport.apply', 'JTOOLBAR_APPLY');
        JToolBarHelper::divider();
            sportsmanagementHelper::ToolbarButtonOnlineHelp();
			JToolBarHelper::preferences($option);
        
		
	}
  
  /**
   * sportsmanagementViewjlextdfbkeyimport::_displayDefaultSaveMatchdays()
   * 
   * @param mixed $tpl
   * @return void
   */
  function _displayDefaultSaveMatchdays( $tpl )
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication ();
		
		$db		= JFactory::getDBO();
		$uri 	= JFactory::getURI();
		$user 	= JFactory::getUser();
		$model	= $this->getModel();
		
		$projectid = $mainframe->getUserState( "$option.pid", '0' );;
		$this->assignRef( 'projectid',		$projectid );
		
		$post = JRequest::get( 'post' );
    $this->assignRef( 'import', $model->getSchedule( $post, $projectid ) );
	$this->assign ( 'request_url', $uri->toString () );
    	
	// Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        
        // Set toolbar items for the page
		JToolBarHelper::title( JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_SAVE_MATCHDAY_INFO_1' ),'dfbkey' );
        JToolBarHelper::save('jlextdfbkeyimport.insert', 'JTOOLBAR_SAVE');
        JToolBarHelper::divider();
            sportsmanagementHelper::ToolbarButtonOnlineHelp();
			JToolBarHelper::preferences($option);
        	
  	
	}
  	
}
?>