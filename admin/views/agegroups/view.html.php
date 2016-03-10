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
 * sportsmanagementViewagegroups
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewagegroups extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewagegroups::init()
	 * 
	 * @return void
	 */
	public function init ()
	{
		
        $starttime = microtime(); 
        $mdlSportsType = JModelLegacy::getInstance('SportsType', 'sportsmanagementModel');
              
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' state<br><pre>'.print_r($this->state,true).'</pre>'),'');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }

        $this->table = JTable::getInstance('agegroup', 'sportsmanagementTable');
		
        //build the html select list for sportstypes
		$sportstypes[] = JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SPORTSTYPE_FILTER'),'id','name');
		$mdlSportsTypes = JModelLegacy::getInstance('SportsTypes', 'sportsmanagementModel');
		$allSportstypes = $mdlSportsTypes->getSportsTypes();
		$sportstypes = array_merge($sportstypes, $allSportstypes);
        $this->sports_type = $allSportstypes;
		
		$lists['sportstypes'] = JHtml::_( 'select.genericList',
										$sportstypes,
										'filter_sports_type',
										'class="inputbox" onChange="this.form.submit();" style="width:120px"',
										'id',
										'name',
										$this->state->get('filter.sports_type'));
		unset($sportstypes);
        
        //build the html options for nation
		$nation[] = JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));
		if ($res = JSMCountries::getCountryOptions())
		{
			$nation = array_merge($nation,$res);
			$this->search_nation = $res;
		}
		
        $lists['nation']	= $nation;
        $lists['nation2']	= JHtmlSelect::genericlist(	$nation,
																'filter_search_nation',
																'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
																'value',
																'text',
																$this->state->get('filter.search_nation'));

		//$app->enqueueMessage(JText::_('items<br><pre>'.print_r($this->items,true).'</pre>'),'');
        
        foreach ( $this->items as $item )
        {
            $sportstype = $mdlSportsType->getSportstype($item->sportstype_id);
            if ( $sportstype )
            {
            $item->sportstype = $sportstype->name;
            }
            else
            {
            $item->sportstype = NULL;    
            }
        }
        
        if ( count($this->items)  == 0 )
        {
            $databasetool = JModelLegacy::getInstance("databasetool", "sportsmanagementModel");
            $insert_agegroup = $databasetool->insertAgegroup($this->state->get('filter.search_nation'),$this->state->get('filter.sports_type'));
        $app->enqueueMessage(JText::_('Zu diesem Land/Sportart gibt es keine Altersgruppen'),'Error');
        }
        



		$this->lists = $lists;

        
       
		
	}
	
	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
	//	// Get a refrence of the page instance in joomla
//		$document	= JFactory::getDocument();
//        // Set toolbar items for the page
//        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
//        $document->addCustomTag($stylelink);
//        
//        // Set toolbar items for the page
$this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_AGEGROUPS_TITLE');
		JToolBarHelper::addNew('agegroup.add');
		JToolBarHelper::editList('agegroup.edit');
		JToolBarHelper::custom('agegroup.import','upload','upload',JText::_('JTOOLBAR_UPLOAD'),false);
		JToolBarHelper::archiveList('agegroup.export',JText::_('JTOOLBAR_EXPORT'));
        JToolbarHelper::checkin('agegroups.checkin');
		JToolBarHelper::deleteList('', 'agegroups.delete', 'JTOOLBAR_DELETE');
		
        parent::addToolbar();
	}
}
?>