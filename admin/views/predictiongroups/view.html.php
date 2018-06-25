<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage predictiongroups
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementViewpredictiongroups
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewpredictiongroups extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewpredictiongroups::init()
	 * 
	 * @return void
	 */
	public function init ()
	{
		//$app = JFactory::getApplication();
		//$jinput = $app->input;
		//$option = $jinput->getCmd('option');
		//$model	= $this->getModel();
		//$uri = JFactory::getURI();
        
		//$this->state = $this->get('State'); 
		//$this->sortDirection = $this->state->get('list.direction');
		//$this->sortColumn = $this->state->get('list.ordering');



		//$items = $this->get('Items');
		//$total = $this->get('Total');
		//$pagination = $this->get('Pagination');
        
		$table = JTable::getInstance('predictiongroup', 'sportsmanagementTable');
		$this->table = $table;
        
        if ( !$this->items )
        {
        $this->app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_NO_GROUPS'),'Error');    
        }

		//$this->user	= JFactory::getUser();
		//$this->lists	= $lists;
		//$this->items	= $items;
		//$this->pagination	= $pagination;
		//$this->request_url	= $uri->toString();
        
       		
	}
	
	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{ 
		//// Get a refrence of the page instance in joomla
//		$document	= JFactory::getDocument();
//        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
//        $document->addCustomTag($stylelink);
//        

        // Set toolbar items for the page
		$this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICTIONGROUPS_TITLE');
		$this->icon = 'predgroups';

		JToolbarHelper::addNew('predictiongroup.add');
		JToolbarHelper::editList('predictiongroup.edit');
		JToolbarHelper::custom('predictiongroup.import','upload','upload',JText::_('JTOOLBAR_UPLOAD'),false);
		JToolbarHelper::archiveList('predictiongroup.export',JText::_('JTOOLBAR_EXPORT'));
		JToolbarHelper::deleteList('','predictiongroups.delete', 'JTOOLBAR_DELETE');
		JToolbarHelper::checkin('predictiongroups.checkin');
	parent::addToolbar();
	
	}
}
?>
