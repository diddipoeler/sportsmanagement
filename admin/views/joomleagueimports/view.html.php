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
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
* 
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * sportsmanagementViewjoomleagueimports
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewjoomleagueimports extends sportsmanagementView
{
	/**
	 * sportsmanagementViewjoomleagueimports::display()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	public function init ()
	{
		// Reference global application object
		//$app = JFactory::getApplication();
		// JInput object
		//$jinput = $app->input;
		//$option = $jinput->getCmd('option');
		//$model = $this->getModel();
		//$uri = JFactory::getURI();
		
		$this->cfg_jl_import = JComponentHelper::getParams($this->option)->get( 'cfg_jl_import',1 );
        $this->jl_table_import_step = $this->jinput->get('jl_table_import_step',0);
        
        //$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getLayout -> '.$this->getLayout().''),'');
        
        //$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' jl_table_import_step <br><pre>'.print_r($this->jl_table_import_step,true).'</pre>'),'');
        
        if ( !$this->jl_table_import_step )
        {
        $this->model->check_database();
        }
        //$this->app->enqueueMessage($this->request_url,'Notice');  
        
		if ( $this->cfg_jl_import )
		{
		$this->app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_JL_IMPORT_ALLOWED_YES'),'Notice');    
		}
		else
		{
		$this->app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_JL_IMPORT_ALLOWED_NO'),'Error');    
		}
		
		//$stateVar = $app->getUserStateFromRequest( "$option.success", 'success', '' );
		
		$this->model->check_database();
        
        //build the html select list for sportstypes
		$sportstypes[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SPORTSTYPE_FILTER'),'id','name');
		$mdlSportsTypes = JModelLegacy::getInstance('SportsTypes', 'sportsmanagementModel');
		$allSportstypes = $mdlSportsTypes->getSportsTypes();
		$sportstypes = array_merge($sportstypes, $allSportstypes);
		
		$variable = $this->jinput->get('filter_sports_type',0);

		$lists['sportstype'] = $sportstypes; 
		$lists['sportstypes'] = JHtml::_( 'select.genericList', 
										$sportstypes, 
										'filter_sports_type', 
										'class="inputbox" onChange="" style="width:120px"',
										'id', 
										'name', 
										$variable);
		unset($sportstypes);
		
//        $databasetool = JModelLegacy::getInstance("databasetool", "sportsmanagementModel");
//        $this->assign('jl_tables',$databasetool->getJoomleagueImportTables() );
//        
//        $checktables = $databasetool->checkImportTablesJlJsm($this->jl_tables);
//        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($checktables,true).'</pre>'),'');
//        
//        //build the html select list for seasons
//		$seasons[]=JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SEASON_FILTER'),'id','name');
//        $mdlSeasons = JModelLegacy::getInstance('Seasons','sportsmanagementModel');
//		$allSeasons = $mdlSeasons->getSeasons();
//		$seasons = array_merge($seasons,$allSeasons);
//        
//		$lists['seasons'] = JHtml::_( 'select.genericList',
//									$seasons,
//									'filter_season',
//									'class="inputbox" onChange="" style="width:120px"',
//									'id',
//									'name',
//									$this->state->get('filter.season'));
//
//		unset($seasons);
//        
		$this->lists = $lists;
		//$this->request_url = $uri->toString();
//        $this->assign('items',$checktables);
        
        //$this->assign('success',$jinput->getVar('success'));
		$this->success = $this->app->getUserStateFromRequest( $this->option.".jl_table_import_success", 0 );
        
        
        
        if ( $this->getLayout() == 'infofield' || $this->getLayout() == 'infofield_3' )
		{
		$myoptions[] = JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP'));
		$mdlagegroup = JModelLegacy::getInstance('agegroups', 'sportsmanagementModel');
        if ( $res = $mdlagegroup->getAgeGroups() )
		{
			$myoptions = array_merge($myoptions,$res);
			$this->assignRef('search_agegroup',$res);
		}
		$lists['agegroup'] = $myoptions;  
        
        $this->get_info_fields = $this->model->get_info_fields();
        
        $this->lists = $lists;  
		$this->setLayout('infofield');
        } 
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' request_url <br><pre>'.print_r($this->request_url,true).'</pre>'),'');
        
        //$this->addToolbar();
		//parent::display($tpl);
	}
    
    /**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
//		// Get a refrence of the page instance in joomla
//		$document	= JFactory::getDocument();
//        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
//        $document->addCustomTag($stylelink);
        
        // Set toolbar items for the page
		$this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_JOOMLEAGUE_IMPORT');
		$this->icon = 'joomleague-import';
        
		if ( $this->cfg_jl_import )
		{
		//JToolbarHelper::custom('joomleagueimports.importjoomleaguenew', 'edit', 'edit', JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_POS_ASSIGNMENT'), false);
		}
        if ( $this->getLayout() == 'default' || $this->getLayout() == 'default_3' )
		{
		JToolbarHelper::custom('joomleagueimports.importjoomleaguenew', 'edit', 'edit', JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_START_BUTTON'), false);
        }
         if ( $this->getLayout() == 'infofield' || $this->getLayout() == 'infofield_3' )
		{
		JToolbarHelper::custom('joomleagueimports.joomleaguesetagegroup', 'edit', 'edit', JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_SETAGEGROUP_START_BUTTON'), false);
        }
JToolbarHelper::back('JPREV','index.php?option=com_sportsmanagement&view=projects');    

//        JToolbarHelper::custom('joomleagueimports.positions','edit','edit',JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_POS_ASSIGNMENT'),false);
//        
//        JToolbarHelper::custom('joomleagueimports.checkimport','upload','upload',JText::_('JTOOLBAR_UPLOAD'),false);
//        JToolbarHelper::custom('joomleagueimports.import','new','new',JText::_('JTOOLBAR_DUPLICATE'),false);
		JToolbarHelper::divider();
//		sportsmanagementHelper::ToolbarButtonOnlineHelp();
//        JToolbarHelper::preferences(JFactory::getApplication()->input->getCmd('option'));
		parent::addToolbar();
	}



}
?>
