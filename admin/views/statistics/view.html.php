<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage statistics
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');


jimport('joomla.filesystem.file');


/**
 * sportsmanagementViewStatistics
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewStatistics extends sportsmanagementView
{

	/**
	 * sportsmanagementViewStatistics::init()
	 * 
	 * @return void
	 */
	public function init ()
	{
		//$app = JFactory::getApplication();
		//$jinput = $app->input;
		//$option = $jinput->getCmd('option');
		//$document = JFactory::getDocument();
		//$user = JFactory::getUser();
		//$uri = JFactory::getURI();
		//$model	= $this->getModel();
        
		//$this->state = $this->get('State');
		//$this->sortDirection = $this->state->get('list.direction');
		//$this->sortColumn = $this->state->get('list.ordering');
		

	$starttime = microtime();
		//$items = $this->get('Items');
		
		if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
		{
		$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
		}
		//$total = $this->get('Total');
		//$pagination = $this->get('Pagination');


		$table = JTable::getInstance('statistic', 'sportsmanagementTable');
		$this->table = $table;
        
		//build the html select list for sportstypes
		$sportstypes[]=JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_ADMIN_EVENTS_SPORTSTYPE_FILTER'), 'id', 'name');
		//$allSportstypes =& JoomleagueModelSportsTypes::getSportsTypes();
		$allSportstypes = JModelLegacy::getInstance('SportsTypes', 'sportsmanagementmodel')->getSportsTypes();		
		
		$sportstypes = array_merge($sportstypes, $allSportstypes);
		$lists['sportstypes']=JHtml::_( 'select.genericList', 
										$sportstypes, 
										'filter_sports_type', 
										'class="inputbox" onChange="this.form.submit();" style="width:120px"', 
										'id', 
										'name', 
										$this->state->get('filter.sports_type'));
		unset($sportstypes);

		//$this->user = $user;
		$this->config = JFactory::getConfig();
		$this->lists = $lists;
		//$this->items = $items;
		//$this->pagination = $pagination;
		//$this->request_url = $uri->toString();
        
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
//        // Set toolbar items for the page
//        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
//        $document->addCustomTag($stylelink);
        
        // Set toolbar items for the page
		$this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_STATISTICS_TITLE');
		
		JToolbarHelper::publishList();
		JToolbarHelper::unpublishList();
		JToolbarHelper::divider();
		JToolbarHelper::editList('statistic.edit');
		JToolbarHelper::addNew('statistic.add');
		JToolbarHelper::custom('statistic.import','upload','upload',JText::_('JTOOLBAR_UPLOAD'),false);
		JToolbarHelper::archiveList('statistic.export',JText::_('JTOOLBAR_EXPORT'));
		
        parent::addToolbar();
	}
}
?>
