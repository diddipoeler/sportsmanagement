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
	   $this->homeplayers = array();
	   $this->awayplayers = array();
       
       $this->homeplayers_position = array();
	   $this->awayplayers_position = array();
       
       $count_homeplayers = array();
       $count_awayplayers = array();
       
       $this->generate_matches = 0;
       
       $this->show_matches = array();
       
       
       
       $mdlProject = BaseDatabaseModel::getInstance('Project', 'sportsmanagementModel');
       $mdlTeamplayers = BaseDatabaseModel::getInstance('teamplayers', 'sportsmanagementModel');
		$this->project = $mdlProject->getProject($this->project_id);
        
	  $this->pid = $this->jinput->getInt('pid', 0); 
      $this->id = $this->jinput->getInt('id', 0);
      $this->projectteam1_id =  $this->jinput->getInt('team1', 0);
       $this->projectteam2_id = $this->jinput->getInt('team2', 0);
       $this->rid = $this->jinput->getInt('rid', 0);
       
       
       $this->homeplayers = $mdlTeamplayers->getProjectTeamplayers(0, $this->project->season_id, $this->projectteam1_id, 1,$this->project_id);
       $this->awayplayers = $mdlTeamplayers->getProjectTeamplayers(0, $this->project->season_id, $this->projectteam2_id, 1,$this->project_id);
       
       $count_homeplayers = $mdlTeamplayers->getTeamplayersMatch(0, $this->project->season_id, $this->projectteam1_id, $this->project_id,$this->id);
       $count_awayplayers = $mdlTeamplayers->getTeamplayersMatch(0, $this->project->season_id, $this->projectteam2_id, $this->project_id,$this->id);

if ( Factory::getConfig()->get('debug') )
{ 
echo 'homeplayers<pre>'.print_r($count_homeplayers,true).'</pre>';
echo 'awayplayers<pre>'.print_r($count_awayplayers,true).'</pre>';
}

       
       if ( sizeof($count_homeplayers) != sizeof($count_awayplayers) )
       {
       Log::add(Text::_('Die Anzahl der Heimspieler stimmt nicht mit der Anzahl der Gastspieler überein!' ), Log::ERROR, 'jsmerror'); 
       $this->generate_matches = 0; 
       }
       else
       {
       $this->generate_matches = sizeof($count_homeplayers);
       
       foreach ( $count_homeplayers as $count_i => $item )
	{
    $this->homeplayers_position[$item->project_position_name] = $item->teamplayer_id;
    }
    
     foreach ( $count_awayplayers as $count_i => $item )
	{
    $this->awayplayers_position[$item->project_position_name] = $item->teamplayer_id;
    }      
       
       }
