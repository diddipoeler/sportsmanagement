<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage rosteralltime
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Language\Text;

/**
 * sportsmanagementViewRosteralltime
 *
 * @package
 * @author    abcde
 * @copyright 2015
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewRosteralltime extends sportsmanagementView
{

	/**
	 * sportsmanagementViewRosteralltime::init()
	 * 
	 * @return void
	 */
	function init()
	{
		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
//		$this->team   = $this->model->getTeam();
		$this->playerposition     = $this->model->getPlayerPosition();
		$this->positioneventtypes = $this->model->getPositionEventTypes();
		$this->rows = $this->model->getTeamPlayers(1, $this->positioneventtypes, $this->items);

//Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' team<br><pre>' . print_r($this->team,true) . '</pre>'), Log::INFO, 'jsmerror');
//Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' playerposition<br><pre>' . print_r($this->playerposition,true) . '</pre>'), Log::INFO, 'jsmerror');
//Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' positioneventtypes<br><pre>' . print_r($this->positioneventtypes,true) . '</pre>'), Log::INFO, 'jsmerror');
//Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' items<br><pre>' . print_r($this->items,true) . '</pre>'), Log::INFO, 'jsmerror');
//Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' rows<br><pre>' . print_r($this->rows,true) . '</pre>'), Log::INFO, 'jsmerror');

	}

}
