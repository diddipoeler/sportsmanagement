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
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/


defined('_JEXEC') or die('Restricted access');


$teamformat = $params->get('teamformat', 'name');
$dateformat = $params->get('dateformat');
$timeformat = $params->get('timeformat');
$mode = $params->get('mode',0);
$textdiv = "";

$n = 1;
//if ($mode == 0)
echo '<div class="row" id="modjlplaygroundplan'.$mode.'">';
//else if ($mode == 1)
#modjlplaygroundplan1
foreach ($list as $match)
 	{

$playgroundname = "";
$playground_id = 0;
$picture = ""; 	  
	if ($mode == 0)
		{
		$textdiv .= '<div class="qslidejl">';
		}
if ($mode == 1)
{
$odd=$n&1;
		$textdiv .= '<div id="jlplaygroundplanis'.$odd.'" class="jlplaygroundplantextdivlist">';
		}
$n++;

if ($params->get ('show_playground_name',0)) 
{
$textdiv.= '<div class="jlplplaneplname"> ';
if ($match->playground_id != "")
{
$playgroundname = $match->playground_name;
$playground_id = $match->playground_slug;
}
else if ($match->team_playground_id != "")
{
$playgroundname = $match->team_playground_name;
$playground_id = $match->playground_team_slug;
}
elseif ($match->club_playground_id != "")
{
$playgroundname = $match->club_playground_name;
$playground_id = $match->playground_club_slug;
} 

if( $params->get('show_playground_link'))
{
$routeparameter = array();
$routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database',0);
$routeparameter['s'] = JRequest::getInt('s',0);
$routeparameter['p'] = $match->project_slug;
$routeparameter['pgid'] = $playground_id ;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('playground',$routeparameter);    
  
$playgroundname= JHTML::link($link, JText::sprintf( '%1$s', $playgroundname ) );
}
else
{
$playgroundname= JText::sprintf( '%1$s', $playgroundname);
}
$textdiv.= $playgroundname.'</div>';
}

if ($params->get ('show_playground_picture',0)) 
{
$textdiv.= '<div class="jlplplaneplpicture"> ';    

if ($match->playground_id != "")
{
$picture = $match->playground_picture;
}
else if ($match->team_playground_id != "")
{
$picture = $match->playground_team_picture;
}
elseif ($match->club_playground_id != "")
{
$picture = $match->playground_club_picture;
} 

if ( $picture )
{
$textdiv .= '<p>'.JHtml::image( $picture,"","width=".$params->get('picture_playground_width')).'</p>';
}

$textdiv.= '</div>';    
}
    
$textdiv .= '<div class="jlplplanedate">';
$textdiv .= JHtml::date( $match->match_date,$dateformat );
$textdiv .= " ".JText::_('MOD_SPORTSMANAGEMENT_PLAYGROUNDPLAN_JL_START_TIME')." ";
$textdiv .= JHtml::date( $match->match_date,$timeformat );
$textdiv.= '</div>';

if ($params->get ('show_project_name',0)) 
{
$textdiv .= '<div class="jlplplaneleaguename">';

$textdiv .= $match->project_name;
$textdiv.= '</div>';
}

if ($params->get ('show_league_name',0)) 
{
$textdiv .= '<div class="jlplplaneleaguename">';
$textdiv .= $match->league_name;
$textdiv.= '</div>';
}

$textdiv .= '<div>';
$textdiv .= '<div class="jlplplanetname">';

if( $params->get('show_club_logo'))
{
$team1logo= modSportsmanagementPlaygroundplanHelper::getTeamLogo($match->team1,$params->get('show_picture'));

if( $params->get('show_picture') == 'logo_big')
{
    $textdiv .= '<p>'.JHtml::image( $team1logo,"","width=".$params->get('picture_width')).'</p>';
}
else
{
$textdiv .= '<p>'.JHtml::image( $team1logo,"").'</p>';    
}

}

$textdiv .= '<p>'.modSportsmanagementPlaygroundplanHelper::getTeams($match->team1,$teamformat).'</p>';
$textdiv.= '</div>';
$textdiv .= '<div class="jlplplanetnamesep"> - </div>';
$textdiv .= '<div class="jlplplanetname">';

if( $params->get('show_club_logo'))
{
$team2logo= modSportsmanagementPlaygroundplanHelper::getTeamLogo($match->team2,$params->get('show_picture'));

if( $params->get('show_picture') == 'logo_big')
{
    $textdiv .= '<p>'.JHtml::image( $team2logo,"","width=".$params->get('picture_width')).'</p>';
}
else
{
$textdiv .= '<p>'.JHtml::image( $team2logo,"").'</p>';    
}


}

$textdiv .= '<p>'.modSportsmanagementPlaygroundplanHelper::getTeams($match->team2,$teamformat).'</p>';
$textdiv.= '</div>';
$textdiv.= '</div>';
$textdiv.= '<div style="clear:both"></div>';
$textdiv.= '</div>';

}

 echo $textdiv;

echo '</div>';