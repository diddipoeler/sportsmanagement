<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jltournamenttree
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementViewjltournamenttree
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewjltournamenttree extends sportsmanagementView
{


	/**
	 * sportsmanagementViewjltournamenttree::init()
	 *
	 * @return void
	 */
	function init()
	{

		if ($this->project->project_type == 'TOURNAMENT_MODE'
			|| $this->project->project_type == 'DIVISIONS_LEAGUE'
		)
		{
			$this->rounds = $this->model->getTournamentRounds();

			$this->color_from = $this->model->getColorFrom();
			$this->color_to = $this->model->getColorTo();
			$this->font_size = $this->model->getFontSize();
			$this->projectname = $this->project->name;
			$this->bracket_rounds = $this->model->getTournamentBracketRounds($this->rounds);
			$this->bracket_teams = $this->model->getTournamentMatches($this->rounds);
			$this->bracket_results = $this->model->getTournamentResults($this->rounds);
			$this->which_first_round = $this->model->getWhichShowFirstRound();
			$this->jl_tree_bracket_round_width = $this->model->getTreeBracketRoundWidth();
			$this->jl_tree_bracket_teamb_width = $this->model->getTreeBracketTeambWidth();
			$this->jl_tree_bracket_width = $this->model->getTreeBracketWidth();

			//            $this->document->addScript(Uri::base() . 'components/' . $this->option . '/assets/js/jquery.json-2.3.min.js');
			//            $this->document->addScript(Uri::base() . 'components/' . $this->option . '/assets/js/jquery.bracket-3.js');
			$this->document->addScript(Uri::base() . 'components/' . $this->option . '/assets/js/jquery.bracket.min.js');

			// Add customstyles
			//            $stylelink = '<link rel="stylesheet" href="' . Uri::base() . 'components/' . $this->option . '/assets/css/jquery.bracket-3.css' . '" type="text/css" />' . "\n";
			//            $this->document->addCustomTag($stylelink);
			//            $stylelink = '<link rel="stylesheet" href="' . Uri::base() . 'components/' . $this->option . '/assets/css/jquery.bracket-site.css' . '" type="text/css" />' . "\n";
			//            $this->document->addCustomTag($stylelink);
			$stylelink = '<link rel="stylesheet" href="' . Uri::base() . 'components/' . $this->option . '/assets/css/jquery.bracket.min.css' . '" type="text/css" />' . "\n";
			$this->document->addCustomTag($stylelink);

			if (ComponentHelper::getParams($this->option)->get('show_debug_info_frontend'))
			{
						Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' config <pre>' . print_r($this->config, true) . '</pre>', '');
			}
		}

	}

}

