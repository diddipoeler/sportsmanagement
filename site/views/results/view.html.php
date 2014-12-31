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

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.view');
jimport( 'joomla.filesystem.file' );


/**
 * sportsmanagementViewResults
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewResults extends JViewLegacy
{

	/**
	 * sportsmanagementViewResults::display()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	public function display($tpl = null)
	{
		// Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        $option = JRequest::getCmd('option');
        $app = JFactory::getApplication();
        $roundcode = 0;
//		$version = urlencode(sportsmanagementHelper::getVersion());
//		$css		= 'components/com_sportsmanagement/assets/css/tabs.css?v='.$version;
//		$document->addStyleSheet($css);

		$document->addScript ( JUri::root(true).'/components/'.$option.'/assets/js/smsportsmanagement.js' );
        //add js file
		// welche joomla version
if(version_compare(JVERSION,'3.0.0','ge')) 
{
JHtml::_('behavior.framework', true);
}
else
{
JHtml::_( 'behavior.mootools' );    
}
		$model	= $this->getModel();
				
		$matches = $model->getMatches($model::$cfg_which_database);
		sportsmanagementModelProject::setProjectID(JRequest::getInt('p',0));
		$config	= sportsmanagementModelProject::getTemplateConfig($this->getName(),$model::$cfg_which_database);
		$project = sportsmanagementModelProject::getProject($model::$cfg_which_database);
        
        sportsmanagementModelPagination::pagenav($project,$model::$cfg_which_database);
		$mdlPagination = JModelLegacy::getInstance("Pagination","sportsmanagementModel");
        
        //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' Pagination'.'<pre>'.print_r($mdlPagination,true).'</pre>' ),'');
        //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' Pagination->prev'.'<pre>'.print_r($mdlPagination->get('prevlink'),true).'</pre>' ),'');
        //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' Pagination->next'.'<pre>'.print_r($mdlPagination->getnextlink(),true).'</pre>' ),'');
        
		//$roundcode = sportsmanagementModelRound::getRoundcode($model->roundid);
        $roundcode = sportsmanagementModelRound::getRoundcode($model::$roundid,$model::$cfg_which_database);
		$rounds = sportsmanagementHelper::getRoundsOptions($project->id, 'ASC', $model::$cfg_which_database);
		
		$this->assign('roundsoption', $rounds);
		$this->assignRef('project', $project);
		$lists = array();
		
		if (isset($this->project))
		{
			$this->assign('overallconfig', sportsmanagementModelProject::getOverallConfig($model::$cfg_which_database));
			$this->assign('config',	array_merge($this->overallconfig, $config));
			$this->assign('teams', sportsmanagementModelProject::getTeamsIndexedByPtid(0,'name',$model::$cfg_which_database));
			$this->assign('showediticon', $model->getShowEditIcon());
			$this->assign('division', $model->getDivision($model::$cfg_which_database));
			$this->assignRef('matches',	$matches);
			//$this->assignRef('roundid',	$model->roundid);
            $this->assignRef('roundid',	$model::$roundid);
			$this->assignRef('roundcode', $roundcode);
			$this->assign('rounds',	sportsmanagementModelProject::getRounds('ASC',$model::$cfg_which_database));
			$this->assign('favteams', sportsmanagementModelProject::getFavTeams($model::$cfg_which_database));
			$this->assign('projectevents', sportsmanagementModelProject::getProjectEvents(0,$model::$cfg_which_database));
			$this->assignRef('model', $model);
			$this->assign('isAllowed', $model->isAllowed());
            $extended = sportsmanagementHelper::getExtended($this->project->extended, 'project');
            $this->assignRef( 'extended', $extended );

if ( ($this->overallconfig['show_project_rss_feed']) == 1 )
	  {
	  $mod_name               = "mod_jw_srfr";
	  $rssfeeditems = '';
    $rssfeedlink = $this->extended->getValue('COM_SPORTSMANAGEMENT_PROJECT_RSS_FEED_LIVE_RESULTS');
    if ( $rssfeedlink )
    {
    $this->assignRef( 'rssfeeditems', $model->getRssFeeds($rssfeedlink,$this->overallconfig['rssitems']) );
    }
    else
    {
    $this->assignRef( 'rssfeeditems', $rssfeeditems );
    }
    //echo 'rssfeed<br><pre>'.print_r($rssfeedlink,true).'</pre><br>';
    
  
       }
      
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' current_round'.'<pre>'.print_r($project->current_round,true).'</pre>' ),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' matches'.'<pre>'.print_r($this->matches,true).'</pre>' ),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' roundid'.'<pre>'.print_r($this->roundid,true).'</pre>' ),'');       
            
            $lists['rounds'] = JHtml::_('select.genericlist',$rounds,'current_round','class="inputbox" size="1" onchange="joomleague_changedoc(this);','value','text',$project->current_round);
			$this->assignRef('lists',$lists);
		
			if (!isset($this->config['switch_home_guest'])){$this->config['switch_home_guest']=0;}
			if (!isset($this->config['show_dnp_teams_icons'])){$this->config['show_dnp_teams_icons']=0;}
			if (!isset($this->config['show_results_ranking'])){$this->config['show_results_ranking']=0;}
		}
        
        //$this->assign('show_debug_info', JComponentHelper::getParams($option)->get('show_debug_info',0) );
		
		// Set page title
		$pageTitle = JText::_('COM_SPORTSMANAGEMENT_RESULTS_PAGE_TITLE');
		if ( isset( $this->project->name ) )
		{
			$pageTitle .= ': ' . $this->project->name;
		}
		$document->setTitle($pageTitle);

		//build feed links
		$feed = 'index.php?option='.$option.'&view=results&p='.$this->project->id.'&format=feed';
		$rss = array('type' => 'application/rss+xml', 'title' => JText::_('COM_SPORTSMANAGEMENT_RESULTS_RSSFEED'));

		// add the links
		$document->addHeadLink(JRoute::_($feed.'&type=rss'), 'alternate', 'rel', $rss);
        $view = JRequest::getVar( "view") ;
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$option.'/assets/css/'.$view.'.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);

		parent::display($tpl);
	}

	/**
	 * return html code for not playing teams
	 * 
	 * @param array $games
	 * @param array $teams
	 * @param array $config
	 * @param array $favteams
	 * @param object $project
	 * @return string html
	 */
	public static function showNotPlayingTeams(&$games,&$teams,&$config,&$favteams,&$project)
	{
		$output = '';
		$playing_teams = array();
		foreach ($games as $game)
		{
			self::addPlayingTeams($playing_teams,$game->projectteam1_id,$game->projectteam2_id,$game->published);
		}
		$x=0;
		$not_playing=count($teams) - count($playing_teams);
		if ($not_playing > 0)
		{
			$output .= '<b>'.JText::sprintf('COM_SPORTSMANAGEMENT_RESULTS_TEAMS_NOT_PLAYING',$not_playing).'</b> ';
			foreach ($teams AS $id => $team)
			{
				if (isset($team->projectteamid) && in_array($team->projectteamid,$playing_teams))
				{
					continue; //if team is playing,go to next
				}
				if ($x > 0)
				{
					$output .= ', ';
				}
				if ($config['show_logo_small'] > 0 && $config['show_dnp_teams_icons'])
				{
					$output .= self::getTeamClubIcon($team,$config['show_logo_small']).'&nbsp;';
				}
				$isFavTeam = in_array($team->id, $favteams);
				$output .= sportsmanagementHelper::formatTeamName($team, 't'.$team->id, $config, $isFavTeam );
				$x++;
			}
		}
		return $output;
	}

	/**
	 * sportsmanagementViewResults::addPlayingTeams()
	 * 
	 * @param mixed $playing_teams
	 * @param mixed $hometeam
	 * @param mixed $awayteam
	 * @param bool $published
	 * @return void
	 */
	public static function addPlayingTeams(&$playing_teams,$hometeam,$awayteam,$published=false)
	{
		if ($hometeam>0 && !in_array($hometeam,$playing_teams) && $published){$playing_teams[]=$hometeam;}
		if ($awayteam>0 && !in_array($awayteam,$playing_teams) && $published){$playing_teams[]=$awayteam;}
	}
	
	/**
	 * returns html <img> for club assigned to team
	 * @param object team
	 * @param int type=1 for club small image,or 2 for club country
	 * @param boolean $with_space
	 * @return unknown_type
	 */
	public static function getTeamClubIcon($team,$type=1,$attribs=array())
	{
		if(!isset($team->name)) return "";
		$title = $team->name;
        
        //echo 'team<pre>',print_r($team,true),'</pre><br>'; 
        
		$attribs = array_merge(array('title' => $title,$attribs));
		if ($type==1)
		{
			$attribs = array_merge(array('width' => '20',$attribs));
            //if (!empty($team->logo_small) && JFile::exists($team->logo_small))
            if ( !empty($team->logo_small) )
			{
				$image = JHtml::image(COM_SPORTSMANAGEMENT_PICTURE_SERVER.$team->logo_small,$title,$attribs);
			}
			else
			{
				$image = JHtml::image(COM_SPORTSMANAGEMENT_PICTURE_SERVER.sportsmanagementHelper::getDefaultPlaceholder("clublogosmall"),$title,$attribs);
			}
		}
        elseif ($type==5)
		{
		  $attribs = array_merge(array('width' => '20',$attribs));
			//if (!empty($team->logo_middle) && JFile::exists($team->logo_middle))
            if ( !empty($team->logo_middle) )
			{
				$image = JHtml::image(COM_SPORTSMANAGEMENT_PICTURE_SERVER.$team->logo_middle,$title,$attribs);
			}
			else
			{
				$image = JHtml::image(COM_SPORTSMANAGEMENT_PICTURE_SERVER.sportsmanagementHelper::getDefaultPlaceholder("clublogomedium"),$title,$attribs);
			}
		}
        elseif ($type==6)
		{
		  $attribs = array_merge(array('width' => '20',$attribs));
			//if (!empty($team->logo_big) && JFile::exists($team->logo_big))
            if ( !empty($team->logo_big) )
			{
				//$image = JHtml::image($team->logo_big,$title,$attribs);
                $image="<a href=\"".COM_SPORTSMANAGEMENT_PICTURE_SERVER.$team->logo_big."\" title=\"".$title."\" data-toggle=\"modal\" data-target=\"#pt".$team->projectteamid."\">";
				$image.=JHtml::image(COM_SPORTSMANAGEMENT_PICTURE_SERVER.$team->logo_big,$title,$attribs);
				$image.="</a>";
                $image.="<div class=\"modal fade\" id=\"pt".$team->projectteamid."\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"modal\" aria-hidden=\"true\">";
                $image.="<div class=\"modal-header\">";
                $image.="<button type=\"button\" class=\"close\" data-dismiss=\"modal\"><span aria-hidden=\"true\">&times;</span></button>";
                $image.="</div>";
                $image.= JHtml::image(COM_SPORTSMANAGEMENT_PICTURE_SERVER.$team->logo_big, $title, array('title' => $title,'class' => "img-rounded" ));
                $image.="</div>";
                
                
			}
			else
			{
				$image = JHtml::image(COM_SPORTSMANAGEMENT_PICTURE_SERVER.sportsmanagementHelper::getDefaultPlaceholder("clublogobig"),$title,$attribs);
			}
		}
		elseif ($type==2 && !empty($team->country))
		{
			$image = JSMCountries::getCountryFlag($team->country);
			if (empty($image))
			{
				$image = JHtml::image(COM_SPORTSMANAGEMENT_PICTURE_SERVER.sportsmanagementHelper::getDefaultPlaceholder("icon"),$title,$attribs);
			}
		}
        
        elseif ($type==7 && !empty($team->country) && !empty($team->logo_big) && JFile::exists($team->logo_big) )
		{
			//$image = JSMCountries::getCountryFlag($team->country);
			if (empty($image))
			{
				$attribs = array_merge(array('width' => '20',$attribs));
                //$image = JHtml::image($team->logo_big,$title,$attribs).' '.JSMCountries::getCountryFlag($team->country);
                $image="<a href=\"".COM_SPORTSMANAGEMENT_PICTURE_SERVER.$team->logo_big."\" title=\"".$title."\" data-toggle=\"modal\" data-target=\"#pt".$team->projectteamid."\">";
				$image.=JHtml::image(COM_SPORTSMANAGEMENT_PICTURE_SERVER.$team->logo_big,$title,$attribs);
				$image.="</a>";
                $image.=' '.JSMCountries::getCountryFlag($team->country);
                $image.="<div class=\"modal fade\" id=\"pt".$team->projectteamid."\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"modal\" aria-hidden=\"true\">";
                $image.="<div class=\"modal-header\">";
                $image.="<button type=\"button\" class=\"close\" data-dismiss=\"modal\"><span aria-hidden=\"true\">&times;</span></button>";
                $image.="</div>";
                $image.= JHtml::image(COM_SPORTSMANAGEMENT_PICTURE_SERVER.$team->logo_big, $title, array('title' => $title,'class' => "img-rounded" ));
                $image.="</div>";
			}
		}
        elseif ($type==7 && !empty($team->country) && !empty($team->logo_big) && !JFile::exists($team->logo_big) )
		{
			//$image = JSMCountries::getCountryFlag($team->country);
			if (empty($image))
			{
				$image = JHtml::image(COM_SPORTSMANAGEMENT_PICTURE_SERVER.sportsmanagementHelper::getDefaultPlaceholder("clublogobig"),$title,$attribs).' '.JSMCountries::getCountryFlag($team->country);
			}
		}
        
        elseif ($type==3 && !empty($team->country) && !empty($team->logo_small) && JFile::exists($team->logo_small) )
		{
			//$image = JSMCountries::getCountryFlag($team->country);
			if (empty($image))
			{
				$image = JHtml::image($team->logo_small,$title,$attribs).' '.JSMCountries::getCountryFlag($team->country);
			}
		}
        elseif ($type==3 && !empty($team->country) && !empty($team->logo_small) && !JFile::exists($team->logo_small) )
		{
			//$image = JSMCountries::getCountryFlag($team->country);
			if (empty($image))
			{
				$image = JHtml::image(COM_SPORTSMANAGEMENT_PICTURE_SERVER.sportsmanagementHelper::getDefaultPlaceholder("icon"),$title,$attribs).' '.JSMCountries::getCountryFlag($team->country);
			}
		}
    elseif ($type==4 && !empty($team->country) && !empty($team->logo_small) && JFile::exists($team->logo_small) )
		{
			//$image = JSMCountries::getCountryFlag($team->country);
			if (empty($image))
			{
				$image = JSMCountries::getCountryFlag($team->country).' '.JHtml::image($team->logo_small,$title,$attribs);
			}
		}
        elseif ($type==4 && !empty($team->country) && !empty($team->logo_small) && !JFile::exists($team->logo_small) )
		{
			//$image = JSMCountries::getCountryFlag($team->country);
			if (empty($image))
			{
				$image = JSMCountries::getCountryFlag($team->country).' '.JHtml::image(COM_SPORTSMANAGEMENT_PICTURE_SERVER.sportsmanagementHelper::getDefaultPlaceholder("icon"),$title,$attribs);
			}
		}
		else
		{
			$image='';
		}

		return $image;
	}
	

	/**
	 * return an array of matches indexed by date
	 *
	 * @return array
	 */
	public static function sortByDate($matches)
	{
		$dates=array();
		foreach ((array) $matches as $m)
		{
			$date=substr($m->match_date,0,10);
			if (!isset($dates[$date]))
			{
				$dates[$date]=array($m);
			}
			else
			{
				$dates[$date][]=$m;
			}
		}
		return $dates;
	}
	
	/**
	 * used in form row template
	 * TODO: move to own template file
	 * 
	 * @param int $i
	 * @param object $match
	 * @param boolean $backend
	 */
	public function editPartResults($i,$match,$backend=false)
	{
		$link="javascript:void(0)";
		$params=array("onclick" => "switchMenu('part".$match->id."')");
		$imgTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_EDIT_MATRIX_ROUNDS_PART_RESULT');
		$desc=JHtml::image(	JURI::root()."media/com_sportsmanagement/jl_images/sort01.gif",
		$imgTitle,array("border" => 0,"title" => $imgTitle));
		echo JHtml::link($link,$desc,$params);

		echo '<span id="part'.$match->id.'" style="display:none">';
		echo '<br />';

		$partresults1= explode(";",$match->team1_result_split);
		$partresults2= explode(";",$match->team2_result_split);

		for ($x=0; $x < ($this->project->game_parts); $x++)
		{
			echo ($x+1).".:";
			echo '<input type="text" style="font-size:9px;" name="team1_result_split';
			echo (! is_null($i)) ? $match->id : '';
			echo '[]" value="';
			echo (isset($partresults1[$x])) ? $partresults1[$x] : '';
			echo '" size="2" tabindex="1" class="inputbox"';
			if (! is_null($i))
			{
				echo ' onchange="';
				if ($backend)
				{
					echo 'document.adminForm.cb'.$i.'.checked=true; isChecked(this.checked);';
				}
				else
				{
					echo '$(\'cb'.$i.'\').checked=true;';
				}
				echo '"';
			}
			echo ' />';
			echo ':';
			echo '<input type="text" style="font-size:9px;"';
			echo ' name="team2_result_split';
			echo (! is_null($i)) ? $match->id : '';
			echo '[]" value="';
			echo (isset($partresults2[$x])) ? $partresults2[$x] : '';
			echo '" size="2" tabindex="1" class="inputbox" ';
			if (! is_null($i))
			{
				echo 'onchange="';
				if ($backend)
				{
					echo 'document.adminForm.cb'.$i.'.checked=true; isChecked(this.checked);';
				}
				else
				{
					echo '$(\'cb'.$i.'\').checked=true;';
				}
				echo '"';
			}
			echo '/>';
			if (($x) < ($this->project->game_parts)){echo '<br />';}
		}

		if ($this->project->allow_add_time)
		{
			if($match->match_result_type >0){
				echo JText::_('COM_SPORTSMANAGEMENT_RESULTS_OVERTIME').':';
				echo '<input type="text" style="font-size:9px;"';
				echo ' name="team1_result_ot'.$match->id.'"';
				echo ' value="';
				echo isset($match->team1_result_ot) ? ''.$match->team1_result_ot : '';
				echo '"';
				echo ' size="2" tabindex="1" class="inputbox" onchange="$(\'cb'.$i.'\').checked=true;" />';
				echo ':';
				echo '<input type="text" style="font-size:9px;"';
				echo ' name="team2_result_ot'.$match->id.'"';
				echo ' value="';
				echo isset($match->team2_result_ot) ? ''.$match->team2_result_ot : '';
				echo '"';
				echo ' size="2" tabindex="1" class="inputbox" onchange="$(\'cb'.$i.'\').checked=true;" />';
			}
			if($match->match_result_type == 2){
				echo '<br />';
				echo JText::_('COM_SPORTSMANAGEMENT_RESULTS_SHOOTOUT').':';
				echo '<input type="text" style="font-size:9px;"';
				echo ' name="team1_result_so'.$match->id.'"';
				echo ' value="';
				echo isset($match->team1_result_so) ? ''.$match->team1_result_so : '';
				echo '"';
				echo ' size="2" tabindex="1" class="inputbox" onchange="$(\'cb'.$i.'\').checked=true;" />';
				echo ':';
				echo '<input type="text" style="font-size:9px;"';
				echo ' name="team2_result_so'.$match->id.'"';
				echo ' value="';
				echo isset($match->team2_result_so) ? ''.$match->team2_result_so : '';
				echo '"';
				echo ' size="2" tabindex="1" class="inputbox" onchange="$(\'cb'.$i.'\').checked=true;" />';
			}
		}
		echo "</span>";
	}
	
	
	/**
	* formats the score according to settings
	*
	* @param object $game
	* @return string
	*/
	public static function formatScoreInline($game,&$config)
	{
		if ($config['switch_home_guest'])
		{
			$homeResult	= $game->team2_result;
			$awayResult	= $game->team1_result;
			$homeResultOT	= $game->team2_result_ot;
			$awayResultOT	= $game->team1_result_ot;
			$homeResultSO	= $game->team2_result_so;
			$awayResultSO	= $game->team1_result_so;
			$homeResultDEC	= $game->team2_result_decision;
			$awayResultDEC	= $game->team1_result_decision;
		}
		else
		{
			$homeResult	= $game->team1_result;
			$awayResult	= $game->team2_result;
			$homeResultOT	= $game->team1_result_ot;
			$awayResultOT	= $game->team2_result_ot;
			$homeResultSO	= $game->team1_result_so;
			$awayResultSO	= $game->team2_result_so;
			$homeResultDEC	= $game->team1_result_decision;
			$awayResultDEC	= $game->team2_result_decision;
		}
	
		$result=$homeResult.'&nbsp;'.$config['seperator'].'&nbsp;'.$awayResult;
		if ($game->alt_decision)
		{
			$result='<b style="color:red;">';
			$result .= $homeResultDEC.'&nbsp;'.$config['seperator'].'&nbsp;'.$awayResultDEC;
			$result .= '</b>';
	
		}
		if (isset($homeResultSO) || isset($formatScoreawayResultSO))
		{
			if ($config['result_style']==1)
            {
				$result .= '<br />';
			}
            else
            {
                $result .= ' ';
			}
			$result .= '('.JText::_('COM_SPORTSMANAGEMENT_RESULTS_SHOOTOUT').' ';
			$result .= $homeResultSO.'&nbsp;'.$config['seperator'].'&nbsp;'.$awayResultSO;
			$result .= ')';
		}
		else
		{
			if ($game->match_result_type==2)
			{
				if ($config['result_style']==1)
                {
					$result .= '<br />';
				}
                else
                {
                    $result .= ' ';
				}
				$result .= '('.JText::_('COM_SPORTSMANAGEMENT_RESULTS_SHOOTOUT');
				$result .= ')';
			}
		}
		if (isset($homeResultOT) || isset($awayResultOT))
		{
			if ($config['result_style']==1)
            {
				$result .= '<br />';
			}
            else
            {
                $result .= ' ';
			}
			$result .= '('.JText::_('COM_SPORTSMANAGEMENT_RESULTS_OVERTIME').' ';
			$result .= $homeResultOT.'&nbsp;'.$config['seperator'].'&nbsp;'.$awayResultOT;
			$result .= ')';
		}
		else
		{
			if ($game->match_result_type==1)
			{
				if ($config['result_style']==1)
                {
					$result .= '<br />';
				}
                else
                {
                    $result .= ' ';
				}
				$result .= '('.JText::_('COM_SPORTSMANAGEMENT_RESULTS_OVERTIME');
				$result .= ')';
			}
		}

		return $result;
	}
	
	/**
	 * return match state html code
	 * @param $game
	 * @param $config
	 * @return unknown_type
	 */
	public static function showMatchState(&$game,&$config)
	{
		$output='';

		if ($game->cancel > 0)
		{
			$output .= $game->cancel_reason;
		}
		else
		{
			$output .= self::formatScoreInline($game,$config);
		}

		return $output;
	}
    
    /**
     * sportsmanagementViewResults::showMatchSummaryAsSqueezeBox()
     * 
     * @param mixed $game
     * @return
     */
    function showMatchSummaryAsSqueezeBox(&$game)
	{
	/*
    $output = '<script language="JavaScript">';
    $output .= 'var options = {size: {x: 300, y: 250}}';
	$output .= 'SqueezeBox.initialize(options)';
    $output .= "SqueezeBox.setContent('string','nummer:')";
    $output .= '</script>'; 
    echo $output;
    */  
    return $game->summary;
    }   

	/**
	 * sportsmanagementViewResults::showMatchRefereesAsTooltip()
	 * 
	 * @param mixed $game
	 * @return void
	 */
	function showMatchRefereesAsTooltip(&$game)
	{
		if ($this->config['show_referee'])
		{
			if ($this->project->teams_as_referees)
			{
				$referees=$this->model->getMatchRefereeTeams($game->id);
			}
			else
			{
				$referees=$this->model->getMatchReferees($game->id);
			}

			if (!empty($referees))
			{
				$toolTipTitle	= JText::_('COM_SPORTSMANAGEMENT_RESULTS_REF_TOOLTIP');
				$toolTipText	= '';

				foreach ($referees as $ref)
				{
					if ($this->project->teams_as_referees)
					{
						$toolTipText .= $ref->teamname.' ('.$ref->position_name.')'.'&lt;br /&gt;';
					}
					else
					{
						$toolTipText .= ($ref->firstname ? $ref->firstname.' '.$ref->lastname : $ref->lastname).' ('.$ref->position_name.')'.'&lt;br /&gt;';
					}
				}

				?>
			<!-- Referee tooltip -->
			<span class="hasTip"
				title="<?php echo $toolTipTitle; ?> :: <?php echo $toolTipText; ?>"> <img
				src="<?php echo JURI::root(); ?>media/com_sportsmanagement/jl_images/icon-16-Referees.png"
				alt="" title="" /> </span>
			
				<?php
			}
			else
			{
				?>&nbsp;<?php
			}
		}
	}
	
	/**
	 * sportsmanagementViewResults::showReportDecisionIcons()
	 * 
	 * @param mixed $game
	 * @return
	 */
	public static function showReportDecisionIcons(&$game)
	{
		//echo '<br /><pre>~'.print_r($game,true).'~</pre><br />';

		$output='';
		$report_link=sportsmanagementHelperRoute::getMatchReportRoute($game->project_id,$game->id);

		if ((($game->show_report) && (trim($game->summary) != '')) || ($game->alt_decision) || ($game->match_result_type > 0))
		{
			if ($game->alt_decision)
			{
				$imgTitle=JText::_($game->decision_info);
				$img='media/com_sportsmanagement/jl_images/court.gif';
			}
			else
			{
				$imgTitle=JText::_('Has match summary');
				$img='media/com_sportsmanagement/jl_images/zoom.png';
			}
			$output .= JHtml::_(	'link',
			$report_link,
			JHtml::image(JURI::root().$img,$imgTitle,array("border" => 0,"title" => $imgTitle)),
			array("title" => $imgTitle));
		}
		else
		{
			$output .= '&nbsp;';
		}

		return $output;
	}
	
	
	/**
	 * returns html for events in tabs
	 * @param object match
	 * @param array project events
	 * @param array match events
	 * @param aray match substitutions
	 * @param array $config
	 * @return string
	 */
	function showEventsContainerInResults($matchInfo,$projectevents,$matchevents,$substitutions=null,$config)
	{
		$output='';
		$result='';

		if ($this->config['use_tabs_events'])
		{
			// Make event tabs with JPane integrated function in Joomla 1.5 API
			$result	=& JPane::getInstance('tabs',array('startOffset'=>0));
			$output .= $result->startPane('pane');

			// Size of the event icons in the tabs (when used)
			$width = 20; $height = 20; $type = 4;
			// Never show event text or icon for each event list item (info already available in tab)
			$showEventInfo = 0;

			$cnt = 0;
			foreach ($projectevents AS $event)
			{
				//display only tabs with events
				foreach ($matchevents AS $me)
				{
					$cnt=0;
					if ($me->event_type_id == $event->id)
					{
						$cnt++;
						break;
					}
				}
				if($cnt==0){continue;}
				
				if ($this->config['show_events_with_icons'] == 1)
				{
					// Event icon as thumbnail on the tab (a placeholder icon is used when the icon does not exist)
					$imgTitle = JText::_($event->name);
					$tab_content = sportsmanagementHelper::getPictureThumb($event->icon, $imgTitle, $width, $height, $type);
				}
				else
				{
					$tab_content = JText::_($event->name);
				}

				$output .= $result->startPanel($tab_content, $event->id);
				$output .= '<table class="matchreport" border="0">';
				$output .= '<tr>';

				// Home team events
				$output .= '<td class="list">';
				$output .= '<ul>';
				foreach ($matchevents AS $me)
				{
					$output .= self::_formatEventContainerInResults($me, $event, $matchInfo->projectteam1_id, $showEventInfo);
				}
				$output .= '</ul>';
				$output .= '</td>';

				// Away team events
				$output .= '<td class="list">';
				$output .= '<ul>';
				foreach ($matchevents AS $me)
				{
					$output .= self::_formatEventContainerInResults($me, $event, $matchInfo->projectteam2_id, $showEventInfo);
				}
				$output .= '</ul>';
				$output .= '</td>';
				$output .= '</tr>';
				$output .= '</table>';
				$output .= $result->endPanel();
			}

			if (!empty($substitutions))
			{
				if ($this->config['show_events_with_icons'] == 1)
				{
					// Event icon as thumbnail on the tab (a placeholder icon is used when the icon does not exist)
					$imgTitle = JText::_('COM_SPORTSMANAGEMENT_IN_OUT');
					$pic_tab	= 'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/subst.png';
					$tab_content = sportsmanagementHelper::getPictureThumb($pic_tab, $imgTitle, $width, $height, $type);
				}
				else
				{
					$tab_content = JText::_('COM_SPORTSMANAGEMENT_IN_OUT');
				}

				$pic_time	= JURI::root().'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/playtime.gif';
				$pic_out	= JURI::root().'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/out.png';
				$pic_in		= JURI::root().'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/in.png';
				$imgTime = JHtml::image($pic_time,JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_MINUTE'),array(' title' => JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_MINUTE')));
				$imgOut  = JHtml::image($pic_out,JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_WENT_OUT'),array(' title' => JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_WENT_OUT')));
				$imgIn   = JHtml::image($pic_in,JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_CAME_IN'),array(' title' => JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_CAME_IN')));

				$output .= $result->startPanel($tab_content,'0');
				$output .= '<table class="matchreport" border="0">';
				$output .= '<tr>';
				$output .= '<td class="list">';
				$output .= '<ul>';
				foreach ($substitutions AS $subs)
				{
					$output .= self::_formatSubstitutionContainerInResults($subs,$matchInfo->projectteam1_id,$imgTime,$imgOut,$imgIn);
				}
				$output .= '</ul>';
				$output .= '</td>';
				$output .= '<td class="list">';
				$output .= '<ul>';
				foreach ($substitutions AS $subs)
				{
					$output .= self::_formatSubstitutionContainerInResults($subs,$matchInfo->projectteam2_id,$imgTime,$imgOut,$imgIn);
				}
				$output .= '</ul>';
				$output .= '</td>';
				$output .= '</tr>';
				$output .= '</table>';
				$output .= $result->endPanel();
			}
			$output .= $result->endPane();
		}
		else
		{
			$showEventInfo = ($this->config['show_events_with_icons'] == 1) ? 1 : 2;
			$output .= '<table class="matchreport" border="0">';
			$output .= '<tr>';

			// Home team events
			$output .= '<td class="list-left">';
			$output .= '<ul>';
			foreach ((array) $matchevents AS $me)
			{
				if ($me->ptid == $matchInfo->projectteam1_id)
				{
					$output .= self::_formatEventContainerInResults($me, $projectevents[$me->event_type_id], $matchInfo->projectteam1_id, $showEventInfo);
				}
			}
			$output .= '</ul>';
			$output .= '</td>';

			// Away team events
			$output .= '<td class="list-right">';
			$output .= '<ul>';
			foreach ($matchevents AS $me)
			{
				if ($me->ptid == $matchInfo->projectteam2_id)
				{
					$output .= self::_formatEventContainerInResults($me, $projectevents[$me->event_type_id], $matchInfo->projectteam2_id, $showEventInfo);
			    }
			}
			$output .= '</ul>';
			$output .= '</td>';
			$output .= '</tr>';
			$output .= '</table>';
		}

		return $output;
	}
	
	/**
	 * sportsmanagementViewResults::formatResult()
	 * 
	 * @param mixed $team1
	 * @param mixed $team2
	 * @param mixed $game
	 * @param mixed $reportLink
	 * @return
	 */
	public static function formatResult(&$team1,&$team2,&$game,&$reportLink,&$config)
	{
		$output	= '';
		// check home and away team for favorite team
		$fav = isset($team1->id) && in_array($team1->id,sportsmanagementModelProject::$favteams) ? 1 : 0;
		if(!$fav)
		{
			$fav = isset($team2->id) && in_array($team2->id,sportsmanagementModelProject::$favteams) ? 1 : 0;
		}
		// 0=no links
		// 1=For all teams
		// 2=For favorite team(s) only
		if($config['show_link_matchreport'] == 1 || ($config['show_link_matchreport'] == 2 && $fav))
		{
			$output = JHtml::_(	'link', $reportLink,
					'<span class="score0">'.sportsmanagementViewResults::showMatchState($game,$config).'</span>',
			array("title" => JText::_('COM_SPORTSMANAGEMENT_RESULTS_SHOW_MATCHREPORT')));
		}
		else
		{
			$output = sportsmanagementViewResults::showMatchState($game,$this->config);
		}

//		//added by stony - Ausgabe der Halbzeitresultate, wenn im Template freigeschaltet
//		$slhr = array(";", "NULL");
//		if($config['show_part_results'] && ((str_replace($slhr, '', $game->team1_result_split) != "") && (str_replace($slhr, '', $game->team2_result_split) != "")) )
//		{
//			$output .= '&nbsp;(' . str_replace(";","",$game->team1_result_split) . '&nbsp;'. ($config['seperator']) .'&nbsp;' . str_replace(";","",$game->team2_result_split) . ')';
//		}
        
        // verbessert diddipoeler
        $part_results_left = explode(";", $game->team1_result_split);
        $part_results_right = explode(";", $game->team2_result_split);
        if( $config['show_part_results'] )
        {
            for ($i = 0; $i < count($part_results_left); $i++)
            {
                if (isset($part_results_left[$i]))
                    {
                                $resultS = $part_results_left[$i] . '&nbsp;' . $config['seperator'] . '&nbsp;' . $part_results_right[$i];
                                $whichPeriod = $i + 1;
                                $output .= '<br /><span class="hasTip" title="' . JText::sprintf( 'COM_SPORTSMANAGEMENT_GLOBAL_NPART',  "$whichPeriod")  .'::' . $resultS . '" >' . $resultS . '</span>';
                                //if ($i != 0) {
                                //$ResultsTooltipTp .= ' | ' . $resultS;
                                //} else {
                                //$ResultsTooltipTp .= $resultS;
                                //}
                    }
            }
        }
        
        
        

		return $output;

	}
	
	/**
	 * sportsmanagementViewResults::_formatEventContainerInResults()
	 * 
	 * @param mixed $matchevent
	 * @param mixed $event
	 * @param mixed $projectteamId
	 * @param mixed $showEventInfo
	 * @return
	 */
	function _formatEventContainerInResults($matchevent, $event, $projectteamId, $showEventInfo)
	{
		// Meaning of $showEventInfo:
		// 0 : do not show event as text or as icon in a list item
		// 1 : show event as icon in a list item (before the time)
		// 2 : show event as text in a list item (after the time)
		$output='';
		if ($matchevent->event_type_id == $event->id && $matchevent->ptid == $projectteamId)
		{
			$output .= '<li class="events">';
			if ($showEventInfo == 1)
			{
				// Size of the event icons in the tabs
				$width = 20; $height = 20; $type = 4;
				$imgTitle = JText::_($event->name);
				$icon = sportsmanagementHelper::getPictureThumb($event->icon, $imgTitle, $width, $height, $type);

				$output .= $icon;
			}

			$event_minute = str_pad($matchevent->event_time, 2 ,'0', STR_PAD_LEFT);
			if ($this->config['show_event_minute'] == 1 && $matchevent->event_time > 0)
			{
				$output .= '<b>'.$event_minute.'\'</b> ';
			} 

			if ($showEventInfo == 2)
			{
				$output .= JText::_($event->name).' ';
			}

			if (strlen($matchevent->firstname1.$matchevent->lastname1) > 0)
			{
				$output .= sportsmanagementHelper::formatName(null, $matchevent->firstname1, $matchevent->nickname1, $matchevent->lastname1, $this->config["name_format"]);
			}
			else
			{
				$output .= JText :: _('COM_SPORTSMANAGEMENT_UNKNOWN_PERSON');
			}
			
			// only show event sum and match notice when set to on in template cofig
			if($this->config['show_event_sum'] == 1 || $this->config['show_event_notice'] == 1)
			{
				if (($this->config['show_event_sum'] == 1 && $matchevent->event_sum > 0) || ($this->config['show_event_notice'] == 1 && strlen($matchevent->notice) > 0))
				{
					$output .= ' (';
						if ($this->config['show_event_sum'] == 1 && $matchevent->event_sum > 0)
						{
							$output .= $matchevent->event_sum;
						}
						if (($this->config['show_event_sum'] == 1 && $matchevent->event_sum > 0) && ($this->config['show_event_notice'] == 1 && strlen($matchevent->notice) > 0))
						{
							$output .= ' | ';
						}
						if ($this->config['show_event_notice'] == 1 && strlen($matchevent->notice) > 0)
						{
							$output .= $matchevent->notice;
						}
					$output .= ')';
				}
			}
			
			$output .= '</li>';
		}
		return $output;
	}

	/**
	 * sportsmanagementViewResults::_formatSubstitutionContainerInResults()
	 * 
	 * @param mixed $subs
	 * @param mixed $projectteamId
	 * @param mixed $imgTime
	 * @param mixed $imgOut
	 * @param mixed $imgIn
	 * @return
	 */
	function _formatSubstitutionContainerInResults($subs,$projectteamId,$imgTime,$imgOut,$imgIn)
	{
		$output='';
		if ($subs->ptid == $projectteamId)
		{
			$output .= '<li class="events">';
			// $output .= $imgTime;
			$output .= '&nbsp;'.$subs->in_out_time.'. '.JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_MINUTE');
			$output .= '<br />';

			$output .= $imgOut;
			$output .= '&nbsp;'.sportsmanagementHelper::formatName(null, $subs->out_firstname, $subs->out_nickname, $subs->out_lastname, $this->config["name_format"]);
			$output .= '&nbsp;('.JText :: _($subs->out_position).')';
			$output .= '<br />';

			$output .= $imgIn;
			$output .= '&nbsp;'.sportsmanagementHelper::formatName(null, $subs->firstname, $subs->nickname, $subs->lastname, $this->config["name_format"]);
			$output .= '&nbsp;('.JText :: _($subs->in_position).')';
			$output .= '<br /><br />';
			$output .= '</li>';
		}
		return $output;
	}
}
?>
