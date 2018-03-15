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
		$about->translations = '<a href="https://opentranslators.transifex.com/projects/p/joomleague/">https://opentranslators.transifex.com/projects/p/joomleague/</a>';
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
		$about->email = 'http://www.JoomLeague.net/forum/index.php?action=contact';

		//forum
		$about->forum = 'http://fussballineuropa.de/index.php?option=com_kunena&view=home&defaultmenu=1169&Itemid=1167&lang=de';
		
		//bugtracker
		$about->bugs = 'http://mantisbugtracker.fussballineuropa.de/my_view_page.php';
		
		//wiki
		$about->wiki = 'http://smwiki.diddipoeler.de/';
		
		//date
		$about->date = '2014-01-01';

		//developer
		//$about->developer = '<a href="http://stats.joomleague.net/authors.html" target="_blank">JoomLeague-Team</a>';
        $about->developer = 'DonClumsy (Tim Keller), SvDoldie (Hauke Prochnow), Stony (Siegfried Galun) ';
/*
		//designer
		$about->designer = 'Kasi';
		$about->designer .= ', <a href="http://www.cg-design.net" target="_blank">cg design</a>&nbsp;(Carsten Grob) ';
*/
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
    $about->github = 'https://github.com/diddipoeler/joomleague-2-komplettpaket';
		$about->diddipoelerpage = 'http://www.fussballineuropa.de';

		//e-mail
		$about->diddipoeleremail = 'diddipoeler@gmx.de';

		//forum
		$about->diddipoelerforum = 'http://www.fussballineuropa.de/index.php?option=com_kunena&view=category&catid=247&Itemid=530';

		$this->_about = $about;

		return $this->_about;
	}

}
?>