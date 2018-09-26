<?PHP
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      readpressebericht.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage match
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

if ( $this->matchnumber )
{

//echo ' csv <pre>',print_r($this->csv, true),'</pre>';
//echo ' csvplayers <pre>',print_r($this->csvplayers, true),'</pre>';
$lfdnummer = 0;
?>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
<table id="csvplayers" class="" width="" border="" cellspacing="" cellpadding="" bgcolor="">
<tr>	
<th class="">Spielnummer</th>	
<th class="">Vorname</th>
<th class="">Nachname</th>
<th class="">In der Datenbank ?</th>
<th class="">Projektteam zugeordnet ?</th>
<th class="">Projektposition</th>
<th class="">Startaufstellung</th>	
</tr>	
	
	<?php foreach ($this->csvplayers as $value): ?>
	<tr>
        <td><?php echo $value->nummer; ?>
        <input type='hidden' name='player[<?php echo $lfdnummer; ?>]' value='<?php echo $value->nummer; ?>' />
        <input type='hidden' name='playerpersonid[<?php echo $lfdnummer; ?>]' value='<?php echo $value->person_id; ?>' />
        <input type='hidden' name='playerprojectpersonid[<?php echo $lfdnummer; ?>]' value='<?php echo $value->project_person_id; ?>' />
        <input type='hidden' name='playerprojectpositionid[<?php echo $lfdnummer; ?>]' value='<?php echo $value->project_position_id; ?>' />
        </td>
        <td><?php echo $value->firstname; ?>
        <input type='hidden' name='playerfirstname[<?php echo $lfdnummer; ?>]' value='<?php echo $value->firstname; ?>' />
        </td>
        <td><?php echo $value->lastname; ?>
        <input type='hidden' name='playerlastname[<?php echo $lfdnummer; ?>]' value='<?php echo $value->lastname; ?>' />
        </td>
        <?PHP
        if ( $value->person_id )
        {
            echo '<td>'.HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/ok.png','','title= "'.''.'"').'</td>';
        }
        else
        {
            echo '<td>'.HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/error.png','','title= "'.''.'"').'</td>';
        }
        
        if ( $value->project_person_id )
        {
            echo '<td>'.HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/ok.png','','title= "'.''.'"').'</td>';
        }
        else
        {
            echo '<td>'.HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/error.png','','title= "'.''.'"').'</td>';
        }
        
        ?>
        <td>
        <?php 
        $inputappend = '';
        $append = '';
        if ( $value->project_position_id != 0 )
	{
	//$selectedvalue = $value->project_position_id;
    $selectedvalue = $value->position_id;
	$append = '';
	}
	else
	{
	$selectedvalue = 0;
	//$append = ' style="background-color:#FFFFFF"';
	}
        if ( $value->project_position_id == 0 )
	{
	//$append=' style="background-color:#FFFFFF"';
	}
	echo HTMLHelper::_( 'select.genericlist', $this->lists['project_position_id'], 'project_position_id[' . $lfdnummer.']', $inputappend . 'class="inputbox" size="1" ' . $append, 'value', 'text', $selectedvalue );
        ?>
        </td>
	<td>
        <?php 	
	echo HTMLHelper::_( 'select.genericlist', $this->lists['startaufstellung'], 'startaufstellung[' . $lfdnummer.']', $inputappend . 'class="inputbox" size="1" ' . $append, 'value', 'text', 0 );
	?>
	</td>	
        </tr>
	<?php 
    $lfdnummer++;
    endforeach; 
    ?>
	
</table>

<table id="csvstaff" class="" width="" border="" cellspacing="" cellpadding="" bgcolor="">
<tr>	
<th class="">Staff Position</th>	
<th class="">Vorname</th>
<th class="">Nachname</th>
<th class="">In der Datenbank ?</th>
<th class="">Projektteam zugeordnet ?</th>
<th class="">Projektposition</th>
</tr>	
	
		<?php foreach ($this->csvstaff as $value): ?>
		<tr>
        <td><?php echo $value->position; ?>
        <input type='hidden' name='staffprojectpersonid[<?php echo $lfdnummer; ?>]' value='<?php echo $value->project_person_id; ?>' />
        <input type='hidden' name='staffprojectpositionid[<?php echo $lfdnummer; ?>]' value='<?php echo $value->project_position_id; ?>' />
        </td>
        <td><?php echo $value->firstname; ?>
        <input type='hidden' name='stafffirstname[<?php echo $lfdnummer; ?>]' value='<?php echo $value->firstname; ?>' />
        </td>
        <td><?php echo $value->lastname; ?>
        <input type='hidden' name='stafflastname[<?php echo $lfdnummer; ?>]' value='<?php echo $value->lastname; ?>' />
        </td>
        <?PHP
        if ( $value->person_id )
        {
            echo '<td>'.HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/ok.png','','title= "'.''.'"').'</td>';
        }
        else
        {
            echo '<td>'.HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/error.png','','title= "'.''.'"').'</td>';
        }
        
        if ( $value->project_person_id )
        {
            echo '<td>'.HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/ok.png','','title= "'.''.'"').'</td>';
        }
        else
        {
            echo '<td>'.HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/error.png','','title= "'.''.'"').'</td>';
        }
        
        ?>
        <td>
        <?php
        //$inputappend = ' disabled="disabled"'; 
        $inputappend = '';
        $append = '';
        if ( $value->project_position_id != 0 )
	{
	$selectedvalue = $value->project_position_id;
	$append = '';
	}
	else
	{
	$selectedvalue = 0;
	//$append = ' style="background-color:#FFFFFF"';
	}
        if ( $value->project_position_id == 0 )
	{
	//$append=' style="background-color:#FFFFFF"';
	}
	echo HTMLHelper::_( 'select.genericlist', $this->lists['project_staff_position_id'], 'project_staff_position_id[' . $lfdnummer.']', $inputappend . 'class="inputbox" size="1" ' . $append, 'value', 'text', $selectedvalue );
        ?>
        </td>
        </tr>
	<?php 
    $lfdnummer++;
    endforeach; 
    ?>
	
</table>

<table id="csvinout" class="" width="" border="" cellspacing="" cellpadding="" bgcolor="">
<tr>	
<th class="">Spieler</th>	
<th class="">Minute</th>
<th class="">Rückennummer</th>
<th class="">für Nummer</th>
<th class="">für Spieler</th>
<th class="">Projektposition</th>
</tr>	
	
	
	<?php foreach ($this->csvinout as $value): ?>
	<tr>
        <td><?php echo $value->spieler; ?></td>
        <td><?php echo $value->in_out_time; ?></td>
        <td><?php echo $value->in; ?></td>
        <td><?php echo $value->out; ?></td>
        <td><?php echo $value->spielerout; ?></td>
        <td>
        <?php 
         
        $inputappend = '';
	$selectedvalue = 0;
	//$append = ' style="background-color:#FFFFFF"';
    $append = '';
	echo HTMLHelper::_( 'select.genericlist', $this->lists['inout_position_id'], 'inout_position_id[' . $value->in.']', $inputappend . 'class="inputbox" size="1" ' . $append, 'value', 'text', $selectedvalue );
        ?>
        </td>
        </tr>
	<?php endforeach; ?>
	
	
</table>

<table id="csvcards" class="" width="" border="" cellspacing="" cellpadding="" bgcolor="">
<tr>	
<th class="">Spieler</th>	
<th class="">Minute</th>
<th class="">Karte</th>
<th class="">Rückennummer</th>
<th class="">Grund</th>
<th class="">In der Datenbank ?</th>
<th class="">Event</th>
</tr>	
	
	
	<?php foreach ($this->csvcards as $value): ?>
	<tr>
        <td><?php echo $value->spieler; ?></td>
        <td><?php echo $value->event_time; ?></td>
        <td><?php echo $value->event_name; ?></td>
        <td><?php echo $value->spielernummer; ?></td>
        <td><?php echo $value->notice; ?></td>
        <?PHP
        if ( $value->event_type_id )
        {
            echo '<td>'.HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/ok.png','','title= "'.''.'"').'</td>';
        }
        else
        {
            echo '<td>'.HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/error.png','','title= "'.''.'"').'</td>';
        }
        ?>
                
        <td>
        <?php
        $inputappend = '';
        $append = '';
        if ( $value->event_type_id != 0 )
	{
	$selectedvalue = $value->event_type_id;
	$append = '';
	}
	else
	{
	$selectedvalue = 0;
	//$append = ' style="background-color:#FFFFFF"';
	}
        if ( $value->event_type_id == 0 )
	{
	//$append=' style="background-color:#FFFFFF"';
	}
        echo HTMLHelper::_( 'select.genericlist', $this->lists['events'], 'project_events_id[' . $value->project_person_id.']', $inputappend . 'class="inputbox" size="1" ' . $append, 'value', 'text', $selectedvalue );
        ?>
        </td>
	<?php endforeach; ?>
		
</table>

<?PHP
}

?>
<input type='submit' value='<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_SAVE_PRESSEBERICHT'); ?>' onclick='' />
<input type="hidden" name="layout" value="savepressebericht" />
<input type="hidden" name="view" value="match" />
<input type="hidden" name="tmpl" value="component" />
<input type="hidden" name="match_id" value="<?php echo JFactory::getApplication()->input->getInt('match_id',0); ?>" />

<input type="hidden" name="season_id" value="<?php echo $this->projectws->season_id; ?>" />	
<input type="hidden" name="fav_team" value="<?php echo $this->projectws->fav_team; ?>" />	
<input type="hidden" name="projectteamid" value="<?php echo $this->projectteamid; ?>" />
</form>
