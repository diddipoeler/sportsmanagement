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
JHtml::_('behavior.tooltip');

//echo '<pre>',print_r($this->project,true),'</pre><br>';
//echo 'matchreport<pre>',print_r($this->matchsingle,true),'</pre><br>';
//echo 'match<pre>',print_r($this->match,true),'</pre><br>';

$complete_results = array();
$complete_results['COM_SPORTSMANAGEMENT_SINGLE']['SETS1'] = 0;
$complete_results['COM_SPORTSMANAGEMENT_SINGLE']['SETS2'] = 0;
$complete_results['COM_SPORTSMANAGEMENT_SINGLE']['GAMES1'] = 0;
$complete_results['COM_SPORTSMANAGEMENT_SINGLE']['GAMES2'] = 0;
$complete_results['COM_SPORTSMANAGEMENT_SINGLE']['POINTS1'] = 0;
$complete_results['COM_SPORTSMANAGEMENT_SINGLE']['POINTS2'] = 0;                        

$complete_results['COM_SPORTSMANAGEMENT_DOUBLE']['SETS1'] = 0;
$complete_results['COM_SPORTSMANAGEMENT_DOUBLE']['SETS2'] = 0;
$complete_results['COM_SPORTSMANAGEMENT_DOUBLE']['GAMES1'] = 0;
$complete_results['COM_SPORTSMANAGEMENT_DOUBLE']['GAMES2'] = 0;
$complete_results['COM_SPORTSMANAGEMENT_DOUBLE']['POINTS1'] = 0;
$complete_results['COM_SPORTSMANAGEMENT_DOUBLE']['POINTS2'] = 0;  

$tie_break_set = 0;
if ( $this->project->use_tie_break )
{
    $tie_break_set = $this->project->game_parts - 1;
}

?>

<div id="verein_display">
		<h3><?php echo JText::_('COM_SPORTSMANAGEMENT_SINGLE'); ?></h3>
		<table class="matchreport">
			<tr style="">
				<th>Nr</th>
				<th>Pos</th>
				<th>Heim</th>
				<th>Pos</th>
				<th>Gast </th>
                <?PHP
                for ($gp=1; $gp <= $this->project->game_parts; $gp++)
                {
                ?>
                <th><?php echo $gp; ?></th>
                <?PHP    
                }
                ?>
				
                
				<th>gsp.</th>
			</tr>
            
            <?PHP    
            foreach( $this->matchsingle as $single)
            {
                if ( $single->match_type == 'SINGLE' )
                {
                echo '<tr>';
                echo '<td>';
                echo $single->match_number;
                echo '</td>';
                
                if ( $single->teamplayer1_id )
                {
                $playerinfo = sportsmanagementModelPlayer::getTeamPlayer($this->project->id,0,$single->teamplayer1_id); 
                //echo '<pre>',print_r($playerinfo,true),'</pre><br>'; 
                foreach( $playerinfo as $player)
                {  
                echo '<td>';
                echo JText::_($player->position_name);
                echo '</td>';  
                echo '<td>';
                echo $player->firstname.', '.$player->lastname;
                echo '</td>';
                }
                  
                }
                else
                {
                echo '<td>';
                echo '</td>';
                
                echo '<td>';
                echo '</td>';    
                }  
                
                if ( $single->teamplayer2_id )
                {
                $playerinfo = sportsmanagementModelPlayer::getTeamPlayer($this->project->id,0,$single->teamplayer2_id); 
                //echo '<pre>',print_r($playerinfo,true),'</pre><br>'; 
                foreach( $playerinfo as $player)
                {  
                echo '<td>';
                echo JText::_($player->position_name);
                echo '</td>';  
                echo '<td>';
                echo $player->firstname.', '.$player->lastname;
                echo '</td>';
                }
                  
                }
                else
                {
                echo '<td>';
                echo '</td>';
                
                echo '<td>';
                echo '</td>';    
                }
                
                
                $result_split1	= explode(";",$single->team1_result_split);
                $result_split2	= explode(";",$single->team2_result_split);
                
                //echo '<pre>',print_r($result_split1,true),'</pre><br>';
                
                for ($gp=0; $gp < $this->project->game_parts; $gp++)
                {
                echo '<td>';
                echo $result_split1[$gp].':'.$result_split2[$gp];
                echo '</td>';
                
                if ( is_numeric($result_split1[$gp]) )
                {
                    if ( $result_split1[$gp] > $result_split2[$gp] )
                    {
                        $complete_results['COM_SPORTSMANAGEMENT_SINGLE']['SETS1'] += 1;
                    }
                    else
                    {
                        $complete_results['COM_SPORTSMANAGEMENT_SINGLE']['SETS2'] += 1;
                    }
                    
                    if ( empty($tie_break_set) )
                    {
                        $complete_results['COM_SPORTSMANAGEMENT_SINGLE']['GAMES1'] += $result_split1[$gp];
                        $complete_results['COM_SPORTSMANAGEMENT_SINGLE']['GAMES2'] += $result_split2[$gp];
                    }
                    else
                    {
                        if ( $tie_break_set != $gp )
                        {
                            $complete_results['COM_SPORTSMANAGEMENT_SINGLE']['GAMES1'] += $result_split1[$gp];
                            $complete_results['COM_SPORTSMANAGEMENT_SINGLE']['GAMES2'] += $result_split2[$gp];
                        }
                        else
                        {
                            if ( $result_split1[$gp] > $result_split2[$gp] )
                            {
                                $complete_results['COM_SPORTSMANAGEMENT_SINGLE']['GAMES1'] += 1;
                            }
                            else
                            {
                                $complete_results['COM_SPORTSMANAGEMENT_SINGLE']['GAMES2'] += 1;
                            }
                        }
                        
                    }
                
                }
                      
                }
                
                if ( $single->team1_result > $single->team2_result )
                    {
                        $complete_results['COM_SPORTSMANAGEMENT_SINGLE']['POINTS1'] += 1;
                    }
                    else
                    {
                        $complete_results['COM_SPORTSMANAGEMENT_SINGLE']['POINTS2'] += 1;
                    }
                    
                echo '</tr>'; 
                
                 
                }
            }
            
                
            ?>
            
            
            
