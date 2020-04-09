<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage predictiongroups
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * sportsmanagementViewpredictiongroups
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewpredictiongroups extends sportsmanagementView
{

	/**
	 * sportsmanagementViewpredictiongroups::init()
	 *
	 * @return void
	 */
	public function init()
	{

		$this->table = Table::getInstance('predictiongroup', 'sportsmanagementTable');

		if (!$this->items)
		{
			$this->app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_NO_GROUPS'), 'Error');
		}

	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar()
	{
		// Set toolbar items for the page
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICTIONGROUPS_TITLE');
		$this->icon  = 'predgroups';

		ToolbarHelper::addNew('predictiongroup.add');
		ToolbarHelper::editList('predictiongroup.edit');
		ToolbarHelper::custom('predictiongroup.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
		ToolbarHelper::archiveList('predictiongroup.export', Text::_('JTOOLBAR_EXPORT'));
		ToolbarHelper::deleteList('', 'predictiongroups.delete', 'JTOOLBAR_DELETE');
		ToolbarHelper::checkin('predictiongroups.checkin');
		parent::addToolbar();

	}
}
