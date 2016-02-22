<?php
/** Joomla Sports Management ein Programm zur Verwaltung für alle Sportarten
* @version 1.0.26
* @file		administrator/components/sportsmanagement/views/divisions/view.html.php
* @author diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license This file is part of Joomla Sports Management.
*
* Joomla Sports Management is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Joomla Sports Management is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Joomla Sports Management. If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von Joomla Sports Management.
*
* Joomla Sports Management ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* Joomla Sports Management wird in der Hoffnung, dass es nützlich sein wird, aber
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
defined( '_JEXEC' ) or die( 'Restricted access' );



/**
 * sportsmanagementViewClubnames
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class sportsmanagementViewClubnames extends sportsmanagementView
{

	
	
	/**
	 * sportsmanagementViewClubnames::init()
	 * 
	 * @return void
	 */
	public function init ()
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$db	= sportsmanagementHelper::getDBConnection();
		$uri = JFactory::getURI();
        $model = $this->getModel();
        $lists = array();
        
        $this->state = $this->get('State'); 
        $this->sortDirection = $this->state->get('list.direction');
        $this->sortColumn = $this->state->get('list.ordering');
                

        $starttime = microtime(); 
        $this->items = $this->get('Items');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
        //build the html options for nation
		$nation[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));
		if ( $res = JSMCountries::getCountryOptions() )
        {
            $nation = array_merge($nation,$res);
            $this->search_nation	= $res;
        }
		
        $lists['nation'] = $nation;
        
		$total = $this->get('Total');
		$this->pagination = $this->get('Pagination');
        
        $this->table = JTable::getInstance('clubname', 'sportsmanagementTable');
        
//        $table = JTable::getInstance('division', 'sportsmanagementTable');
//		$this->table	= $table;



		$this->user	= JFactory::getUser();
        //$this->projectws	= $project;
		$this->lists	= $lists;

//		$this->pagination	= $pagination;
		$this->request_url	= $uri->toString();
        
        
		
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
       
		//$option = JRequest::getCmd('option');
//        // Get a refrence of the page instance in joomla
//		$document	= JFactory::getDocument();
//        // Set toolbar items for the page
//        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
//        $document->addCustomTag($stylelink);
        
        // Set toolbar items for the page
		$this->title =  JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_CLUBNAMES_TITLE' );
        
        JToolBarHelper::publish('clubnames.publish', 'JTOOLBAR_PUBLISH', true);
		JToolBarHelper::unpublish('clubnames.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        JToolBarHelper::checkin('clubnames.checkin');
        JToolBarHelper::custom('clubnames.import', 'upload', 'upload', JText::_('JTOOLBAR_INSTALL'), false);
        //JTOOLBAR_INSTALL
        
        
		JToolBarHelper::divider();
		
		JToolBarHelper::addNew('clubname.add');
		JToolBarHelper::editList('clubname.edit');
        if ( COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE )
        {
		JToolbarHelper::trash('clubnames.trash');
        }
        else
        {
        JToolBarHelper::deleteList('', 'clubnames.delete', 'JTOOLBAR_DELETE');    
        }
		JToolbarHelper::checkin('clubnames.checkin');
        parent::addToolbar();
	}
}
?>