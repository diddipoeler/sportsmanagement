<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage rosterpositions
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * sportsmanagementViewrosterpositions
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewrosterpositions extends sportsmanagementView
{
    
	/**
	 * sportsmanagementViewrosterpositions::init()
	 *
	 * @return void
	 */
	public function init()
	{
		$this->table = Table::getInstance('rosterposition', 'sportsmanagementTable');
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar()
	{
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROSTERPOSITIONS_TITLE');
		ToolbarHelper::custom('rosterpositions.addhome', 'new', 'new', Text::_('COM_SPORTSMAMAGEMENT_ADMIN_ROSTERPOSITIONS_HOME'), false);
		ToolbarHelper::custom('rosterpositions.addaway', 'new', 'new', Text::_('COM_SPORTSMAMAGEMENT_ADMIN_ROSTERPOSITIONS_AWAY'), false);
		ToolbarHelper::editList('rosterposition.edit');
		parent::addToolbar();
	}

}
