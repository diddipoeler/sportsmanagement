<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jlxmlexports
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
 * sportsmanagementViewJLXMLExports
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2016
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewJLXMLExports extends sportsmanagementView
{

	/**
	 * sportsmanagementViewJLXMLExports::init()
	 *
	 * @return void
	 */
	function init()
	{
		$this->exportSystem = Factory::getConfig()->get('sitename');

	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar()
	{
		ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_EXPORT_TITLE'), 'generic.png');
		ToolbarHelper::custom('jlxmlexports.export', 'upload', 'upload', Text::_('JTOOLBAR_EXPORT'), false);
		ToolbarHelper::divider();
		ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=projects');
		parent::addToolbar();
	}

}
