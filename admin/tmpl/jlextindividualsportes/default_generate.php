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
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

if ($this->debug)
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
    
$match_type = ( $item->teamplayer1_position == 'Double' || $item->teamplayer2_position  == 'Double' ) ? 'DOUBLE' : 'SINGLE';     
    
?>       
<tr>     
<td>

<input type="hidden" name="match_type[]" value="<?php echo $match_type; ?>" />


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
