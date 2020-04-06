<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       editstats.php
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
HTMLHelper::_('behavior.framework');
$params = $this->form->getFieldsets('params');

?>

<form name="adminForm" id="adminForm" method="post" action="<?php echo $this->uri->toString(); ?>">
<div id="jlstatsform">
<fieldset>
<div class="fltrt">
<button type="button" onclick="Joomla.submitform('editmatch.savestats', this.form);">
<?php echo Text::_('JSAVE');?></button>        
<button type="button" onclick="Joomla.submitform('editmatch.cancel', this.form);">
<?php echo Text::_('JCANCEL');?></button>        

</div>
        
<div class="configuration" >

</div>
</fieldset>
<div class="clear"></div>
<?php
echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'home'));  
echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'home', Text::_($this->teams->team1, true));
echo $this->loadTemplate('home');
echo HTMLHelper::_('bootstrap.endTab');        
echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'away', Text::_($this->teams->team2, true));
echo $this->loadTemplate('away');
echo HTMLHelper::_('bootstrap.endTab');        
echo HTMLHelper::_('bootstrap.endTabSet');        
       
?>
<!-- <input type='hidden' name='option' value='com_sportsmanagement' /> -->
<input type="hidden" name="view" value="" />
<input type="hidden" name="close" id="close" value="0" />
<input type="hidden" name="task" id="" value="" />
<input type="hidden" name="p" value="<?php echo sportsmanagementModelEditMatch::$projectid; ?>" />
<input type="hidden" name="r" value="<?php echo sportsmanagementModelEditMatch::$roundid; ?>" />
<input type="hidden" name="s" value="<?php echo sportsmanagementModelEditMatch::$seasonid; ?>" />
<input type="hidden" name="id" value="<?php echo $this->match->id; ?>" />
<input type="hidden" name="match_id" value="<?php echo $this->match->id; ?>" />
<input type="hidden" name="boxchecked" value="0" />
<?php echo HTMLHelper::_('form.token'); ?>
</div>
</form>
<div style="clear: both"></div>
