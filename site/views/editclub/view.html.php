<?php
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
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
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');


/**
 * sportsmanagementViewEditClub
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewEditClub extends JViewLegacy
{

	/**
	 * sportsmanagementViewEditClub::display()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	function display($tpl=null)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$uri 	= JFactory::getURI();
		$user 	= JFactory::getUser();
        $document = JFactory::getDocument();
		//$version = urlencode(JoomleagueHelper::getVersion());
		//$css='components/com_joomleague/assets/css/tabs.css?v='.$version;
		//$document->addStyleSheet($css);
        
		$model	= $this->getModel();
        $this->assignRef('club',$model->getClub() );

		$lists = array();

    $this->club->merge_teams = explode(",", $this->club->merge_teams);
    

		$this->assignRef('form',$this->get('Form'));	

/*        
        // extended club data
		$xmlfile = JLG_PATH_ADMIN . DS . 'assets' . DS . 'extended' . DS . 'club.xml';
		$jRegistry = new JRegistry;
		$jRegistry->loadString($this->club->extended, 'ini');
		$extended =& JForm::getInstance('extended', $xmlfile, array('control'=> 'extended'), false, '/config');
		$extended->bind($jRegistry);
		$this->assignRef( 'extended', $extended );
*/        

		$extended = sportsmanagementHelper::getExtended($this->club->extended, 'club');
		$this->assignRef( 'extended', $extended );
        
        $this->assignRef('lists',$lists);


        $this->assign('cfg_which_media_tool', JComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_media_tool',0) );
//        $this->assign('cfg_be_show_merge_teams', JComponentHelper::getParams('com_joomleague')->get('cfg_be_show_merge_teams',0) );

		
		parent::display($tpl);	
	}

	
}
?>
