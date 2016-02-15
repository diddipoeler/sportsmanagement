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
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
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
 * sportsmanagementViewPersons
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewPersons extends sportsmanagementView
{

	/**
	 * sportsmanagementViewPersons::init()
	 * 
	 * @return
	 */
	public function init ()
	{
	   $tpl = '';
       
		if ( $this->getLayout() == 'assignplayers' || $this->getLayout() == 'assignplayers_3' )
		{
			//$this->state = $this->get('State'); 
            $this->_displayAssignPlayers($tpl);
			return;
		}
        
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $user	= JFactory::getUser();
		$model	= $this->getModel();
        
        $this->state = $this->get('State'); 
        $this->sortDirection = $this->state->get('list.direction');
        $this->sortColumn = $this->state->get('list.ordering');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $my_text = 'state <pre>'.print_r($this->state,true).'</pre>';    
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text); 
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->state,true).'</pre>'),'');
        }


$starttime = microtime(); 
		$items = $this->get('Items');
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');
        
        $table = JTable::getInstance('person', 'sportsmanagementTable');
		$this->table	= $table;

		$app->setUserState($option.'task','');



		//build the html select list for positions
		$positionsList[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_POSITION'));
		$positions = JModelLegacy::getInstance('positions', 'sportsmanagementmodel')->getAllPositions();
		if ($positions)
        { 
            $positions = array_merge($positionsList, $positions);
            }
		$lists['positions'] = $positions;
		unset($positionsList);

		//build the html options for nation
		$nation[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));
		if ($res = JSMCountries::getCountryOptions())
        {
            $nation = array_merge($nation,$res);
            $this->assignRef('search_nation',$res);
            }
		
        $lists['nation'] = $nation;
        $lists['nation2'] = JHtmlSelect::genericlist(	$nation, 
																'filter_search_nation', 
																'class="inputbox" style="width:140px; " onchange="this.form.submit();"', 
																'value', 
																'text', 
																$this->state->get('filter.search_nation'));
        /*$lists['nation2']=JHtml::_( 'select.genericList',
										$nation,
										'filter_nation',
										'class="inputbox" onChange="this.form.submit();" style="width:120px"',
										'id',
										'name',
										$filter_nation);
		*/
        unset($nation);
        
        $myoptions = array();
        $myoptions[] = JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP'));
        $mdlagegroup = JModelLegacy::getInstance('agegroups', 'sportsmanagementModel');
        if ( $res = $mdlagegroup->getAgeGroups() )
        {
            $myoptions = array_merge($myoptions,$res);
            $this->search_agegroup	= $res;
        }
        $lists['agegroup'] = $myoptions;
        $lists['agegroup2']= JHtmlSelect::genericlist(	$myoptions, 
																'filter_search_agegroup', 
																'class="inputbox" style="width:140px; " onchange="this.form.submit();"', 
																'value', 
																'text', 
																$this->state->get('filter.search_agegroup'));
        unset($myoptions);
        

		$this->user	= JFactory::getUser();
		$this->config	= JFactory::getConfig();
		$this->lists	= $lists;
		$this->items	= $items;
		$this->pagination =	$pagination;
		$this->request_url	= JFactory::getURI()->toString();
        
        

	}

	/**
	 * sportsmanagementViewPersons::_displayAssignPlayers()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	function _displayAssignPlayers($tpl=null)
	{
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $user	= JFactory::getUser();
		$model = $this->getModel();
        
        $this->state = $this->get('State'); 
        $this->sortDirection = $this->state->get('list.direction');
        $this->sortColumn = $this->state->get('list.ordering');
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->state,true).'</pre>'),'');
        
		//$project_id = $app->getUserState($option.'project');
        $this->project_id	= $app->getUserState( "$option.pid", '0' );
        $this->persontype	= $app->getUserState( "$option.persontype", '0' );
		$mdlProject = JModelLegacy::getInstance('project', 'sportsmanagementModel');
        $project = $mdlProject->getProject($this->project_id);
		$project_name = $project->name;
		$project_team_id = $app->getUserState($option.'project_team_id');
		$team_name = $model->getProjectTeamName($project_team_id);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' persontype<br><pre>'.print_r($this->persontype,true).'</pre>'),'');
        
		//$mdlQuickAdd = JModelLegacy::getInstance('Quickadd','sportsmanagementModel');
        
        $items = $this->get('Items');
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');
        
        $table = JTable::getInstance('person', 'sportsmanagementTable');
		$this->table	= $table;

		//save icon should be replaced by the apply
		JToolBarHelper::apply('person.saveassigned',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_SAVE_SELECTED'));		
		
		// Set toolbar items for the page
		$type = $jinput->getInt('type');
		if ($type == 0)
		{
                    //back icon should be replaced by the abort/close icon
                    JToolBarHelper::back(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_BACK'),'index.php?option=com_sportsmanagement&view=teamplayers');
                    JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_ASSIGN_PLAYERS'),'generic.png');
                    //$items = $model->getNotAssignedPlayers(JString::strtolower($search),$project_team_id);
		}
		elseif ($type == 1)
		{
                    //back icon should be replaced by the abort/close icon
                    JToolBarHelper::back(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_BACK'),'index.php?option=com_sportsmanagement&view=teamstaffs');
                    JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_ASSIGN_STAFF'),'generic.png');
                    //$items = $model->getNotAssignedStaff(JString::strtolower($search),$project_team_id);
		}
		elseif ($type == 2)
		{
                    //back icon should be replaced by the abort/close icon
                    JToolBarHelper::back(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_BACK'),'index.php?option=com_sportsmanagement&view=projectreferees');
                    JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_ASSIGN_REFEREES'),'generic.png');
                    //$items = $model->getNotAssignedReferees(JString::strtolower($search),$this->project_id);
		}
        
        //build the html options for nation
		$nation[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));
		if ($res = JSMCountries::getCountryOptions()){$nation = array_merge($nation, $res);}
        $lists['nation'] = $nation;
        
        //build the html select list for positions
		$positionsList[] = JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_POSITION'));
		$positions = JModelLegacy::getInstance('positions', 'sportsmanagementmodel')->getAllPositions();
		if ($positions)
		{
			$positions = array_merge($positionsList, $positions);
		}
		$lists['positions'] = $positions;
		unset($positionsList);
        
        $myoptions = array();
        $myoptions[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP'));
        $mdlagegroup = JModelLegacy::getInstance("agegroups", "sportsmanagementModel");
        if ( $res = $mdlagegroup->getAgeGroups() )
        {
            $myoptions = array_merge($myoptions,$res);
            $this->search_agegroup	= $res;
        }
        $lists['agegroup'] = $myoptions;
        $lists['agegroup2']= JHtmlSelect::genericlist(	$myoptions, 
																'filter_search_agegroup', 
																'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
																'value', 
																'text', 
																$this->state->get('filter.search_agegroup'));
        unset($myoptions);

		//JToolBarHelper::onlinehelp();		
		
		//$limit = $app->getUserStateFromRequest('global.list.limit','limit',$app->getCfg('list_limit'),'int');

		//jimport('joomla.html.pagination');
		//$pagination = new JPagination($mdlQuickAdd->_total,JRequest::getVar('limitstart',0,'','int'),$limit);
		//$mdlQuickAdd->_pagination=$pagination;



		$this->prjid	= $this->project_id;
		$this->prj_name	= $project_name;
		//$this->team_id	= $team_id;
		$this->team_name	= $team_name;
		$this->project_team_id	= $project_team_id;
		$this->lists	= $lists;
		$this->items	= $items;
        $this->user	= $user;
		$this->pagination	= $pagination;
		$this->request_url	= JFactory::getURI()->toString();
		$this->type	= $type;
        
        $this->setLayout('assignplayers');

		//parent::display($tpl);
	}

	/**
	 * Displays a calendar control field with optional onupdate js handler
	 *
	 * @param	string	The date value
	 * @param	string	The name of the text field
	 * @param	string	The id of the text field
	 * @param	string	The date format
	 * @param	string	js function to call on date update
	 * @param	array	Additional html attributes
	 */
	function calendar($value,$name,$id,$format='%Y-%m-%d',$attribs=null,$onUpdate=null,$i=null)
	{
		JHtml::_('behavior.calendar'); //load the calendar behavior

		if (is_array($attribs)){$attribs=JArrayHelper::toString($attribs);}
		$document = JFactory::getDocument();
		$document->addScriptDeclaration('window.addEvent(\'domready\',function() {Calendar.setup({
	        inputField     :    "'.$id.'",    // id of the input field
	        ifFormat       :    "'.$format.'",     // format of the input field
	        button         :    "'.$id.'_img", // trigger for the calendar (button ID)
	        align          :    "Tl",          // alignment (defaults to "Bl")
	        onUpdate       :    '.($onUpdate ? $onUpdate : 'null').',
	        singleClick    :    true
    	});});');
		$html = '';
		$html .= '<input onchange="document.getElementById(\'cb'.$i.'\').checked=true" type="text" name="'.$name.'" id="'.$id.'" value="'.htmlspecialchars($value,ENT_COMPAT,'UTF-8').'" '.$attribs.' />'.
				 '<img class="calendar" src="'.JURI::root(true).'/templates/system/images/calendar.png" alt="calendar" id="'.$id.'_img" />';
		return $html;
	}
	
	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
  		//// Get a refrence of the page instance in joomla
