<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage positions
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementViewPositions
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewPositions extends sportsmanagementView
{
	/**
	 * sportsmanagementViewPositions::init()
	 * 
	 * @return void
	 */
	public function init ()
	{
			

$starttime = microtime(); 
		//$items = $this->get('Items');
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
		
        
		$this->table = JTable::getInstance('position', 'sportsmanagementTable');



		//build the html options for parent position
		$parent_id[] = JHtml::_('select.option', '', JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_IS_P_POSITION'));
		if ($res = $this->model->getParentsPositions())
		{
			foreach ($res as $re){$re->text = JText::_($re->text);}
			$parent_id = array_merge($parent_id, $res);
		}
		$lists['parent_id'] = $parent_id;
		unset($parent_id);

		//build the html select list for sportstypes
		$sportstypes[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_SPORTSTYPE_FILTER'), 'id', 'name');
		$allSportstypes = JModelLegacy::getInstance('SportsTypes','sportsmanagementmodel')->getSportsTypes();
		$sportstypes = array_merge($sportstypes, $allSportstypes);
        
        $this->sports_type	= $allSportstypes;
        
		$lists['sportstypes'] = JHtml::_( 'select.genericList', 
							$sportstypes, 
							'filter_sports_type', 
							'class="inputbox" onChange="this.form.submit();" style="width:120px"', 
							'id', 
							'name', 
							$this->state->get('filter.sports_type'));
		unset($sportstypes);
		
		$this->lists = $lists;
		
	}
	
	
	/**
	 * sportsmanagementViewPositions::addToolbar()
	 * 
	 * @return void
	 */
	protected function addToolbar()
	{

		// Set toolbar items for the page
		$this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_TITLE');

		JToolbarHelper::publish('positions.publish', 'JTOOLBAR_PUBLISH', true);
		JToolbarHelper::unpublish('positions.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		JToolbarHelper::divider();

		JToolbarHelper::apply('positions.saveshort');
		JToolbarHelper::editList('position.edit');
		JToolbarHelper::addNew('position.add');
		JToolbarHelper::custom('position.import', 'upload', 'upload', JText::_('JTOOLBAR_UPLOAD'), false);
		JToolbarHelper::archiveList('position.export', JText::_('JTOOLBAR_EXPORT'));
        		
        parent::addToolbar();
	}
}
?>
