<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage extrafields
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * sportsmanagementViewextrafields
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewextrafields extends sportsmanagementView
{

	/**
	 * sportsmanagementViewextrafields::init()
	 *
	 * @return void
	 */
	public function init()
	{

					$this->table = Table::getInstance('club', 'sportsmanagementTable');

	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar()
	{
		// Set toolbar items for the page
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_EXTRAFIELDS_TITLE');
		ToolbarHelper::addNew('extrafield.add');
		ToolbarHelper::editList('extrafield.edit');
		ToolbarHelper::custom('extrafield.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
		ToolbarHelper::archiveList('extrafield.export', Text::_('JTOOLBAR_EXPORT'));

		parent::addToolbar();
	}
}
