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
 * @subpackage jlxmlimport
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementViewJLXMLImport
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewJLXMLImport extends sportsmanagementView
{
	/**
	 * sportsmanagementViewJLXMLImport::init()
	 *
	 * @return void
	 */
	public function init()
	{

		$config = ComponentHelper::getParams('com_media');
		$this->config    = $config;

	}
}
