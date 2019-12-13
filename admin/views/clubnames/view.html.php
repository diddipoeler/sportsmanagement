<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage clubnames
 */


defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

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
        $lists = array();
        
        //build the html options for nation
		$nation[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));
		if ( $res = JSMCountries::getCountryOptions() )
        {
            $nation = array_merge($nation,$res);
            $this->search_nation	= $res;
        }
		
        $lists['nation'] = $nation;
        $this->table = Table::getInstance('clubname', 'sportsmanagementTable');
		$this->lists = $lists;
		
	}
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.7
	 */
	protected function addToolbar()
	{
        // Set toolbar items for the page
		$this->title =  Text::_( 'COM_SPORTSMANAGEMENT_ADMIN_CLUBNAMES_TITLE' );
        
        ToolbarHelper::publish('clubnames.publish', 'JTOOLBAR_PUBLISH', true);
		ToolbarHelper::unpublish('clubnames.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::checkin('clubnames.checkin');
        ToolbarHelper::custom('clubnames.import', 'upload', 'upload', Text::_('JTOOLBAR_INSTALL'), false);
		ToolbarHelper::divider();
		ToolbarHelper::addNew('clubname.add');
		ToolbarHelper::editList('clubname.edit');

        parent::addToolbar();
	}
}
?>