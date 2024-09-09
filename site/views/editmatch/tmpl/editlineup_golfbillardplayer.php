<?php

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

//echo '<pre>'.print_r($this->lists['team_players_billard'],true).'</pre>';

for ($a=1; $a < 6;$a++)
{
?>
<div class="row">
 <div class="col-sm">
   <?php echo Text::_('COM_SPORTSMANAGEMENT_GOLF_BILLARD_P_PLAYER').' '.$a; ?>
      
    </div>
    <div class="col-sm">
      One of three columns
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
      One of three columns
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
      One of three columns
    </div>
  </div>


<?php
}


?>
