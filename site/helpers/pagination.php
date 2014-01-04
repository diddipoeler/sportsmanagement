<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

//require_once (JLG_PATH_ADMIN .DS.'models'.DS.'round.php');
//require_once (JLG_PATH_ADMIN .DS.'models'.DS.'rounds.php');

class sportsmanagementPagination
{

	/**
	 * create and return the round page navigation
	 *
	 * @param object $project
	 * @return string
	 */
	function pagenav($project)
	{
	   $option = JRequest::getCmd('option');
		$pageNav = '';
		$spacer2 = '&nbsp;&nbsp;';
		$spacer4 = '&nbsp;&nbsp;&nbsp;&nbsp;';
		$roundid = JRequest::getInt( "r", $project->current_round);
		$mytask = JRequest::getVar('task','','request','word');
		$view = JRequest::getVar('view','','request','word');
		$layout = JRequest::getVar('layout','','request','word');
		$controller = JRequest::getVar('controller');
		$divLevel = JRequest::getInt('divLevel',0);
		$division = JRequest::getInt('division',0);
		$firstlink = '';
		$lastlink = '';
		//$mdlRound = JModel::getInstance("Round", "JoomleagueModel");
		//$mdlRounds = JModel::getInstance("Rounds", "JoomleagueModel");
		//$mdlRounds->setProjectId($project->id);

		$firstRound			= sportsmanagementModelRounds::getFirstRound($project->id);
		$lastRound			= sportsmanagementModelRounds::getLastRound($project->id);
		$previousRound 		= sportsmanagementModelRounds::getPreviousRound($roundid, $project->id);
		$nextRound			= sportsmanagementModelRounds::getNextRound($roundid, $project->id);
		$currentRoundcode 	= sportsmanagementModelRound::getRoundcode($roundid);
		$arrRounds 			= sportsmanagementModelRounds::getRoundsOptions($project->id);
		$rlimit 			= count($arrRounds);

		$params = array();
		$params['option'] = $option;
		if ($view){$params['view'] = $view;}
		$params['p'] = $project->id;
		if ($controller){$params['controller'] = $controller;}
		if ($layout){$params['layout'] = $layout;}
		if ($mytask){$params['task'] = $mytask;}
		if ($division > 0){$params['division'] = $division;}
		if ($divLevel > 0){$params['divLevel'] = $divLevel;}
		$prediction_id = JRequest::getInt("prediction_id",0);
		if($prediction_id >0) {
			$params['prediction_id']= $prediction_id;
		}
		
		$query = JURI::buildQuery($params);
		$link = JRoute::_('index.php?' . $query);
		$backward = sportsmanagementModelRound::getRoundId($currentRoundcode-1, $project->id);
		$forward = sportsmanagementModelRound::getRoundId($currentRoundcode+1, $project->id);

		if ($firstRound['id'] != $roundid)
		{
			$params['r'] = $backward;
			$query = JURI::buildQuery($params);
			$link = JRoute::_('index.php?' . $query . '#'.$option.'_top');
			$prevlink = JHTML::link($link,JText::_('COM_SPORTSMANAGEMENT_GLOBAL_PREV'));

			$params['r'] = $firstRound['id'];
			$query = JURI::buildQuery($params);
			$link = JRoute::_('index.php?' . $query . '#'.$option.'_top');
			$firstlink = JHTML::link($link,JText::_('COM_SPORTSMANAGEMENT_GLOBAL_PAGINATION_START')) . $spacer4;
		}
		else
		{
			$prevlink = JText::_('COM_SPORTSMANAGEMENT_GLOBAL_PREV');
			$firstlink = JText::_('COM_SPORTSMANAGEMENT_GLOBAL_PAGINATION_START') . $spacer4;
		}
		if ($lastRound['id'] != $roundid)
		{
			$params['r'] = $forward;
			$query = JURI::buildQuery($params);
			$link = JRoute::_('index.php?'.$query.'#'.$option.'_top');
			$nextlink = $spacer4;
			$nextlink .= JHTML::link($link,JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NEXT'));

			$params['r'] = $lastRound['id'];
			$query = JURI::buildQuery($params);
			$link = JRoute::_('index.php?' . $query . '#'.$option.'_top');
			$lastlink = $spacer4 . JHTML::link($link,JText::_('COM_SPORTSMANAGEMENT_GLOBAL_PAGINATION_END'));
		}
		else
		{
			$nextlink = $spacer4 . JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NEXT');
			$lastlink = $spacer4 . JText::_('COM_SPORTSMANAGEMENT_GLOBAL_PAGINATION_END');
		}
		$limit = count($arrRounds);
		$low = $currentRoundcode - 3;
		$high = $currentRoundcode + 3;
		for ($counter=1; $counter <= $limit; $counter++)
		{
				$round = $arrRounds[$counter-1];
				$roundcode = (int) $round->roundcode;
				if($roundcode < $low || $roundcode > $high) continue;
				if ( $roundcode < 10 )
				{
					$pagenumber = '0' . $roundcode;
				}
				else
				{
					$pagenumber = $roundcode;
				}
				if ($round->id != $roundid)
				{
					$params['r']= $round->id;
					$query		= JURI::buildQuery($params);
					$link		= JRoute::_('index.php?' . $query . '#'.$option.'_top');
					$pageNav   .= $spacer4 . JHTML::link($link,$pagenumber);
				}
				else
				{
					$pageNav .= $spacer4 . $pagenumber;
				}
		}
		return '<span class="pageNav">&laquo;' . $spacer2 . $firstlink . $prevlink . $pageNav . $nextlink .  $lastlink . $spacer2 . '&raquo;</span>';
	}

	function pagenav2($jl_task,$rlimit,$currentRoundcode=0,$user='',$mode='')
	{
	   $option = JRequest::getCmd('option');
		$mytask = JRequest::getVar('task',false);
		$divLevel = JRequest::getInt('divLevel',0);
		$division = JRequest::getInt('division',0);

		$pageNav2 = '<form action="" method="get" style="display:inline;">';
		$pageNav2 .= '<select class="inputbox" onchange="joomleague_changedoc(this)">';

		$params = array();
		$params['option'] = $option;
		$params['controller'] = $jl_task;
		$params['p'] = $this->projectid;
		if ($user){$params['uid'] = $user;}
		if ($mode){$params['mode'] = $mode;}
		if ($mytask){$params['task'] = $mytask;}
		if ($division > 0){$params['division'] = $division;}
		if ($divLevel > 0){$params['divLevel'] = $divLevel;}

		for ($counter=1; $counter <= $rlimit; $counter++)
		{
			if ($counter< 10){$pagenumber="0" . $counter;}else{$pagenumber = $counter;}
			if ($counter <= $rlimit)
			{
				$params['r'] = $counter;
				$query = JURI::buildQuery($params);
				$link  = JRoute::_('index.php?' . $query);

				$pageNav2 .= "<option value='".$link."'";
				if ($counter==$currentRoundcode)
				{
					$pageNav2 .= " selected='selected'";
				}
				$pageNav2 .= '>';
			}
			$pageNav2 .= $pagenumber . '</option>';
		}
		$pageNav2 .= '</select></form>';
		return $pageNav2;
	}

}
?>