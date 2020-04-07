<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage treetonode
 */

defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * sportsmanagementViewTreetonode
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2017
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewTreetonode extends sportsmanagementView
{

	/**
	 * sportsmanagementViewTreetonode::init()
	 *
	 * @return void
	 */
	function init()
	{
		$config = sportsmanagementModelProject::getTemplateConfig('treetonode');

			  $this->project = sportsmanagementModelProject::getProject();
		$this->overallconfig = sportsmanagementModelProject::getOverallConfig();
		$this->config = $config;
		$this->node = $this->model->getTreetonode();
		$this->roundname = $this->model->getRoundName();

		// Set page title
		// TODO: treeto name, no project name
		$titleInfo = sportsmanagementHelper::createTitleInfo(Text::_('COM_SPORTSMANAGEMENT_TREETO_PAGE_TITLE'));

		if (!empty($this->project))
		{
			$titleInfo->projectName = $this->project->name;
			$titleInfo->leagueName = $this->project->league_name;
			$titleInfo->seasonName = $this->project->season_name;
		}

		$division = sportsmanagementModelProject::getDivision(Factory::getApplication()->input->getInt('division', 0));

		if (!empty($division) && $division->id != 0)
		{
			$titleInfo->divisionName = $division->name;
		}

		if (isset($this->config["page_title_format"]))
		{
			$this->pagetitle = sportsmanagementHelper::formatTitle($titleInfo, $this->config["page_title_format"]);
		}
		else
		{
			$this->pagetitle = '';
		}

		$this->document->setTitle($this->pagetitle);

	}
	// $this->app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . '<pre>'.print_r($this->node,true).'</pre>'  , '');
}
