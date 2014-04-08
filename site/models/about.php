<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version         1.0.05
 * @file                agegroup.php
 * @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license                This file is part of SportsManagement.
 *
 * SportsManagement is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * SportsManagement is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Diese Datei ist Teil von SportsManagement.
 *
 * SportsManagement ist Freie Software: Sie können es unter den Bedingungen
 * der GNU General Public License, wie von der Free Software Foundation,
 * Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
 * veröffentlichten Version, weiterverbreiten und/oder modifizieren.
 *
 * SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
 * OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
 * Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
 * Siehe die GNU General Public License für weitere Details.
 *
 * Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
 * Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
 *
 * Note : All ini files need to be saved as UTF-8 without BOM
 */

defined('_JEXEC') or die('Restricted access');


jimport('joomla.application.component.model');

//require_once( JLG_PATH_SITE . DS . 'models' . DS . 'project.php' );

/**
 * sportsmanagementModelAbout
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementModelAbout extends JModel
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
		$about->repository = '<a href="http://gitorious.org/joomleague">http://gitorious.org/joomleague</a>';
		//version
		$version = sportsmanagementHelper::getVersion();
		$revision = explode('.', $version);
		$about->version = '<a href="http://gitorious.org/joomleague/joomleague/commits/'.$revision[0].'.'.$revision[1].'.0/">' . $version . '</a>';
		
		//author
		$about->author = '<a href="http://stats.joomleague.net/authors.html">Joomleague-Team</a>';

		//page
		$about->page = 'http://www.joomleague.net';

		//e-mail
		$about->email = 'http://www.JoomLeague.net/forum/index.php?action=contact';

		//forum
		$about->forum = 'http://forum.joomleague.net';
		
		//bugtracker
		$about->bugs = 'http://bugtracker.joomleague.net';
		
		//wiki
		$about->wiki = 'http://wiki.joomleague.net';
		
		//date
		$about->date = '2014-01-01';

		//developer
		//$about->developer = '<a href="http://stats.joomleague.net/authors.html" target="_blank">JoomLeague-Team</a>';
        $about->developer = 'DonClumsy (Tim Keller), SvDoldie (Hauke Prochnow), Stony (Siegfried Gallun) ';
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