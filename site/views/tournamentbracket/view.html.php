<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage tournamentbracket
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;


class sportsmanagementViewtournamentbracket extends sportsmanagementView
{

	
	function init()
	{

		//$division = sportsmanagementModelProject::getDivision(sportsmanagementModelClubs::$divisionid, sportsmanagementModelClubs::$cfg_which_database);
		$tournamentbracket    = $this->model->gettournamentbracket();
  }


}
