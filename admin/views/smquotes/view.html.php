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
 * sportsmanagementViewsmquotes
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewsmquotes extends JViewLegacy
{
	/**
	 * sportsmanagementViewsmquotes::display()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	function display($tpl=null)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$uri = JFactory::getURI();
        $model	= $this->getModel();
        $starttime = microtime(); 
        
        $this->state = $this->get('State'); 
        $this->sortDirection = $this->state->get('list.direction');
        $this->sortColumn = $this->state->get('list.ordering');



		$items = $this->get('Items');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');
        
        
        //$this->state		= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.filter_state','filter_state','','word');;
//        $mainframe->enqueueMessage(JText::_('sportsmanagementViewsmquotes state<br><pre>'.print_r($this->state,true).'</pre>'),'Notice');
//        $mainframe->enqueueMessage(JText::_('sportsmanagementViewsmquotes filter_state<br><pre>'.print_r($this->filter_state,true).'</pre>'),'Notice');
//        $mainframe->enqueueMessage(JText::_('sportsmanagementViewsmquotes filter_catid<br><pre>'.print_r($this->filter_catid,true).'</pre>'),'Notice');
        
        
  


		$this->assign('user',JFactory::getUser());
		$this->assignRef('lists',$lists);
		$this->assignRef('items',$items);
		$this->assignRef('pagination',$pagination);
		$this->assign('request_url',$uri->toString());
		$this->addToolbar();
		parent::display($tpl);
	}
	
	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
		// Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        
        // Set toolbar items for the page
		JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_QUOTES_TITLE'),'quotes');
		JToolBarHelper::addNew('smquote.add');
		JToolBarHelper::editList('smquote.edit');
		JToolBarHelper::custom('smquote.import','upload','upload',JText::_('JTOOLBAR_UPLOAD'),false);
        
        JToolBarHelper::custom('smquotes.edittxt','featured.png','featured_f2.png',JText::_('JTOOLBAR_EDIT'),false);
        
        $bar = JToolBar::getInstance('toolbar');
        $bar->appendButton('Link', 'info', 'Kategorie', 'index.php?option=com_categories&view=categories&extension=com_sportsmanagement');
        
		JToolBarHelper::archiveList('smquote.export',JText::_('JTOOLBAR_EXPORT'));
		JToolBarHelper::deleteList('', 'smquotes.delete', 'JTOOLBAR_DELETE');
		JToolBarHelper::divider();
		sportsmanagementHelper::ToolbarButtonOnlineHelp();
        JToolBarHelper::preferences(JRequest::getCmd('option'));
	}
}
?>
