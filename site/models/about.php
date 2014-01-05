<?php 

defined('_JEXEC') or die('Restricted access');
/*
@copyright	Copyright (C) 2005-2013 JoomLeague.net. All rights reserved.
@license		GNU/GPL, see LICENSE.php
Joomla! is free software. This version may have been modified pursuant
to the GNU General Public License, and as distributed it includes or
is derivative of works licensed under the GNU General Public License or
other free or open source software licenses.
See COPYRIGHT.php for copyright notices and details.
*/

/*
Model class for the Joomleague component

@author		JoomLeague Team <www.joomleague.net>
@package	JoomLeague
@since		0.1
*/

jimport('joomla.application.component.model');

//require_once( JLG_PATH_SITE . DS . 'models' . DS . 'project.php' );

class sportsmanagementModelAbout extends JModel
{
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