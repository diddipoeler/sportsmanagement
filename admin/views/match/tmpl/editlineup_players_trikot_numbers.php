<?php defined('_JEXEC') or die('Restricted access');

/*
echo 'starters<pre>'.print_r($this->starters,true).'</pre><br>';
echo 'positions<pre>'.print_r($this->positions,true).'</pre><br>';
echo 'substitutions<pre>'.print_r($this->substitutions,true).'</pre><br>';
*/

?>
<fieldset class="adminform">
<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUP_TRIKOT_NUMBER'); ?></legend>
<?php      
foreach ($this->positions AS $position_id => $pos)
		{
		?>
<fieldset class="adminform">
<legend><?php echo $pos->text; ?></legend>
<table>    
    <?PHP
    // get players assigned to this position
    foreach ($this->starters[$position_id] AS $player)
		{
		//echo ''.$player->firstname.'-'.$player->lastname.'-'.$player->jerseynumber.'-'.$player->trikot_number.'<br>';
		?>
		<tr>
		
    <td><?php echo $player->firstname; ?>
    </td>
    
    <td><?php echo $player->lastname; ?>
    </td>
    
    <td><?php echo $player->jerseynumber; ?>
    </td>
    
    <td><input type='' name='trikot_number[<?php echo $player->value;?>]' value="<?php echo $player->trikot_number; ?>" />
    </td>
    	
		</tr>
		<?PHP
    }
		
    ?>
    </table>
    </fieldset>   
    <?PHP	
		}


?>      
</fieldset>      