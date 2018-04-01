<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      about.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage abaout
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');

/**
 * sportsmanagementModelAbout
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementModelAbout extends JModelLegacy
{
	/**
	 * sportsmanagementModelAbout::getAbout()
	 * 
	 * @return
	 */
	function getAbout()
	{
		$about = new stdClass();
		
		//Translations Hosted by
		$about->translations = '<a href="https://www.transifex.com/opentranslators/sportsmanagement/">https://www.transifex.com/opentranslators/sportsmanagement/</a>';
		//Repository Hosted by
		$about->repository = '<a href="https://github.com/diddipoeler/sportsmanagement">https://github.com/diddipoeler/sportsmanagement</a>';
		//version
		$version = sportsmanagementHelper::getVersion();
		$revision = explode('.', $version);
		$about->version = '<a href="http://gitorious.org/joomleague/joomleague/commits/'.$revision[0].'.'.$revision[1].'.0/">' . $version . '</a>';
		
		//author
		$about->author = '<a href="http://stats.joomleague.net/authors.html">Joomleague-Team</a>';

		//page
		$about->page = 'http://sportsmanagement.fussballineuropa.de/';

		//e-mail
		$about->email = 'diddipoeler@gmx.de';

		//forum
		$about->forum = 'http://www.fussballineuropa.de/index.php/forum';
		
		//bugtracker
		$about->bugs = 'https://github.com/diddipoeler/sportsmanagement/issues';
		
		//wiki
		$about->wiki = 'http://smwiki.diddipoeler.de/';
		
		//date
		$about->date = '2014-01-01';

		//developer
        $about->developer = 'DonClumsy (Tim Keller), SvDoldie (Hauke Prochnow), Stony (Siegfried Galun) ';

		//designer
		$about->designer = 'DonClumsy';
		$about->designer .= ' (Tim Keller), ';
        
        
		//icons
		$about->icons = '<a href="http://www.hollandsevelden.nl/iconset/" target="_blank">Jersey Icons</a> (Hollandsevelden.nl)';
		$about->icons .= ', <a href="http://www.famfamfam.com/lab/icons/silk/" target="_blank">Silk / Flags Icons</a> (Mark James)';
		$about->icons .= ', Panel images (Kasi)';

		//flash
		$about->flash = '<a href="http://teethgrinder.co.uk/open-flash-chart-2/" target="_blank">Open Flash Chart 2.x</a>';

		//graphoc library
		$about->graphic_library = '<a href="http://www.walterzorn.com" target="_blank">www.walterzorn.com</a>';
		
		//phpthumb class
		$about->phpthumb = '<a href="http://phpthumb.gxdlabs.com/" target="_blank">phpthumb.gxdlabs.com</a>';


    //page
    $about->github = 'https://github.com/diddipoeler/sportsmanagement';
		$about->diddipoelerpage = 'http://www.fussballineuropa.de';

		//e-mail
		$about->diddipoeleremail = 'diddipoeler@gmx.de';

		//forum
		$about->diddipoelerforum = 'http://www.fussballineuropa.de/index.php/forum/sports-management';

		$this->_about = $about;

		return $this->_about;
	}

}
?>