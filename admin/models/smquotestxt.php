<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       smquotestxt.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;

/**
 * sportsmanagementModelsmquotestxt
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelsmquotestxt extends JSMModelAdmin
{

	function getTXTFiles()
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');
		$path   = JPATH_SITE . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'mod_sportsmanagement_rquotes' . DIRECTORY_SEPARATOR . 'mod_sportsmanagement_rquotes';
		/** Get a list of files in the search path with the given filter. */
		$files = Folder::files($path, '.txt$|.php$');
		return $files;
	}

}
