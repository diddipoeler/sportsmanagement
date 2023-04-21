<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jlextindividualsportes
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementViewjlextindividualsportes
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewjlextindividualsportes extends sportsmanagementView
{

	/**
	 * sportsmanagementViewjlextindividualsportes::init()
	 *
	 * @return
	 */
	public function init()
	{
		$tpl = null;


if ( Factory::getConfig()->get('debug') )
{  
// QUERY_STRING    
//Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' id' . '<pre>'.print_r($this->jinput,true).'</pre>' ), Log::NOTICE, 'jsmerror');

Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' pid' . $this->jinput->getInt('pid', 0)), Log::NOTICE, 'jsmerror');    
Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' id' . $this->jinput->getInt('id', 0)), Log::NOTICE, 'jsmerror');
Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' team1' . $this->jinput->getInt('team1', 0)), Log::NOTICE, 'jsmerror');
Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' team2' . $this->jinput->getInt('team2', 0)), Log::NOTICE, 'jsmerror');
Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' rid' . $this->jinput->getInt('rid', 0)), Log::NOTICE, 'jsmerror');
Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' layout' . $this->getLayout()), Log::NOTICE, 'jsmerror');
Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' generate' . $this->jinput->getInt('generate', 0)), Log::NOTICE, 'jsmerror');
}		


		switch ($this->getLayout())
		{
			case 'default';
			case 'default_3';
			case 'default_4';
			$this->_displayDefault($tpl);
			break;
            case 'generate';
			case 'generate_3';
			case 'generate_4';
			$this->_displayGenerate($tpl);
			break;
		}

	}


/**
 * sportsmanagementViewjlextindividualsportes::_displayGenerate()
 * 
 * @param mixed $tpl
 * @return void
 */
function _displayGenerate($tpl)
	{
	   
       $mdlProject               = BaseDatabaseModel::getInstance('Project', 'sportsmanagementModel');
       $mdlTeamplayers               = BaseDatabaseModel::getInstance('teamplayers', 'sportsmanagementModel');
		$this->project                  = $mdlProject->getProject($this->project_id);
        
	  $this->pid = $this->jinput->getInt('pid', 0); 
      $this->id = $this->jinput->getInt('id', 0);
      $this->projectteam1_id =  $this->jinput->getInt('team1', 0);
       $this->projectteam2_id = $this->jinput->getInt('team2', 0);
       $this->rid = $this->jinput->getInt('rid', 0);
       
       
       $this->homeplayers = $mdlTeamplayers->getProjectTeamplayers(0, $this->project->season_id, $this->projectteam1_id, 1,$this->project_id);
       $this->awayplayers = $mdlTeamplayers->getProjectTeamplayers(0, $this->project->season_id, $this->projectteam2_id, 1,$this->project_id);
       
       $this->setLayout('default_generate');
       }
       
       
	/**
	 * sportsmanagementViewjlextindividualsportes::_displayDefault()
	 *
	 * @param   mixed  $tpl
	 *
	 * @return void
	 */
	function _displayDefault($tpl)
	{
	   $lists['search_mode'] = '';
       
		$this->state         = $this->get('State');
		$this->sortDirection = $this->state->get('list.direction');
		$this->sortColumn    = $this->state->get('list.ordering');

		$cid = $this->jinput->request->get('cid', null, array());

		$project_id      = $this->app->getUserState("$this->option.pid", '0');
		$this->match_id        = $this->jinput->getInt('id', 0);
		$this->rid              = $this->jinput->getInt('rid', 0);
		$this->projectteam1_id = $this->jinput->getInt('team1', 0);
		$this->projectteam2_id = $this->jinput->getInt('team2', 0);
        
        $this->massadd              = $this->jinput->getInt('massadd', 0);

		$mdlProject = BaseDatabaseModel::getInstance("Project", "sportsmanagementModel");
		$this->projectws   = $mdlProject->getProject($project_id);
		$mdlRound   = BaseDatabaseModel::getInstance("Round", "sportsmanagementModel");
		$this->roundws    = $mdlRound->getRound($this->rid );

		$this->model->checkGames($this->projectws , $this->match_id, $this->rid , $this->projectteam1_id, $this->projectteam2_id);

		$this->matches    = $this->get('Items');
		$this->total      = $this->get('Total');
		$this->pagination = $this->get('Pagination');

		$teams[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAM_PLAYER'));

		if ($projectteams = $this->model->getPlayer($this->projectteam1_id, $project_id))
		{
			$teams = array_merge($teams, $projectteams);
		}

		$lists['homeplayer'] = $teams;
		unset($teams);

		$teams[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAM_PLAYER'));

		if ($projectteams = $this->model->getPlayer($this->projectteam2_id, $project_id))
		{
			$teams = array_merge($teams, $projectteams);
		}

		$lists['awayplayer'] = $teams;
		unset($teams);

//		$this->matches    = $matches;
		//$this->pagination = $pagination;

		// $this->request_url    = $uri->toString();

		$this->ProjectTeams = $this->model->getProjectTeams($project_id);

		//$this->match_id = $match_id;
		//$this->rid      = $rid;

//		$this->projectteam1_id = $projectteam1_id;
//		$this->projectteam2_id = $projectteam2_id;

		//$this->projectws = $projectws;
		//$this->roundws   = $roundws;

		if ($result = $this->model->getPlayer($this->projectteam1_id, $project_id))
		{
			$this->getHomePlayer = $this->model->getPlayer($this->projectteam1_id, $project_id);
		}
		else
		{
			$tempplayer          = new stdClass;
			$tempplayer->value   = 0;
			$tempplayer->text    = 'TempPlayer';
			$exportplayer[]      = $tempplayer;
			$this->getHomePlayer = $exportplayer;
		}

		if ($result = $this->model->getPlayer($this->projectteam2_id, $project_id))
		{
			$this->getAwayPlayer = $this->model->getPlayer($this->projectteam2_id, $project_id);
		}
		else
		{
			$tempplayer          = new stdClass;
			$tempplayer->value   = 0;
			$tempplayer->text    = 'TempPlayer';
			$exportplayer[]      = $tempplayer;
			$this->getAwayPlayer = $exportplayer;
		}

		$this->lists = $lists;

		$this->setLayout('default');
	}

}
