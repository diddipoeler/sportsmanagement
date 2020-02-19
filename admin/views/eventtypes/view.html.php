<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage eventtypes
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Toolbar\ToolbarHelper;

jimport('joomla.filesystem.file');

/**
 * sportsmanagementViewEventtypes
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewEventtypes extends sportsmanagementView
{

	/**
	 * sportsmanagementViewEventtypes::init()
	 * 
	 * @return void
	 */
	public function init ()
	{
       
        $this->table = Table::getInstance('eventtype', 'sportsmanagementTable');

		//build the html select list for sportstypes
		$sportstypes[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_EVENTS_SPORTSTYPE_FILTER'), 'id', 'name');
		$allSportstypes = BaseDatabaseModel::getInstance('SportsTypes','sportsmanagementmodel')->getSportsTypes();
    		
		$sportstypes = array_merge($sportstypes, $allSportstypes);
		$this->sports_type	= $allSportstypes;
        
		$lists['sportstypes'] = HTMLHelper::_( 'select.genericList',
							$sportstypes,
							'filter_sports_type',
							'class="inputbox" onChange="this.form.submit();" style="width:120px"',
							'id',
							'name',
							$this->state->get('filter.sports_type')	);
		unset($sportstypes);

		$this->lists	= $lists;
       
        
		
	}
	
	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
		// Set toolbar items for the page
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_EVENTS_TITLE');

		ToolbarHelper::publish('eventtypes.publish', 'JTOOLBAR_PUBLISH', true);
		ToolbarHelper::unpublish('eventtypes.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		ToolbarHelper::divider();
		
		ToolbarHelper::addNew('eventtype.add');
		ToolbarHelper::editList('eventtype.edit');
		ToolbarHelper::custom('eventtype.import','upload','upload',Text::_('JTOOLBAR_UPLOAD'),false);
		ToolbarHelper::archiveList('eventtype.export',Text::_('JTOOLBAR_EXPORT'));
				
        parent::addToolbar();
	}
}
?>