/**       
When we are in the screen to enter the individual match is it possible to :
- automaticly generate all individual match for the game ? Exemple for Espoirs :
  A or C against W (depending if the team is composed of 2 or 3 players)
B or C against X (depending if the team is composed of 2 or 3 players)
Double against Double
A against X or Y (depending if the team is composed of 2 or 3 players)
B against W or Y (depending if the team is composed of 2 or 3 players)
 
For Classement par equipes : 
C against Y
B against X
A against Y or Z (depending if the team is composed of 3 or 4 players)
C or D against W (depending if the team is composed of 3 or 4 players)
A against X
B against W
C against X or Z (depending if the team is composed of 3 or 4 players)
B or D against Y (depending if the team is composed of 3 or 4 players)
A against W
Double against Double       
       
*/       
       switch ( $this->generate_matches )
       {
       case 0:
       /** fehler nicht sgenerieren */
       break; 
       case 5:
       /** 4 spieler 1 doppel */
       $temp = new stdClass;
       $temp->teamplayer1_id = $this->homeplayers_position['C'];
       $temp->teamplayer2_id = $this->awayplayers_position['Y'];
       $temp->teamplayer1_position = 'C';
       $temp->teamplayer2_position = 'Y';

       $this->show_matches[] = $temp;
        $temp = new stdClass;
       $temp->teamplayer1_id = $this->homeplayers_position['B'];
       $temp->teamplayer2_id = $this->awayplayers_position['X'];
       $temp->teamplayer1_position = 'B';
       $temp->teamplayer2_position = 'X';
       $this->show_matches[] = $temp;
        $temp = new stdClass;
       $temp->teamplayer1_id = $this->homeplayers_position['A'];
       $temp->teamplayer2_id = $this->awayplayers_position['Z'];
       $temp->teamplayer1_position = 'A';
       $temp->teamplayer2_position = 'Z';
       $this->show_matches[] = $temp;
        $temp = new stdClass;
       $temp->teamplayer1_id = $this->homeplayers_position['D'];
       $temp->teamplayer2_id = $this->awayplayers_position['W'];
       $temp->teamplayer1_position = 'D';
       $temp->teamplayer2_position = 'W';
       $this->show_matches[] = $temp;
        $temp = new stdClass;
       $temp->teamplayer1_id = $this->homeplayers_position['A'];
       $temp->teamplayer2_id = $this->awayplayers_position['X'];
       $temp->teamplayer1_position = 'A';
       $temp->teamplayer2_position = 'X';
       $this->show_matches[] = $temp;
        $temp = new stdClass;
       $temp->teamplayer1_id = $this->homeplayers_position['B'];
       $temp->teamplayer2_id = $this->awayplayers_position['W'];
       $temp->teamplayer1_position = 'B';
       $temp->teamplayer2_position = 'W';
       $this->show_matches[] = $temp;
        $temp = new stdClass;
       $temp->teamplayer1_id = $this->homeplayers_position['C'];
       $temp->teamplayer2_id = $this->awayplayers_position['Z'];
       $temp->teamplayer1_position = 'C';
       $temp->teamplayer2_position = 'Z';
       $this->show_matches[] = $temp;
        $temp = new stdClass;
       $temp->teamplayer1_id = $this->homeplayers_position['D'];
       $temp->teamplayer2_id = $this->awayplayers_position['Y'];
       $temp->teamplayer1_position = 'D';
       $temp->teamplayer2_position = 'Y';
       $this->show_matches[] = $temp;
        $temp = new stdClass;
       $temp->teamplayer1_id = $this->homeplayers_position['A'];
       $temp->teamplayer2_id = $this->awayplayers_position['W'];
       $temp->teamplayer1_position = 'A';
       $temp->teamplayer2_position = 'W';
       $this->show_matches[] = $temp;
        $temp = new stdClass;
       $temp->teamplayer1_id = $this->homeplayers_position['Double'];
       $temp->teamplayer2_id = $this->awayplayers_position['Double'];
       $temp->teamplayer1_position = 'Double';
       $temp->teamplayer2_position = 'Double';
       $this->show_matches[] = $temp;
       break;
       case 4:
       /** 3 spieler 1 doppel */
       $temp = new stdClass;
       $temp->teamplayer1_id = $this->homeplayers_position['C'];
       $temp->teamplayer2_id = $this->awayplayers_position['Y'];
       $temp->teamplayer1_position = 'C';
       $temp->teamplayer2_position = 'Y';
       $this->show_matches[] = $temp;
        $temp = new stdClass;
       $temp->teamplayer1_id = $this->homeplayers_position['B'];
       $temp->teamplayer2_id = $this->awayplayers_position['X'];
       $temp->teamplayer1_position = 'B';
       $temp->teamplayer2_position = 'X';
       $this->show_matches[] = $temp;
        $temp = new stdClass;
       $temp->teamplayer1_id = $this->homeplayers_position['A'];
       $temp->teamplayer2_id = $this->awayplayers_position['Y'];
       $temp->teamplayer1_position = 'A';
       $temp->teamplayer2_position = 'Y';
       $this->show_matches[] = $temp;
        $temp = new stdClass;
       $temp->teamplayer1_id = $this->homeplayers_position['C'];
       $temp->teamplayer2_id = $this->awayplayers_position['W'];
       $temp->teamplayer1_position = 'C';
       $temp->teamplayer2_position = 'W';
       $this->show_matches[] = $temp;
        $temp = new stdClass;
       $temp->teamplayer1_id = $this->homeplayers_position['A'];
       $temp->teamplayer2_id = $this->awayplayers_position['X'];
       $temp->teamplayer1_position = 'A';
       $temp->teamplayer2_position = 'X';
       $this->show_matches[] = $temp;
        $temp = new stdClass;
       $temp->teamplayer1_id = $this->homeplayers_position['B'];
       $temp->teamplayer2_id = $this->awayplayers_position['W'];
       $temp->teamplayer1_position = 'B';
       $temp->teamplayer2_position = 'W';
       $this->show_matches[] = $temp;
        $temp = new stdClass;
       $temp->teamplayer1_id = $this->homeplayers_position['C'];
       $temp->teamplayer2_id = $this->awayplayers_position['X'];
       $temp->teamplayer1_position = 'C';
       $temp->teamplayer2_position = 'X';
       $this->show_matches[] = $temp;
        $temp = new stdClass;
       $temp->teamplayer1_id = $this->homeplayers_position['B'];
       $temp->teamplayer2_id = $this->awayplayers_position['Y'];
       $temp->teamplayer1_position = 'B';
       $temp->teamplayer2_position = 'Y';
       $this->show_matches[] = $temp;
        $temp = new stdClass;
       $temp->teamplayer1_id = $this->homeplayers_position['A'];
       $temp->teamplayer2_id = $this->awayplayers_position['W'];
       $temp->teamplayer1_position = 'A';
       $temp->teamplayer2_position = 'W';
       $this->show_matches[] = $temp;
        $temp = new stdClass;
       $temp->teamplayer1_id = $this->homeplayers_position['Double'];
       $temp->teamplayer2_id = $this->awayplayers_position['Double'];
       $temp->teamplayer1_position = 'Double';
       $temp->teamplayer2_position = 'Double';
       $this->show_matches[] = $temp;
       
       break;
       case 3:
       /** 2 spieler 1 doppel */
       $temp = new stdClass;
       $temp->teamplayer1_id = $this->homeplayers_position['A'];
       $temp->teamplayer2_id = $this->awayplayers_position['W'];
       $temp->teamplayer1_position = 'A';
       $temp->teamplayer2_position = 'W';
       $this->show_matches[] = $temp;
       $temp = new stdClass;
       $temp->teamplayer1_id = $this->homeplayers_position['B'];
       $temp->teamplayer2_id = $this->awayplayers_position['X'];
       $temp->teamplayer1_position = 'B';
       $temp->teamplayer2_position = 'X';
       $this->show_matches[] = $temp;
       $temp = new stdClass;
       $temp->teamplayer1_id = $this->homeplayers_position['Double'];
       $temp->teamplayer2_id = $this->awayplayers_position['Double'];
       $temp->teamplayer1_position = 'Double';
       $temp->teamplayer2_position = 'Double';
       $this->show_matches[] = $temp;
       $temp = new stdClass;
       $temp->teamplayer1_id = $this->homeplayers_position['A'];
       $temp->teamplayer2_id = $this->awayplayers_position['X'];
       $temp->teamplayer1_position = 'A';
       $temp->teamplayer2_position = 'X';
       $this->show_matches[] = $temp;
       $temp = new stdClass;
       $temp->teamplayer1_id = $this->homeplayers_position['B'];
       $temp->teamplayer2_id = $this->awayplayers_position['Y'];
       $temp->teamplayer1_position = 'B';
       $temp->teamplayer2_position = 'Y';
       $this->show_matches[] = $temp;
       
       break;
       }
       
       
       
       
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
