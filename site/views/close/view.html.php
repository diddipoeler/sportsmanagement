<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage close
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

jimport('joomla.application.component.view');

/**
 * This view is displayed after successfull saving of config data.
 * Use it to show a message informing about success or simply close a modal window.
 *
 * @package    Joomla.Administrator
 * @subpackage com_config
 */
class sportsmanagementViewClose extends JViewLegacy
{
	/**
	 * Display the view
	 */
	function display($tpl = null)
	{
		// Close a modal window
		Factory::getDocument()->addScriptDeclaration(
			'
			window.parent.location.href=window.parent.location.href;
			window.parent.SqueezeBox.close();
		'
		);
	}
}
