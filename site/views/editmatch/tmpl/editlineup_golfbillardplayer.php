<?php

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
				sportsmanagementHelper::formatName(null, $p->firstname, $p->nickname, $p->lastname, $default_name_format) 
			);
		}
//echo '<pre>'.print_r($not_assigned_options,true).'</pre>';

for ($a=1; $a < 6;$a++)
{
?>
<div class="row">
 <div class="col-sm">
   <?php echo Text::_('COM_SPORTSMANAGEMENT_GOLF_BILLARD_P_PLAYER').' '.$a; ?>
      
    </div>
    <div class="col-sm">
    <?php
    echo HTMLHelper::_('select.genericlist', $not_assigned_options, 'roster['.$a.']', '', 'value', 'text');
    ?>
      
    </div>
  </div>


<?php
}
?>
<div class="row">
  <div class="col-sm">
      
    </div>
</div>
  <?php
for ($a=1; $a < 2;$a++)
{
?>
<div class="row">
 <div class="col-sm">
   <?php echo Text::_('COM_SPORTSMANAGEMENT_GOLF_BILLARD_P_CAPTAIN'); ?>
      
    </div>
    <div class="col-sm">
      <?php
    echo HTMLHelper::_('select.genericlist', $not_assigned_options, 'rosterc[]', '', 'value', 'text');
    ?>
    </div>
  </div>


<?php
}

for ($a=1; $a < 2;$a++)
{
?>
<div class="row">
 <div class="col-sm">
   <?php echo Text::_('COM_SPORTSMANAGEMENT_GOLF_BILLARD_P_RESERVE'); ?>
      
    </div>
    <div class="col-sm">
        <?php
    echo HTMLHelper::_('select.genericlist', $not_assigned_options, 'rosterr[]', '', 'value', 'text');
    ?>
    </div>
  </div>


<?php
}


?>
