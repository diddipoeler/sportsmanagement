<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage season
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

/**
 * sportsmanagementViewSeason
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewSeason extends sportsmanagementView
{

	/**
	 * sportsmanagementViewSeason::init()
	 *
	 * @return
	 */
	public function init()
	{

	}


	/**
	 * sportsmanagementViewSeason::addToolBar()
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		$this->jinput->set('hidemainmenu', true);
		$isNew      = $this->item->id ? $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_SEASON_EDIT') : $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_SEASON_ADD_NEW');
		$this->icon = 'season';
		parent::addToolbar();
	}


}
