<?php 
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage rounds
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;


$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<script language="javascript">
window.addEvent('domready',function(){
    $$('table.adminlist tr').each(function(el){
        var cb;
        if (cb=el.getElement("input[name^=cid]")) {
            el.getElement("input[name^=roundcode]").addEvent('change',function(){
                if (isNaN(this.value)) {
                    alert(Joomla.JText._('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_CSJS_MSG_NOTANUMBER'));
                    return false;
                }
            });
        }
    });
});
</script>
<div id='alt_massadd_enter' style='display:<?php echo ($this->massadd == 0) ? 'none' : 'block'; ?>'>
    <fieldset class='adminform'>
        <legend><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_MASSADD_LEGEND', '<i>'.$this->project->name.'</i>'); ?></legend>
        <form id='copyform' method='post' style='display:inline' id='copyform'>
            <input type='hidden' name='project_id' value='<?php echo $this->project->id; ?>' />
            <input type='hidden' name='task' value='round.copyfrom' />
    <?php echo HTMLHelper::_('form.token')."\n"; ?>
            <table class='admintable'><tbody><tr>
                <td class='key' nowrap='nowrap'><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_MASSADD_COUNT'); ?></td>
                <td><input type='text' name='add_round_count' id='add_round_count' value='0' size='3' class='inputbox' /></td>
                <td><input type='submit' class='button' value='<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_MASSADD_SUBMIT_BUTTON'); ?>' onclick='this.form.submit();' /></td>
            </tr></tbody></table>
        </form>
    </fieldset>
</div>

<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
<?PHP
if (!$this->massadd) {
    echo $this->loadTemplate('joomla_version');
}
?>
<input type="hidden" name="pid" value="<?php echo $this->project->id; ?>" />
<input type="hidden" name="next_roundcode" value="<?php echo count($this->matchday) + 1; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>" />
<?php echo HTMLHelper::_('form.token')."\n"; ?>
<?php echo $this->table_data_div; ?>
</form>
<div>
<?php
echo $this->loadTemplate('footer');
?>
</div>
