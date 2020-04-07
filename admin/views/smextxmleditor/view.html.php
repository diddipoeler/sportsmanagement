<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage smextxmleditor
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * sportsmanagementViewsmextxmleditor
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2013
 * @access    public
 */
class sportsmanagementViewsmextxmleditor extends sportsmanagementView
{
	/**
	 * sportsmanagementViewsmextxmleditor::init()
	 *
	 * @return void
	 */
	public function init()
	{
		$this->file_name = $this->jinput->getString('file_name', "");
		$this->form        = $this->get('Form');
		$this->source    = $this->get('Source');
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar()
	{
		$this->jinput->set('hidemainmenu', true);
		parent::addToolbar();
		ToolbarHelper::apply('smextxmleditor.apply');
		ToolbarHelper::save('smextxmleditor.save');
		ToolbarHelper::cancel('smextxmleditor.cancel', 'JTOOLBAR_CANCEL');
		$this->title = $this->file_name;
		$this->icon = 'xml-edit';
	}

}
