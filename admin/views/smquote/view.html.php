<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage smquote
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * sportsmanagementViewsmquote
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewsmquote extends sportsmanagementView
{


	/**
	 * sportsmanagementViewsmquote::init()
	 *
	 * @return
	 */
	public function init()
	{

		$this->item->name = $this->item->author;

	}


	/**
	 * sportsmanagementViewsmquote::addToolBar()
	 *
	 * @return void
	 */
	protected function addToolBar()
	{

		$jinput = Factory::getApplication()->input;
		$jinput->set('hidemainmenu', true);

		$isNew      = $this->item->id ? $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_SMQUOTE_EDIT') : $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_SMQUOTE_ADD_NEW');
		$this->icon = 'quote';

		parent::addToolbar();

	}

}
