<?php
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

//require_once (JLG_PATH_ADMIN .DS.'models'.DS.'round.php');
//require_once (JLG_PATH_ADMIN .DS.'models'.DS.'rounds.php');

/**
 * sportsmanagementPagination
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelPagination extends JModelLegacy
{
    public static $nextlink = '';
    public static $prevlink = '';

	
    /**
     * sportsmanagementModelPagination::getnextlink()
     * 
     * @return
     */
    function getnextlink()
    {
        $option = JRequest::getCmd('option');
       $mainframe = JFactory::getApplication();
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
       {
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' nextink'.'<pre>'.print_r($this->nextlink,true).'</pre>' ),'');
        }
        
        return $this->nextlink;
    }
    
    /**
	 * create and return the round page navigation
	 *
	 * @param object $project
	 * @return string
	 */
	function pagenav($project)
	{
	   $option = JRequest::getCmd('option');
       $mainframe = JFactory::getApplication();
       
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
        
        if (empty($roundid) )
        {
            $roundid = $project->current_round;
        }
        
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' project->current_round'.'<pre>'.print_r($project->current_round,true).'</pre>' ),'');
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' current_round'.'<pre>'.print_r($roundid,true).'</pre>' ),'');
        
		//$mdlRound = JModelLegacy::getInstance("Round", "JoomleagueModel");
		//$mdlRounds = JModelLegacy::getInstance("Rounds", "JoomleagueModel");
		//sportsmanagementModelRounds::setProjectId($project->id);

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
		
		$query = JUri::buildQuery($params);
		$link = JRoute::_('index.php?' . $query);
		$backward = sportsmanagementModelRound::getRoundId($currentRoundcode-1, $project->id);
		$forward = sportsmanagementModelRound::getRoundId($currentRoundcode+1, $project->id);

		if ($firstRound['id'] != $roundid)
		{
			$params['r'] = $backward;
			$query = JUri::buildQuery($params);
			$link = JRoute::_('index.php?' . $query . '#'.$option.'_top');
            self::$prevlink = $link;
			$prevlink = JHtml::link($link,JText::_('COM_SPORTSMANAGEMENT_GLOBAL_PREV'));
            
            if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
       {
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' prevlink'.'<pre>'.print_r($link,true).'</pre>' ),'');
            }

			$params['r'] = $firstRound['id'];
			$query = JUri::buildQuery($params);
			$link = JRoute::_('index.php?' . $query . '#'.$option.'_top');
			$firstlink = JHtml::link($link,JText::_('COM_SPORTSMANAGEMENT_GLOBAL_PAGINATION_START')) . $spacer4;
		}
		else
		{
			$prevlink = JText::_('COM_SPORTSMANAGEMENT_GLOBAL_PREV');
			$firstlink = JText::_('COM_SPORTSMANAGEMENT_GLOBAL_PAGINATION_START') . $spacer4;
		}
		
        if ($lastRound['id'] != $roundid)
		{
			$params['r'] = $forward;
			$query = JUri::buildQuery($params);
			$link = JRoute::_('index.php?'.$query.'#'.$option.'_top');
            self::$nextlink = $link;
            
            if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
       {
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' nextink'.'<pre>'.print_r($link,true).'</pre>' ),'');
            }
            
			$nextlink = $spacer4;
			$nextlink .= JHtml::link($link,JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NEXT'));

			$params['r'] = $lastRound['id'];
			$query = JUri::buildQuery($params);
			$link = JRoute::_('index.php?' . $query . '#'.$option.'_top');
			$lastlink = $spacer4 . JHtml::link($link,JText::_('COM_SPORTSMANAGEMENT_GLOBAL_PAGINATION_END'));
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
					$query		= JUri::buildQuery($params);
					$link		= JRoute::_('index.php?' . $query . '#'.$option.'_top');
					$pageNav   .= $spacer4 . JHtml::link($link,$pagenumber);
				}
				else
				{
					$pageNav .= $spacer4 . $pagenumber;
				}
		}
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
       {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' firstlink'.'<pre>'.print_r($firstlink,true).'</pre>' ),'');
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' prevlink'.'<pre>'.print_r($prevlink,true).'</pre>' ),'');
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' nextlink'.'<pre>'.print_r($nextlink,true).'</pre>' ),'');
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' lastlink'.'<pre>'.print_r($lastlink,true).'</pre>' ),'');
        }
        
		return '<span class="pageNav">&laquo;' . $spacer2 . $firstlink . $prevlink . $pageNav . $nextlink .  $lastlink . $spacer2 . '&raquo;</span>';
	}

	/**
	 * sportsmanagementModelPagination::pagenav2()
	 * 
	 * @param mixed $jl_task
	 * @param mixed $rlimit
	 * @param integer $currentRoundcode
	 * @param string $user
	 * @param string $mode
	 * @return
	 */
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
				$query = JUri::buildQuery($params);
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