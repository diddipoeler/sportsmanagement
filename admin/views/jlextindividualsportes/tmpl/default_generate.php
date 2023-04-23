<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jlextindividualsportes
 * @file       default_generate.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * 
 * No, they do not have a position for the season because each matchday the team captain decide the position of the players (at the match beginning). 
 * Day1 a player may be at A position (A, B, C and D are home positions and W X Y and Z are away positions) and Day 2 B or C or D ...
 
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
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;


if ( Factory::getConfig()->get('debug') )
{  
echo 'homeplayers<pre>'.print_r($this->homeplayers,true).'</pre>';
echo 'awayplayers<pre>'.print_r($this->awayplayers,true).'</pre>';
echo 'show_matches<pre>'.print_r($this->show_matches,true).'</pre>';
}

?>
<div class="table-responsive" id="editcell">
<!-- Start games list -->
<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id='adminForm'>
<fieldset>
                <div class="fltlft">
                    <button type="button" onclick="Joomla.submitform('jlextindividualsportes.generatematchsingles', this.form);">
						<?php echo Text::_('JAPPLY'); ?></button>
                    

                    

                </div>

            </fieldset>
            
<table class="table">
<?php
foreach ( $this->show_matches as $count_i => $item )
{
?>       
<tr>     
<td>
<?php
echo $item->teamplayer1_position;
?>
</td>
<td>
<?php
echo $item->teamplayer2_position;
?>
</td>




<?php
foreach ( $this->homeplayers as $count_home => $home )
{
   
if ( $home->season_team_person_id == $item->teamplayer1_id )
{
?>
<td>
<input type="hidden" name="teamplayer1_id[]" value="<?php echo $home->season_team_person_id; ?>" />

<?php
echo $home->lastname;
?>
</td>
<td>
<?php
echo $home->firstname;
?>
</td>
<?php    
}       
       
}

foreach ( $this->awayplayers as $count_away => $away )
{
if ( $away->season_team_person_id == $item->teamplayer2_id )
{
?>
<td>
<input type="hidden" name="teamplayer2_id[]" value="<?php echo $away->season_team_person_id; ?>" />
<?php
echo $away->lastname;
?>
</td>
<td>
<?php
echo $away->firstname;
?>
</td>
<?php    
}       
       
       
}	   
?>       
</tr>  
<?php       
}
?>
</table>
<input type='hidden' name='project_id' value='<?php echo $this->pid; ?>'/>
<input type='hidden' name='match_id' value='<?php echo $this->id; ?>'/>
<input type='hidden' name='projectteam1_id' value='<?php echo $this->projectteam1_id; ?>'/>
<input type='hidden' name='projectteam2_id' value='<?php echo $this->projectteam2_id; ?>'/>
<input type='hidden' name='round_id' value='<?php echo $this->rid; ?>'/>

<input type='hidden' name='task' value='' id='task'/>
<?php echo HTMLHelper::_('form.token') . "\n"; ?>
</form>
</div>