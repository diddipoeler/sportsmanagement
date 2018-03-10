<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');



/**
 * sportsmanagementViewjlextindividualsportes
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewjlextindividualsportes extends sportsmanagementView
{
	public function init ()
	{
		$app = JFactory::getApplication();

		if ($this->getLayout() == 'default')
		{
			$this->_displayDefault($tpl);
			return;
		}

		parent::display($tpl);
	}

	function _displayDefault($tpl)
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
        $model = $this->getModel();
		$uri = JFactory::getURI();

		$this->state = $this->get('State'); 
        $this->sortDirection = $this->state->get('list.direction');
        $this->sortColumn = $this->state->get('list.ordering');
        
		//$cid = JFactory::getApplication()->input->getVar('cid', 'request', 'array');
		$cid = $jinput->request->get('cid', null, ARRAY)

		
        $project_id	= $app->getUserState( "$option.pid", '0' );
		$match_id	= $input->getInt('id', 0);
        $rid		= $input->getInt('rid', 0);
		$projectteam1_id		= $input->getInt('team1', 0);
		$projectteam2_id		= J$input->getInt('team2', 0);
        
        $mdlProject = JModelLegacy::getInstance("Project", "sportsmanagementModel");
	    $projectws = $mdlProject->getProject($project_id);
        $mdlRound = JModelLegacy::getInstance("Round", "sportsmanagementModel");
		$roundws = $mdlRound->getRound($rid);
        
        //$app->enqueueMessage(__FILE__.' '.get_class($this).' '.__FUNCTION__.' projectws<br><pre>'.print_r($projectws, true).'</pre><br>','');
        
        $model->checkGames($projectws, $match_id, $rid, $projectteam1_id, $projectteam2_id);
        
        //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' ' .  ' match_id<br><pre>'.print_r($match_id,true).'</pre>'),'');
        
        $matches = $this->get('Items');
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');
        
        
        
        $teams[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAM_PLAYER'));
        if ($projectteams = $model->getPlayer($projectteam1_id, $project_id))
		{
			$teams = array_merge($teams, $projectteams);
		}
			$lists['homeplayer'] = $teams;
			unset($teams);
            
            
            
         $teams[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAM_PLAYER'));
         if ($projectteams = $model->getPlayer($projectteam2_id, $project_id))
		{
			$teams = array_merge($teams, $projectteams);
		}

			$lists['awayplayer'] = $teams;
			unset($teams);    
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' ' .  ' lists<br><pre>'.print_r($lists,true).'</pre>'),'');
        }
        
        $this->matches	= $matches;
        $this->pagination	= $pagination;
        $this->request_url	= $uri->toString();
        
        $this->ProjectTeams	= $model->getProjectTeams($project_id);
        
        $this->match_id	= $match_id;
        $this->rid	= $rid;
        
        $this->projectteam1_id	= $projectteam1_id;
        $this->projectteam2_id	= $projectteam2_id;
        
        $this->projectws	= $projectws;
        $this->roundws	= $roundws;
        
        if ( $result = $model->getPlayer($projectteam1_id, $project_id) )
        {
        $this->getHomePlayer	= $model->getPlayer($projectteam1_id, $project_id));    
        }
        else
        {
            $tempplayer = new stdClass();
            $tempplayer->value = 0;
            $tempplayer->text = 'TempPlayer';
            $exportplayer[] = $tempplayer;
            $this->getHomePlayer	= $exportplayer;
        }
        
        if ( $result = $model->getPlayer($projectteam2_id, $project_id) )
        {
        $this->getAwayPlayer	= $model->getPlayer($projectteam2_id, $project_id);    
        }
        else
        {
            $tempplayer = new stdClass();
            $tempplayer->value = 0;
            $tempplayer->text = 'TempPlayer';
            $exportplayer[] = $tempplayer;
            $this->getAwayPlayer	= $exportplayer;
        }
        

        $this->lists	= $lists;



		parent::display($tpl);
	}

}
?>