<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    sportsmanagement
 * @subpackage about
 * @file       about.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementModelAbout
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelAbout extends BaseDatabaseModel
{
	/**
	 * sportsmanagementModelAbout::getAbout()
	 *
	 * @return
	 */
	function getAbout()
	{
		$about = new stdClass;

			  // Translations Hosted by
		$about->translations = '<a href="https://www.transifex.com/jsm/sportsmanagement/">https://www.transifex.com/jsm/sportsmanagement/</a>';

		// Repository Hosted by
		$about->repository = '<a href="https://github.com/diddipoeler/sportsmanagement">https://github.com/diddipoeler/sportsmanagement</a>';

		// Version
		$version = sportsmanagementHelper::getVersion();
		$revision = explode('.', $version);
		$about->version = '';

			  // Author
		$about->author = '';

		// Page
		$about->page = 'http://sportsmanagement.fussballineuropa.de/';

		// E-mail
		$about->email = 'diddipoeler@gmx.de';

		// Forum
		$about->forum = 'http://www.fussballineuropa.de/index.php/forum';

			  // Bugtracker
		$about->bugs = 'https://github.com/diddipoeler/sportsmanagement/issues';

			  // Wiki
		$about->wiki = 'http://smwiki.diddipoeler.de/';

			  // Date
		$about->date = '2014-01-01';

		// Developer
		$about->developer = 'DonClumsy (Tim Keller), SvDoldie (Hauke Prochnow), Stony (Siegfried Galun) ';

		// Designer
		$about->designer = 'DonClumsy';
		$about->designer .= ' (Tim Keller), ';

			  // Icons
		$about->icons = '<a href="http://www.hollandsevelden.nl/iconset/" target="_blank">Jersey Icons</a> (Hollandsevelden.nl)';
		$about->icons .= ', <a href="http://www.famfamfam.com/lab/icons/silk/" target="_blank">Silk / Flags Icons</a> (Mark James)';
		$about->icons .= ', Panel images (Kasi)';

		// Flash
		$about->flash = '';

		// Graphoc library
		$about->graphic_library = '';

			  // Phpthumb class
		$about->phpthumb = '';

		  // Page
		  $about->github = 'https://github.com/diddipoeler/sportsmanagement';
		$about->diddipoelerpage = 'http://www.fussballineuropa.de';

		// E-mail
		$about->diddipoeleremail = 'diddipoeler@gmx.de';

		// Forum
		$about->diddipoelerforum = 'http://www.fussballineuropa.de/index.php/forum/sports-management';

		$this->_about = $about;

		return $this->_about;
	}

}
