<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage matrix
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * sportsmanagementViewMatrix
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewMatrix extends sportsmanagementView
{

	/**
	 * sportsmanagementViewMatrix::init()
	 *
	 * @return void
	 */
	function init()
	{

		$this->divisionid = sportsmanagementModelMatrix::$divisionid;
		$this->roundid    = sportsmanagementModelMatrix::$roundid;
		$this->division   = $this->model->getDivision();
		$this->round      = $this->model->getRound();

		if (isset($this->config['teamnames']) && $this->config['teamnames'])
		{
			$this->teams = sportsmanagementModelProject::getTeamsIndexedByPtid(sportsmanagementModelMatrix::$divisionid, $this->config['teamnames'], $this->jinput->getInt('cfg_which_database', 0));
		}
		else
		{
			$this->config['teamnames'] = 'name';
			$this->teams               = sportsmanagementModelProject::getTeamsIndexedByPtid(sportsmanagementModelMatrix::$divisionid, 'name', $this->jinput->getInt('cfg_which_database', 0));
		}

		if (!isset($this->config['image_placeholder']))
		{
			$this->config['image_placeholder'] = '';
		}

		$this->results = $this->model->getMatrixResults($this->project->id);

		if (isset($this->config['show_matrix_russia']))
		{
			if (($this->config['show_matrix_russia']) == 1)
			{
				$this->russiamatrix = $this->model->getRussiaMatrixResults($this->teams, $this->results);
			}
		}

		if ($this->project->project_type == 'DIVISIONS_LEAGUE' && !$this->divisionid)
		{
			$ranking_reason  = array();
			$divisions       = sportsmanagementModelProject::getDivisions(0, $this->jinput->getInt('cfg_which_database', 0));
			$this->divisions = $divisions;

			foreach ($this->results as $result)
			{
				foreach ($this->teams as $teams)
				{
					if ($result->division_id)
					{
						if (($result->projectteam1_id == $teams->projectteamid) || ($result->projectteam2_id == $teams->projectteamid))
						{
							$teams->division_id = $result->division_id;

							if ($teams->start_points)
							{
								if ($teams->start_points < 0)
								{
									$color = "red";
								}
								else
								{
									$color = "green";
								}

								$ranking_reason[$result->division_id][$teams->name] = '<font color="' . $color . '">' . $teams->name . ': ' . $teams->start_points . ' Punkte Grund: ' . $teams->reason . '</font>';
							}
						}
					}
				}
			}

			foreach ($this->divisions as $row)
			{
				if (isset($ranking_reason[$row->id]))
				{
					$row->notes = implode(", ", $ranking_reason[$row->id]);
				}
			}
		}

		if (!is_null($this->project))
		{
			$this->favteams = sportsmanagementModelProject::getFavTeams($this->jinput->getInt('cfg_which_database', 0));
		}

		// Set page title
		$pageTitle = Text::_('COM_SPORTSMANAGEMENT_MATRIX_PAGE_TITLE');

		if (isset($this->project->name))
		{
			$pageTitle .= ': ' . $this->project->name;
		}

		$this->document->setTitle($pageTitle);

		// $view = $jinput->getVar( "view") ;
		$stylelink = '<link rel="stylesheet" href="' . Uri::root() . 'components/' . $this->option . '/assets/css/' . $this->view . '.css' . '" type="text/css" />' . "\n";
		$this->document->addCustomTag($stylelink);

	}
}
