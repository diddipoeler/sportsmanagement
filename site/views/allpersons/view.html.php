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

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * sportsmanagementViewallpersons
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewallpersons extends JView
{
    protected $state = null;
	protected $item = null;
	protected $items = null;
	protected $pagination = null;
    
	/**
	 * sportsmanagementViewallpersons::display()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	function display($tpl=null)
	{
		// Get a refrence of the page instance in joomla
		$document = JFactory::getDocument();
        $option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        $inputappend = '';
        $app = JFactory::getApplication();
		$user		= JFactory::getUser();
        $starttime = microtime(); 
        $model	= $this->getModel();

		
        $state 		= $this->get('State');
        $items 		= $this->get('Items');
        
		
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		$pagination	= $this->get('Pagination');
        $this->assignRef('columns',$model->columns);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' pagination<br><pre>'.print_r($pagination,true).'</pre>'),'');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' columns<br><pre>'.print_r($columns,true).'</pre>'),'');
		
        //build the html options for nation
		$nation[] = JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));
		if ($res = JSMCountries::getCountryOptions()){$nation=array_merge($nation,$res);}
		
        $lists['nation'] = $nation;
        $lists['nation2'] = JHtmlSelect::genericlist(	$nation,
																'filter_search_nation',
																$inputappend.'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
																'value',
																'text',
																$state->get('filter.search_nation'));
                                                                
        // Set page title
		$document->setTitle(JText::_('COM_SPORTSMANAGEMENT_ALLPERSONS_PAGE_TITLE'));
        
        $form = new stdClass();
        $form->limitField = $pagination->getLimitBox();
        
        $this->filter = $state->get('filter.search');
               
      
		$this->assignRef('form', $form);
		$this->assignRef('items', $items);
		$this->assignRef('state', $state);
		$this->assignRef('user', $user);
		$this->assignRef('pagination', $pagination);
        
        $this->sortDirection    = $this->state->get('filter_order_Dir');
        $this->sortColumn       = $this->state->get('filter_order');
        
        $this->assignRef('lists', $lists);

		parent::display($tpl);
	}

}
?>