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


/**
 * sportsmanagementViewProjectteam
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewProjectteam extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewProjectteam::init()
	 * 
	 * @return
	 */
	public function init ()
	{
		// get the Data
		$form = $this->get('Form');
		$item = $this->get('Item');
		$script = $this->get('Script');
 
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
        
        // Assign the Data
		$this->form = $form;
		$this->item = $item;
		$this->script = $script;
        
        $project_id	= $this->item->project_id;;
        $mdlProject = JModelLegacy::getInstance("Project", "sportsmanagementModel");
	    $project = $mdlProject->getProject($project_id);
        $this->assignRef('project',$project);
        $team_id	= $this->item->team_id;
        $mdlTeam = JModelLegacy::getInstance("Team", "sportsmanagementModel");
	    $project_team = $mdlTeam->getTeam(0,$team_id);
        $trainingdata = $mdlTeam->getTrainigData(0,$this->item->id);
        
        $daysOfWeek=array(	0 => JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'),
			1 => JText::_('MONDAY'),
			2 => JText::_('TUESDAY'),
			3 => JText::_('WEDNESDAY'),
			4 => JText::_('THURSDAY'),
			5 => JText::_('FRIDAY'),
			6 => JText::_('SATURDAY'),
			7 => JText::_('SUNDAY'));
        $dwOptions=array();
			foreach($daysOfWeek AS $key => $value)
            {
                $dwOptions[]=JHtml::_('select.option',$key,$value);
            }
			foreach ($trainingdata AS $td)
			{
				$lists['dayOfWeek'][$td->id]=JHtml::_('select.genericlist',$dwOptions,'dayofweek['.$td->id.']','class="inputbox"','value','text',$td->dayofweek);
			}    
            
        $extended = sportsmanagementHelper::getExtended($item->extended, 'projectteam');
		$this->assignRef( 'extended', $extended );
        //$this->assign('cfg_which_media_tool', JComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_media_tool',0) );
        
        $this->assignRef('project',$project);
        $this->assignRef('lists',	$lists);
        $this->assignRef('project_team',$project_team);
        $this->assignRef('trainingData',$trainingdata);
		
 
	
	}
    

	/**
	 * sportsmanagementViewProjectteam::addToolbar()
	 * 
	 * @return void
	 */
	protected function addToolbar()
	{
			
        JRequest::setVar('hidemainmenu', true);
        JRequest::setVar('pid', $this->item->project_id);
		
        $isNew = $this->item->id ? $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAM_EDIT') : $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAM_NEW');
        $this->icon = 'projectteam';
        
                
        parent::addToolbar();

	}
    
    
    
}
?>