//		$document	= JFactory::getDocument();
//        $option = JRequest::getCmd('option');
//        // Set toolbar items for the page
//        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
//        $document->addCustomTag($stylelink);
//        
//		//$user		= JFactory::getUser();
//        // Set toolbar items for the page

		$this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_TITLE');

// 		JToolBarHelper::publishList('person.publish');
// 		JToolBarHelper::unpublishList('person.unpublish');
		JToolBarHelper::publish('persons.publish', 'JTOOLBAR_PUBLISH', true);
		JToolBarHelper::unpublish('persons.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        JToolBarHelper::checkin('persons.checkin');
		JToolBarHelper::divider();
		
		JToolBarHelper::apply('persons.saveshort');
		JToolBarHelper::editList('person.edit');
		JToolBarHelper::addNew('person.add');
		JToolBarHelper::custom('person.import', 'upload', 'upload', JText::_('JTOOLBAR_UPLOAD'), false);
		JToolBarHelper::archiveList('person.export', JText::_('JTOOLBAR_EXPORT'));
        JToolbarHelper::checkin('persons.checkin');
		if ( COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE )
            {
		    JToolbarHelper::trash('persons.trash');
            }
            else
            {
            JToolBarHelper::deleteList('', 'persons.delete', 'JTOOLBAR_DELETE');    
            }
		
        parent::addToolbar();
	}
}
?>