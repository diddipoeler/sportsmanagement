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





/**
 * sportsmanagementViewjlextdbbimport
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewjlextdbbimport extends sportsmanagementView 
{
	
	/**
	 * sportsmanagementViewjlextdbbimport::init()
	 * 
	 * @return
	 */
	public function init ()
    {
		//global $app;
		
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
		//JToolBarHelper::title ( JText::_ ( 'COM_SPORTSMANAGEMENT_ADMIN_LMO_IMPORT_TITLE_1_3' ), 'generic.png' );
		//JToolBarHelper::help ( 'screen.joomleague', true );
		
		$uri = JFactory::getURI ();
		$config = JComponentHelper::getParams ( 'com_media' );
		$post = JRequest::get ( 'post' );
		$files = JRequest::get ( 'files' );
		
		$this->assignRef ( 'request_url', $uri->toString () );
		$this->assignRef ( 'config', $config );
		
		$revisionDate = '2011-04-28 - 12:00';
		$this->assignRef ( 'revisionDate', $revisionDate );
		
		
	}
	
   
    
	/**
	 * sportsmanagementViewjlextdbbimport::_displayDefault()
	 * 
	 * @param mixed $tpl
	 * @return
	 */
	function _displayDefault($tpl) 
    {
		//global $option;
		$option = JRequest::getCmd('option');
		$app = JFactory::getApplication ();
		$db = JFactory::getDBO ();
		$uri = JFactory::getURI ();
		$user = JFactory::getUser ();
		
		// $model = $this->getModel('project') ;
		// $projectdata = $this->get('Data');
		// $this->assignRef( 'name', $projectdata->name);
		
		$model = $this->getModel ();
		$project = $app->getUserState ( $option . 'project' );
		$this->assignRef ( 'project', $project );
		$config = JComponentHelper::getParams ( 'com_media' );
		
		$this->assign ( 'request_url', $uri->toString () );
		$this->assignRef ( 'config', $config );
		$revisionDate = '2011-04-28 - 12:00';
		$this->assignRef ( 'revisionDate', $revisionDate );
		$import_version = 'NEW';
		$this->assignRef ( 'import_version', $import_version );
		
		
	}
    
    
	/**
	 * sportsmanagementViewjlextdbbimport::_displayDefaultUpdate()
	 * 
	 * @param mixed $tpl
	 * @return
	 */
	function _displayDefaultUpdate($tpl) 
    {
		// global $app, $option;
		$app = & JFactory::getApplication ();
		$option = JRequest::getCmd ( 'option' );
		
		$db = JFactory::getDBO ();
		$uri = JFactory::getURI ();
		$user = JFactory::getUser ();
		$model = $this->getModel ();
		//$option = 'com_joomleague';
		$project = $app->getUserState ( $option . 'project' );
		$this->assignRef ( 'project', $project );
		$config = JComponentHelper::getParams ( 'com_media' );
		
		$uploadArray = $app->getUserState ( $option . 'uploadArray', array () );
		$lmoimportuseteams = $app->getUserState ( $option . 'lmoimportuseteams' );
		$whichfile = $app->getUserState ( $option . 'whichfile' );
		//$delimiter = $app->getUserState ( $option . 'delimiter' );
		
		$this->assignRef ( 'uploadArray', $uploadArray );
		
		$this->assignRef ( 'importData', $model->getUpdateData () );
		
		// $this->assignRef('xml',$model->getData());
		
		//parent::display ( $tpl );
	}
    
   
    
   
    
	/**
	 * sportsmanagementViewjlextdbbimport::addToolbar()
	 * 
	 * @return
	 */
	protected function addToolbar() 
    {
        // Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        $option = JRequest::getCmd('option');
		
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        
        // Set toolbar items for the page
		JToolBarHelper::title( JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT' ),'dbb-cpanel' );
        
        parent::addToolbar();

	}
}

?>
