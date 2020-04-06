<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage jlextindividualsportes
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

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
        switch ($this->getLayout()) {
        case 'default';
        case 'default_3';
        case 'default_4';
            $this->_displayDefault($tpl);
            break;
        }

    }

    /**
     * sportsmanagementViewjlextindividualsportes::_displayDefault()
     *
     * @param  mixed $tpl
     * @return void
     */
    function _displayDefault($tpl)
    {
        $this->state = $this->get('State');
        $this->sortDirection = $this->state->get('list.direction');
        $this->sortColumn = $this->state->get('list.ordering');
      
        $cid = $this->jinput->request->get('cid', null, array());
      
        $project_id    = $this->app->getUserState("$this->option.pid", '0');
        $match_id = $this->jinput->getInt('id', 0);
        $rid = $this->jinput->getInt('rid', 0);
        $projectteam1_id = $this->jinput->getInt('team1', 0);
        $projectteam2_id = $this->jinput->getInt('team2', 0);
      
        $mdlProject = BaseDatabaseModel::getInstance("Project", "sportsmanagementModel");
        $projectws = $mdlProject->getProject($project_id);
        $mdlRound = BaseDatabaseModel::getInstance("Round", "sportsmanagementModel");
        $roundws = $mdlRound->getRound($rid);
      
      
        $this->model->checkGames($projectws, $match_id, $rid, $projectteam1_id, $projectteam2_id);
      
      
        $matches = $this->get('Items');
        $total = $this->get('Total');
        $pagination = $this->get('Pagination');
      
        $teams[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAM_PLAYER'));
        if ($projectteams = $this->model->getPlayer($projectteam1_id, $project_id)) {
            $teams = array_merge($teams, $projectteams);
        }
         $lists['homeplayer'] = $teams;
         unset($teams);
          
          
          
         $teams[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAM_PLAYER'));
        if ($projectteams = $this->model->getPlayer($projectteam2_id, $project_id)) {
            $teams = array_merge($teams, $projectteams);
        }

         $lists['awayplayer'] = $teams;
         unset($teams);  
      
      
        $this->matches    = $matches;
        $this->pagination    = $pagination;
        //$this->request_url	= $uri->toString();
      
        $this->ProjectTeams    = $this->model->getProjectTeams($project_id);
      
        $this->match_id    = $match_id;
        $this->rid    = $rid;
      
        $this->projectteam1_id    = $projectteam1_id;
        $this->projectteam2_id    = $projectteam2_id;
      
        $this->projectws    = $projectws;
        $this->roundws    = $roundws;
      
        if ($result = $this->model->getPlayer($projectteam1_id, $project_id) ) {
            $this->getHomePlayer = $this->model->getPlayer($projectteam1_id, $project_id);  
        }
        else
        {
            $tempplayer = new stdClass();
            $tempplayer->value = 0;
            $tempplayer->text = 'TempPlayer';
            $exportplayer[] = $tempplayer;
            $this->getHomePlayer = $exportplayer;
        }
      
        if ($result = $this->model->getPlayer($projectteam2_id, $project_id) ) {
            $this->getAwayPlayer = $this->model->getPlayer($projectteam2_id, $project_id);  
        }
        else
        {
            $tempplayer = new stdClass();
            $tempplayer->value = 0;
            $tempplayer->text = 'TempPlayer';
            $exportplayer[] = $tempplayer;
            $this->getAwayPlayer = $exportplayer;
        }

        $this->lists = $lists;

        $this->setLayout('default');
    }

}
?>
