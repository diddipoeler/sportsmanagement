<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage smextxmleditors
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * sportsmanagementViewsmextxmleditors
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2013
 * @access    public
 */
class sportsmanagementViewsmextxmleditors extends sportsmanagementView
{
	/**
	 * sportsmanagementViewsmextxmleditors::init()
	 *
	 * @return void
	 */
	public function init()
	{
		$this->files = $this->model->getXMLFiles();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar()
	{
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_EDITORS');
		$this->icon  = 'xml-edits';
		ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=cpanel');
		parent::addToolbar();
	}

}