</table>


<h3><?php echo JText::_('COM_SPORTSMANAGEMENT_DOUBLE'); ?></h3>
		<table class="matchreport">
			<tr style="">
				<th>Nr</th>
				<th>Pos</th>
				<th>Heim</th>
				<th>Pos</th>
				<th>Gast </th>
                <?PHP
                for ($gp=1; $gp <= $this->project->game_parts; $gp++)
                {
                ?>
                <th><?php echo $gp; ?></th>
                <?PHP    
                }
                ?>
				
                
				<th>gsp.</th>
			</tr>
            
            <?PHP    
            foreach( $this->matchsingle as $single)
            {
                if ( $single->match_type == 'DOUBLE' )
                {
                echo '<tr>';
                echo '<td>';
                echo $single->match_number;
                echo '</td>';
                  
                if ( $single->double_team1_player1 )
                {
                $playerinfo1 = sportsmanagementModelPlayer::getTeamPlayer($this->project->id,0,$single->double_team1_player1);
                $playerinfo2 = sportsmanagementModelPlayer::getTeamPlayer($this->project->id,0,$single->double_team1_player2);  
                //echo '<pre>',print_r($playerinfo,true),'</pre><br>'; 
                foreach( $playerinfo1 as $player)
                {  
                $matchposition =  JText::_($player->position_name).'<br>';  
                $matchplayer = $player->firstname.', '.$player->lastname.' / <br>'; 
                }
                foreach( $playerinfo2 as $player)
                {  
                $matchposition .=  JText::_($player->position_name);  
                $matchplayer .= $player->firstname.', '.$player->lastname; 
                }
                
                echo '<td>';
                echo $matchposition;
                echo '</td>';  
                echo '<td>';
                echo $matchplayer;
                echo '</td>';
                  
                }
                else
                {
                echo '<td>';
                echo '</td>';
                
                echo '<td>';
                echo '</td>';    
                }  
                
                if ( $single->double_team2_player1 )
                {
                $playerinfo1 = sportsmanagementModelPlayer::getTeamPlayer($this->project->id,0,$single->double_team2_player1);
                $playerinfo2 = sportsmanagementModelPlayer::getTeamPlayer($this->project->id,0,$single->double_team2_player2);  
                //echo '<pre>',print_r($playerinfo,true),'</pre><br>'; 
                foreach( $playerinfo1 as $player)
                {  
                $matchposition =  JText::_($player->position_name).'<br>';  
                $matchplayer = $player->firstname.', '.$player->lastname.' / <br>'; 
                }
                foreach( $playerinfo2 as $player)
                {  
                $matchposition .=  JText::_($player->position_name);  
                $matchplayer .= $player->firstname.', '.$player->lastname; 
                }
                
                echo '<td>';
                echo $matchposition;
                echo '</td>';  
                echo '<td>';
                echo $matchplayer;
                echo '</td>';
                  
                }
                else
                {
                echo '<td>';
                echo '</td>';
                
                echo '<td>';
                echo '</td>';    
                }
                
                $result_split1	= explode(";",$single->team1_result_split);
                $result_split2	= explode(";",$single->team2_result_split);
                //echo '<pre>',print_r($result_split1,true),'</pre><br>';
                for ($gp=0; $gp < $this->project->game_parts; $gp++)
                {
                echo '<td>';
                echo $result_split1[$gp].':'.$result_split2[$gp];
                echo '</td>'; 
                
                if ( is_numeric($result_split1[$gp]) )
                {
                    if ( $result_split1[$gp] > $result_split2[$gp] )
                    {
                        $complete_results['COM_SPORTSMANAGEMENT_DOUBLE']['SETS1'] += 1;
                    }
                    else
                    {
                        $complete_results['COM_SPORTSMANAGEMENT_DOUBLE']['SETS2'] += 1;
                    }
                    
                    if ( empty($tie_break_set) )
                    {
                        $complete_results['COM_SPORTSMANAGEMENT_DOUBLE']['GAMES1'] += $result_split1[$gp];
                        $complete_results['COM_SPORTSMANAGEMENT_DOUBLE']['GAMES2'] += $result_split2[$gp];
                    }
                    else
                    {
                        if ( $tie_break_set != $gp )
                        {
                            $complete_results['COM_SPORTSMANAGEMENT_DOUBLE']['GAMES1'] += $result_split1[$gp];
                            $complete_results['COM_SPORTSMANAGEMENT_DOUBLE']['GAMES2'] += $result_split2[$gp];
                        }
                        else
                        {
                            if ( $result_split1[$gp] > $result_split2[$gp] )
                            {
                                $complete_results['COM_SPORTSMANAGEMENT_DOUBLE']['GAMES1'] += 1;
                            }
                            else
                            {
                                $complete_results['COM_SPORTSMANAGEMENT_DOUBLE']['GAMES2'] += 1;
                            }
                        }
                        
                    }
                
                }
                
                   
                }
                
                if ( $single->team1_result > $single->team2_result )
                    {
                        $complete_results['COM_SPORTSMANAGEMENT_DOUBLE']['POINTS1'] += 1;
                    }
                    else
                    {
                        $complete_results['COM_SPORTSMANAGEMENT_DOUBLE']['POINTS2'] += 1;
                    }
                
                
                echo '</tr>';    
                }
            }
            
                
            ?>
            
</table>

<h3><?php echo JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_TENNIS_RESULTS'); ?></h3>
<table class="matchreport">
<tr style="">
				<th></th>
				<th><?php echo JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_TENNIS_RESULTS_POINTS'); ?></th>
				<th><?php echo JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_TENNIS_RESULTS_SETS'); ?></th>
				<th><?php echo JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_TENNIS_RESULTS_GAMES'); ?></th>
				
			</tr>
            
<?PHP
foreach ( $complete_results as $key => $value )
{
echo '<tr>'; 
echo '<td>';
echo JText::_($key);
echo '</td>';
echo '<td>';
echo $value['POINTS1'].':'.$value['POINTS2'];
echo '</td>'; 
echo '<td>';
echo $value['SETS1'].':'.$value['SETS2'];
echo '</td>';
echo '<td>';
echo $value['GAMES1'].':'.$value['GAMES2'];
echo '</td>';  
echo '</tr>';     
}


?>
            
</table>
<?PHP

//echo 'results<pre>',print_r($complete_results,true),'</pre><br>';

?>

</div>
