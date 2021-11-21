<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage scoresheet
 * @file       view.html.php
 * @author     ortwin20000, diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

require_once JPATH_COMPONENT_SITE . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'nextmatch.php';

/**
 * sportsmanagementViewScoresheet
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2018
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewScoresheet extends sportsmanagementView
{

	/**
	 * sportsmanagementViewTeamPlan::init()
	 *
	 * @return void
	 */
	function init()
	{
		
		$this->match       = $this->model->getMatch($this->jinput->getInt('mid', 0), $this->jinput->getInt('cfg_which_database', 0));
		$this->teamplayer1 = $this->model->getTeamPlayer($this->match[0]->team1_id, $this->match[0]->season_id, $this->jinput->getInt('cfg_which_database', 0));
		$this->teamplayer2 = $this->model->getTeamPlayer($this->match[0]->team2_id, $this->match[0]->season_id, $this->jinput->getInt('cfg_which_database', 0));
		$myDateTime        = new DateTime($this->match[0]->match_date);

		// Create a new Scoresheet instance
		$fields = array(
			'Bewerb'         => $this->match[0]->projectname,
			'Ort'            => $this->match[0]->playgroundname,
			'Datum'          => $myDateTime->format('d.m.Y'),
			'Zeit'           => $myDateTime->format('H:i'),
			'Spielfeld'      => '',
			'Spielnummer'    => $this->match[0]->match_number,
			'MannschaftA'    => $this->match[0]->team1_name,
			'MannschaftB'    => $this->match[0]->team2_name,
			'Schiedsrichter' => $this->match[0]->referee,
			'Anschreiber'    => '',
			'Linienrichter1' => '',
			'Linienrichter2' => ''
		);

		foreach ($this->teamplayer1 AS $key => $player)
		{
			$position                             = $key + 1;
			$fields['SpielerA' . $position]       = $player->lastname . ' ' . $player->firstname;
			$fields['SpielernummerA' . $position] = $player->knvbnr;
		}

		foreach ($this->teamplayer2 AS $key => $player)
		{
			$position                             = $key + 1;
			$fields['SpielerB' . $position]       = $player->lastname . ' ' . $player->firstname;
			$fields['SpielernummerB' . $position] = $player->knvbnr;
		}

		if ($this->match[0]->game_parts == "3")
		{
			$pdf = new FPDM('media/' . $this->option . DIRECTORY_SEPARATOR . $this->view . DIRECTORY_SEPARATOR . 'Spielbericht_3.pdf');
		}
		else
		{
			$pdf = new FPDM('media/' . $this->option . DIRECTORY_SEPARATOR . $this->view . DIRECTORY_SEPARATOR . 'Spielbericht_5.pdf');
		}

		$pdf->Load($fields, true); // Second parameter: false if field values are in ISO-8859-1, true if UTF-8
		$pdf->Merge();
		ob_end_clean();
		$pdf->Output();
	}
}
