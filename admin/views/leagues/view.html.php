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
 * sportsmanagementViewLeagues
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewLeagues extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewLeagues::init()
	 * 
	 * @return void
	 */
	public function init ()
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$uri = JFactory::getURI();
        $model	= $this->getModel();
        $inputappend = '';
        $startmemory = memory_get_usage();
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' bilderpfad<br><pre>'.print_r(COM_SPORTSMANAGEMENT_PICTURE_SERVER,true).'</pre>'),'');
        
        $this->state = $this->get('State'); 
        $this->sortDirection = $this->state->get('list.direction');
        $this->sortColumn = $this->state->get('list.ordering');
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' state<br><pre>'.print_r($this->state,true).'</pre>'),'');


        $starttime = microtime(); 
		$items = $this->get('Items');
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');
        
        $table = JTable::getInstance('league', 'sportsmanagementTable');
		$this->table	= $table;
        
        //build the html options for nation
		$nation[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));
		if ( $res = JSMCountries::getCountryOptions() )
        {
            $nation = array_merge($nation,$res);
            $this->search_nation	= $res;
        }
		
        $lists['nation'] = $nation;
        $lists['nation2'] = JHtmlSelect::genericlist(	$nation,
																'filter_search_nation',
																$inputappend.'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
																'value',
																'text',
																$this->state->get('filter.search_nation'));

		unset($nation);
        $nation[] = JHtml::_('select.option', '0' ,JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_ASSOCIATION'));
        $mdlassociation = JModelLegacy::getInstance('jlextassociations', 'sportsmanagementModel');
        if ( $res = $mdlassociation->getAssociations() )
        {
            $nation = array_merge($nation, $res);
            $this->search_association	= $res;
        }
        
        $lists['association'] = array();
        foreach( $res as $row)
        {
            if (array_key_exists($row->country, $lists['association'] )) 
            {
            $lists['association'][$row->country][] = $row;
            //echo "Das Element 'erstes' ist in dem Array vorhanden";
            }
            else
            {
            $lists['association'][$row->country][] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_ASSOCIATION'));
            $lists['association'][$row->country][] = $row;    
            }
            
            
            
            //$lists['association'] = $nation;
        }
        //$lists['association'] = $nation;
        
        
        $lists['association2'] = JHtmlSelect::genericlist(	$nation,
																'filter_search_association',
																$inputappend.'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
																'value',
																'text',
																$this->state->get('filter.search_association'));
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' association<br><pre>'.print_r($lists['association'],true).'</pre>'),'');
        
        unset($myoptions);
        
        $myoptions[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP'));
        $mdlagegroup = JModelLegacy::getInstance('agegroups', 'sportsmanagementModel');
        if ( $res = $mdlagegroup->getAgeGroups() )
        {
            $myoptions = array_merge($myoptions, $res);
            $this->search_agegroup	= $res;
        }
        $lists['agegroup'] = $myoptions;
        $lists['agegroup2'] = JHtmlSelect::genericlist(	$myoptions,
																'filter_search_agegroup',
																'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
																'value',
																'text',
																$this->state->get('filter.search_agegroup'));
        unset($myoptions);
        
        $this->user	= JFactory::getUser();
		$this->lists	= $lists;
		$this->items	= $items;
		$this->pagination	= $pagination;
		$this->request_url	= $uri->toString();
		
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' speicherbelegung<br><pre>'.print_r(sportsmanagementModeldatabasetool::getMemory($startmemory, memory_get_usage()),true).'</pre>'),'Notice');
        }
        
        
		
	}
	
	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
		$jinput = JFactory::getApplication()->input;
        $option = $jinput->getCmd('option');
       
		//// Get a refrence of the page instance in joomla
//		$document	= JFactory::getDocument();
//        // Set toolbar items for the page
//        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
//        $document->addCustomTag($stylelink);
        
        // Set toolbar items for the page
		$this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_LEAGUES_TITLE');
        JToolBarHelper::apply('leagues.saveshort');
        
		JToolBarHelper::addNew('league.add');
		JToolBarHelper::editList('league.edit');
		JToolBarHelper::custom('league.import', 'upload', 'upload', JText::_('JTOOLBAR_UPLOAD'), false);
		JToolBarHelper::archiveList('league.export', JText::_('JTOOLBAR_EXPORT'));
        JToolbarHelper::checkin('leagues.checkin');
        if ( COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE )
        {
		JToolbarHelper::trash('leagues.trash');
        }
        else
        {
        JToolBarHelper::deleteList('', 'leagues.delete', 'JTOOLBAR_DELETE');    
        }
        
        parent::addToolbar();
	}
}
?>