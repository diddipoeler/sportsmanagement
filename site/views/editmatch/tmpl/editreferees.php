<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       editreferees.php
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
<div id="lineup">
    <form action="<?php echo $this->uri->toString(); ?>" id='editreferees' method='post' name='editreferees' >
    <fieldset>
        <div class="fltrt">
<button type="button" onclick="jQuery('select.position-starters option').prop('selected', 'selected');Joomla.submitform('editmatch.saveReferees', this.form);">
<?php echo Text::_('JSAVE');?></button>    
<button type="button" onclick="Joomla.submitform('editmatch.cancel', this.form);">
<?php echo Text::_('JCANCEL');?></button>        
        </div>
        <div class="configuration" >
    <?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ER_TITLE'); ?>
        </div>
    </fieldset>
    <div class="clear"></div>
        <fieldset class="adminform">
            <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ER_DESCR'); ?></legend>
            <table class='adminlist'>
            <thead>
                <tr>
                    <th>
        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ER_REFS'); ?>
                    </th>
                    <th>
        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ER_ASSIGNED'); ?>
                    </th>                    
                </tr>
            </thead>            
                <tr>
                    <td style="text-align:center; ">
        <?php
        // echo select list of non assigned players from team roster
        echo $this->lists['team_referees'];
        ?>
                    </td>
                    <td style="text-align:center; vertical-align:top; ">
                        <table>
        <?php
        if (isset($this->positions) ) {
            foreach ($this->positions AS $key => $pos)
            {
                                ?>
                                <tr>
<td style='text-align:center; vertical-align:middle; '>
<!-- left / right buttons -->
<br />
                                        
                                        
<input id="moveright" type="button" value="<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_RIGHT'); ?>" onclick="move_list_items('roster','position<?php echo $key;?>');" />
<input id="moveleft" type="button" value="<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_LEFT'); ?>" onclick="move_list_items('position<?php echo $key;?>','roster');" />
                                        
</td>
<td>
<!-- player affected to this position -->
<b><?php echo Text::_($pos->text); ?></b><br />
<?php echo $this->lists['team_referees'.$key];?>
</td>
<td style='text-align:center; vertical-align:middle; '>
<!-- up/down buttons -->
<br />
<input	type="button" id="moveup-<?php echo $key;?>" class="inputbox move-up"
value="<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_UP'); ?>" /><br />
<input	type="button" id="movedown-<?php echo $key;?>" class="inputbox move-down"
value="<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DOWN'); ?>" />
</td>
                                </tr>
                                <?php
            }
        }
        ?>
                        </table>
                    </td>
                </tr>
            </table>
        </fieldset>
        <br/>
        <br/>
<input type="hidden" name="task" value="" />
<input type="hidden" name="view" value="" />
<input type="hidden" name="close" id="close" value="0" />
<input type="hidden" name="id" value="<?php echo $this->match->id; ?>" />
<input type="hidden" name="project_id" value="<?php echo $this->project_id; ?>" />
<input type="hidden" name="changes_check" value="0" id="changes_check" />
<input type="hidden" name="p" value="<?php echo sportsmanagementModelEditMatch::$projectid; ?>" />
<input type="hidden" name="r" value="<?php echo sportsmanagementModelEditMatch::$roundid; ?>" />
<input type="hidden" name="s" value="<?php echo sportsmanagementModelEditMatch::$seasonid; ?>" />
<input type="hidden" name="division" value="<?php echo sportsmanagementModelEditMatch::$divisionid; ?>" />
<input type="hidden" name="cfg_which_database" value="<?php echo sportsmanagementModelEditMatch::$cfg_which_database; ?>" />
<input type="hidden" name="positionscount" value="<?php echo count($this->positions); ?>" id="positioncount" />
<?php echo HTMLHelper::_('form.token')."\n"; ?>
</form>
</div>
