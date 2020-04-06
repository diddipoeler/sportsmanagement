<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       editlineup.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage editmatch
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
 
$params = $this->form->getFieldsets('params');
?>
<script type="text/javascript">

</script>

<?php

?>
<form name="editmatch" id="editmatch" method="post" action="<?php echo $this->uri->toString(); ?>">
<fieldset>
<div class="fltrt">
<button type="button" onclick="jQuery('select.position-starters option').prop('selected', 'selected');jQuery('select.position-staff option').prop('selected', 'selected');Joomla.submitform('editmatch.saveroster', this.form);">
<?php echo Text::_('JSAVE');?></button>
<button type="button" onclick="Joomla.submitform('editmatch.cancel', this.form);">
<?php echo Text::_('JCANCEL');?></button>
</div>
<div class="configuration" >
<?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELU_TITLE', $this->teamname); ?>
</div>
</fieldset>
<div class="clear"></div>
<div id="lineup">
<?php
echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'player')); 
echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'player', Text::_('COM_SPORTSMANAGEMENT_TABS_PLAYERS', true));
echo $this->loadTemplate('players');
echo HTMLHelper::_('bootstrap.endTab');            
echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'substitutions', Text::_('COM_SPORTSMANAGEMENT_TABS_SUBST', true));
echo $this->loadTemplate('substitutions');
echo HTMLHelper::_('bootstrap.endTab');        
echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'staff', Text::_('COM_SPORTSMANAGEMENT_TABS_STAFF', true));
echo $this->loadTemplate('staff');
echo HTMLHelper::_('bootstrap.endTab');        
echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'players_trikot_numbers', Text::_('COM_SPORTSMANAGEMENT_TABS_PLAYER_TRIKOT_NUMBERS', true));
echo $this->loadTemplate('players_trikot_numbers');
echo HTMLHelper::_('bootstrap.endTab');    
echo HTMLHelper::_('bootstrap.endTabSet');        
?>    
<input type="hidden" name="task" value="" />
<input type="hidden" name="view" value="" />
<input type="hidden" name="project_id" value="<?php echo $this->project_id; ?>" />
<input type="hidden" name="p" value="<?php echo sportsmanagementModelEditMatch::$projectid; ?>" />
<input type="hidden" name="r" value="<?php echo sportsmanagementModelEditMatch::$roundid; ?>" />
<input type="hidden" name="s" value="<?php echo sportsmanagementModelEditMatch::$seasonid; ?>" />
<input type="hidden" name="division" value="<?php echo sportsmanagementModelEditMatch::$divisionid; ?>" />
<input type="hidden" name="cfg_which_database" value="<?php echo sportsmanagementModelEditMatch::$cfg_which_database; ?>" />
<input type="hidden" name="close" id="close" value="0" />
<input type="hidden" name="id" value="<?php echo $this->match->id; ?>" />
<input type="hidden" name="changes_check" value="0" id="changes_check" />
<input type="hidden" name="team" value="<?php echo $this->tid; ?>" id="team" />
<input type="hidden" name="positionscount" value="<?php echo count($this->positions); ?>" id="positioncount"    />
<?php echo HTMLHelper::_('form.token'); ?>       

        
    </div>
</form>
