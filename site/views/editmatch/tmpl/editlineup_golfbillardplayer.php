<?php
/**
 * https://getbootstrap.com/docs/5.2/helpers/color-background/
 * 
 */
 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

//echo '<pre>'.print_r($this->lists['team_players_billard'],true).'</pre>';
//echo '<pre>'.print_r($this->lists['team_players_billard_assign'],true).'</pre>';

$not_assigned_options[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PLAYER'));
foreach ((array) $this->lists['team_players_billard'] AS $p)
		{
			$not_assigned_options[] = HTMLHelper::_(
				'select.option', $p->value, 
				sportsmanagementHelper::formatName(null, $p->firstname, $p->nickname, $p->lastname, $default_name_format).' ('.$p->knvbnr.')' 
			);
		}
//echo '<pre>'.print_r($not_assigned_options,true).'</pre>';

for ($a=1; $a < 6;$a++)
{
?>
<div class="row">
 <div class="col-md-1 box">
   <?php echo Text::_('COM_SPORTSMANAGEMENT_GOLF_BILLARD_P_PLAYER').' '.$a; ?>
      
    </div>
    <div class="col-sm">
    <?php
    $player_id = 0;
    $player_name = '';
    foreach ( $this->lists['team_players_billard_assign'] as $key => $value ) if ( $value->trikot_number == $a )
    {
    $player_id = $value->tpid;    
    $player_name = sportsmanagementHelper::formatName(null, $value->firstname, $value->nickname, $value->lastname, $default_name_format).' ('.$value->knvbnr.')';
    }
    if ( $player_id )
    {
    echo $player_name;    
    }
    else
    {
    echo HTMLHelper::_('select.genericlist', $not_assigned_options, 'roster['.$a.']', '', 'value', 'text',$player_id);
    }
    
    ?>
      
    </div>
  </div>


<?php
}
?>
<div class="row">
  <div class="text-bg-secondary p-3">
      
    </div>
</div>
  <?php
for ($a=1; $a < 2;$a++)
{
?>
<div class="row">
 <div class="col-md-1 box">
   <?php echo Text::_('COM_SPORTSMANAGEMENT_GOLF_BILLARD_P_CAPTAIN'); ?>
      
    </div>
    <div class="col-sm">
      <?php
      $player_id = 0;
      $player_name = '';
    foreach ( $this->lists['team_players_billard_assign'] as $key => $value ) if ( $value->trikot_number == 100 )
    {
    $player_id = $value->tpid;    
    $player_name = sportsmanagementHelper::formatName(null, $value->firstname, $value->nickname, $value->lastname, $default_name_format).' ('.$value->knvbnr.')';
    }
    if ( $player_id )
    {
    echo $player_name;    
    }
    else
    {
    echo HTMLHelper::_('select.genericlist', $not_assigned_options, 'rosterc[]', '', 'value', 'text',$player_id);
    }
    ?>
    </div>
  </div>


<?php
}
?>
<div class="row">
  <div class="text-bg-secondary p-3">
      
    </div>
</div>
  <?php


for ($a=1; $a < 2;$a++)
{
?>
<div class="row">
 <div class="col-md-1 box">
   <?php echo Text::_('COM_SPORTSMANAGEMENT_GOLF_BILLARD_P_RESERVE'); ?>
      
    </div>
    <div class="col-sm">
        <?php
        $player_id = 0;
        $player_name = '';
    foreach ( $this->lists['team_players_billard_assign'] as $key => $value ) if ( $value->trikot_number == 50 )
    {
    $player_id = $value->tpid;    
    $player_name = sportsmanagementHelper::formatName(null, $value->firstname, $value->nickname, $value->lastname, $default_name_format).' ('.$value->knvbnr.')';
    }
    if ( $player_id )
    {
    echo $player_name;    
    }
    else
    {
    echo HTMLHelper::_('select.genericlist', $not_assigned_options, 'rosterr[]', '', 'value', 'text',$player_id);
    }
    ?>
    </div>
  </div>


<?php
}


?>
