<?php
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
* @version         1.0.05
* @file                jlextdfbnetplayerimport.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k�nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp�teren
* ver�ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n�tzlich sein wird, aber
* OHNE JEDE GEW�HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew�hrleistung der MARKTF�HIGKEIT oder EIGNUNG F�R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f�r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// Check to ensure this file is included in Joomla!
defined ( '_JEXEC' ) or die ( 'Restricted access' );

jimport ( 'joomla.application.component.view' );



class sportsmanagementViewjlextsisimport extends JView 
{
	function display($tpl = null) 
    {
		//global $mainframe;
		
		if ($this->getLayout () == 'default') {
			$this->_displayDefault ( $tpl );
			return;
		}
		
        /*
		if ($this->getLayout () == 'default_edit') {
			$this->_displayDefaultEdit ( $tpl );
			return;
		}
		
		if ($this->getLayout () == 'default_update') {
			$this->_displayDefaultUpdate ( $tpl );
			return;
		}
		
		if ($this->getLayout () == 'info') {
			$this->_displayInfo ( $tpl );
			return;
		}
		
		if ($this->getLayout () == 'selectpage') {
			$this->_displaySelectpage ( $tpl );
			return;
		}
		*/
        
		// Set toolbar items for the page
		//JToolBarHelper::title ( JText::_ ( 'COM_JOOMLEAGUE_ADMIN_LMO_IMPORT_TITLE_1_3' ), 'generic.png' );
		//JToolBarHelper::help ( 'screen.joomleague', true );
		
		$uri = JFactory::getURI ();
		$config = JComponentHelper::getParams ( 'com_media' );
		$post = JRequest::get ( 'post' );
		$files = JRequest::get ( 'files' );
		
		$this->assignRef ( 'request_url', $uri->toString () );
		$this->assignRef ( 'config', $config );
		
		$revisionDate = '2011-04-28 - 12:00';
		$this->assignRef ( 'revisionDate', $revisionDate );
		
		parent::display ( $tpl );
	}
	
    
    
	function _displayDefault($tpl) 
    {
		//global $option;
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication ();
		$db = JFactory::getDBO ();
		$uri = JFactory::getURI ();
		$user = JFactory::getUser ();
		
		// $model = $this->getModel('project') ;
		// $projectdata = $this->get('Data');
		// $this->assignRef( 'name', $projectdata->name);
		
		$model = $this->getModel ();
		$project = $mainframe->getUserState ( $option . 'project' );
		$this->assignRef ( 'project', $project );
		$config = JComponentHelper::getParams ( 'com_media' );
        $params = JComponentHelper::getParams( $option );
        $sis_xmllink	= $params->get( 'sis_xmllink' );
        $sis_nummer	= $params->get( 'sis_meinevereinsnummer' );
        $sis_passwort	= $params->get( 'sis_meinvereinspasswort' );
		
//        $mainframe->enqueueMessage(JText::_('sis_xmllink<br><pre>'.print_r($sis_xmllink,true).'</pre>'   ),'');
//        $mainframe->enqueueMessage(JText::_('sis_meinevereinsnummer<br><pre>'.print_r($sis_nummer,true).'</pre>'   ),'');
//        $mainframe->enqueueMessage(JText::_('sis_meinvereinspasswort<br><pre>'.print_r($sis_passwort,true).'</pre>'   ),'');
        
		$this->assign ( 'request_url', $uri->toString () );
		$this->assignRef ( 'config', $config );
		$revisionDate = '2011-04-28 - 12:00';
		$this->assignRef ( 'revisionDate', $revisionDate );
		$import_version = 'NEW';
		$this->assignRef ( 'import_version', $import_version );
		
		$this->addToolbar ();
		parent::display ( $tpl );
	}
    
    
	function _displayDefaultUpdate($tpl) 
    {
		// global $mainframe, $option;
		$mainframe = & JFactory::getApplication ();
		$option = JRequest::getCmd ( 'option' );
		
		$db = JFactory::getDBO ();
		$uri = JFactory::getURI ();
		$user = JFactory::getUser ();
		$model = $this->getModel ();
		//$option = 'com_joomleague';
		$project = $mainframe->getUserState ( $option . 'project' );
		$this->assignRef ( 'project', $project );
		$config = JComponentHelper::getParams ( 'com_media' );
		
		$uploadArray = $mainframe->getUserState ( $option . 'uploadArray', array () );
		$lmoimportuseteams = $mainframe->getUserState ( $option . 'lmoimportuseteams' );
		$whichfile = $mainframe->getUserState ( $option . 'whichfile' );
		//$delimiter = $mainframe->getUserState ( $option . 'delimiter' );
		
		$this->assignRef ( 'uploadArray', $uploadArray );
		
		$this->assignRef ( 'importData', $model->getUpdateData () );
		
		// $this->assignRef('xml',$model->getData());
		
		parent::display ( $tpl );
	}
    
    
    
	protected function addToolbar() 
    {
         // Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        $option = JRequest::getCmd('option');
		
        
        
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        
        // Set toolbar items for the page
		JToolBarHelper::title( JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_SIS_IMPORT' ),'sisimport' );
        JToolBarHelper::divider();
            sportsmanagementHelper::ToolbarButtonOnlineHelp();
			JToolBarHelper::preferences($option);

	}
}

?>
