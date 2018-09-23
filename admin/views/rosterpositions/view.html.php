<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage rosterpositions
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;

/**
 * sportsmanagementViewrosterpositions
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewrosterpositions extends sportsmanagementView
{
	/**
	 * sportsmanagementViewrosterpositions::init()
	 * 
	 * @return void
	 */
	public function init ()
	{
        
        $this->table = Table::getInstance('rosterposition', 'sportsmanagementTable');
		
	}
    
    	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
	
// Set toolbar items for the page
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROSTERPOSITIONS_TITLE');
        JToolbarHelper::custom('rosterpositions.addhome', 'new', 'new', Text::_('COM_SPORTSMAMAGEMENT_ADMIN_ROSTERPOSITIONS_HOME'), false);
		JToolbarHelper::custom('rosterpositions.addaway', 'new', 'new', Text::_('COM_SPORTSMAMAGEMENT_ADMIN_ROSTERPOSITIONS_AWAY'), false);
		JToolbarHelper::editList('rosterposition.edit');
		JToolbarHelper::trash('rosterpositions.trash');
		JToolbarHelper::deleteList('', 'rosterpositions.delete', 'JTOOLBAR_DELETE');
		JToolbarHelper::checkin('rosterpositions.checkin');
		parent::addToolbar();  
       
       
       
    }   

}
?>
