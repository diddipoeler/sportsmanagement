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

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * sportsmanagementViewStaff
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewStaff extends JViewLegacy
{

	/**
	 * sportsmanagementViewStaff::display()
	 * 
	 * @param mixed $tpl
	 * @return
	 */
	function display($tpl=null)
	{
		// Get a refrence of the page instance in joomla
		$document = JFactory::getDocument();
        $option = JRequest::getCmd('option');
        $mainframe = JFactory::getApplication();

		$model = $this->getModel();
//        $mdlPerson = JModelLegacy::getInstance("Person", "sportsmanagementModel");
        $model->projectid = JRequest::getInt( 'p', 0 );
		$model->personid = JRequest::getInt( 'pid', 0 );
		$model->teamplayerid = JRequest::getInt( 'pt', 0 );
//        $mdlPerson->projectid = JRequest::getInt( 'p', 0 );
//		$mdlPerson->personid = JRequest::getInt( 'pid', 0 );
//		$mdlPerson->teamplayerid = JRequest::getInt( 'pt', 0 );
        
//      sportsmanagementModelPerson::projectid = JRequest::getInt( 'p', 0 );
//		sportsmanagementModelPerson::personid = JRequest::getInt( 'pid', 0 );
//		sportsmanagementModelPerson::teamplayerid = JRequest::getInt( 'pt', 0 );
        
		$config = sportsmanagementModelProject::getTemplateConfig($this->getName());
		$person = sportsmanagementModelPerson::getPerson();
        
//        $mainframe->enqueueMessage(JText::_('sportsmanagementViewStaff person<br><pre>'.print_r($person,true).'</pre>'),'');
//        $mainframe->enqueueMessage(JText::_('sportsmanagementViewStaff personid<br><pre>'.print_r($model->personid,true).'</pre>'),'');

		$this->assign('project',sportsmanagementModelProject::getProject());
		$this->assign('overallconfig',sportsmanagementModelProject::getOverallConfig());
		$this->assignRef('config',$config);
		$this->assignRef('person',$person);
		$this->assign('showediticon',sportsmanagementModelPerson::getAllowed($config['edit_own_player']));
		
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' showediticon<br><pre>'.print_r($this->showediticon,true).'</pre>'),'Notice');
        
		$staff=&$model->getTeamStaff();
		$titleStr = JText::sprintf('COM_SPORTSMANAGEMENT_STAFF_ABOUT_AS_A_STAFF', sportsmanagementHelper::formatName(null, $this->person->firstname, $this->person->nickname, $this->person->lastname, $this->config["name_format"]));		
		
		$this->assignRef('inprojectinfo',$staff);
        
		$this->assign('history',$model->getStaffHistory('ASC'));

		$this->assign('stats',$model->getStats());
		$this->assign('staffstats',$model->getStaffStats());
		$this->assign('historystats',$model->getHistoryStaffStats());
		$this->assign('title',$titleStr);

		$extended = sportsmanagementHelper::getExtended($person->extended, 'staff');
		$this->assignRef( 'extended', $extended);
		$document->setTitle($titleStr);
        
        $view = JRequest::getVar( "view") ;
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$option.'/assets/css/'.$view.'.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        

		parent::display($tpl);
	}

}
?>