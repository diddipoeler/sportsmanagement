<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage match
 * @file       editreferees.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

// welche joomla version ?
if(version_compare(JVERSION, '3.0.0', 'ge')) {
    HTMLHelper::_('jquery.framework');
}


$params = $this->form->getFieldsets('params');

?>
<?php
//save and close
$close = Factory::getApplication()->input->getInt('close', 0);
if($close == 1) {
    ?><script>
    window.addEvent('domready', function() {
        $('cancel').onclick();  
    });
    </script>
    <?php
}
?>
<div id="lineup">
    <form  action="<?php echo Route::_('index.php?option=com_sportsmanagement');?>" id='component-form' method='post' style='display:inline' name='adminform' >
    <fieldset>
        <div class="fltrt">
            <button type="button" onclick="jQuery('select.position-starters option').prop('selected', 'selected');Joomla.submitform('matches.saveReferees', this.form);">
                <?php echo Text::_('JAPPLY');?></button>
            <button type="button" onclick="$('close').value=1; jQuery('select.position-starters option').prop('selected', 'selected'); Joomla.submitform('matches.saveReferees', this.form);">
                <?php echo Text::_('JSAVE');?></button>
            <button id="cancel" type="button" onclick="<?php echo Factory::getApplication()->input->getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
                <?php echo Text::_('JCANCEL');?></button>
        </div>
        <div class="configuration" >
    <?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ER_TITLE'); ?>
        </div>
    </fieldset>
    <div class="clear"></div>
        <fieldset class="adminform">
            <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ER_DESCR'); ?></legend>
            <table class="table">
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
                        <table class="table">
        <?php
        foreach ($this->positions AS $key => $pos)
        {
            ?>
          <tr>
           <td style='text-align:center; vertical-align:middle; '>
            <!-- left / right buttons -->
            <br />
                                      
                                      
                                        <input id="moveright" type="button" value="<?php echo Text::_('JGLOBAL_RIGHT'); ?>" onclick="move_list_items('roster','position<?php echo $key;?>');" />
                                        <input id="moveleft" type="button" value="<?php echo Text::_('JGLOBAL_LEFT'); ?>" onclick="move_list_items('position<?php echo $key;?>','roster');" />
                                      
          </td>
          <td>
           <!-- player affected to this position -->
           <b><?php echo Text::_($pos->text); ?></b><br />
            <?php echo $this->lists['team_referees'.$key];?>
          </td>
          <td style='text-align:center; vertical-align:middle; '>
           <!-- up/down buttons -->
           <br />
           <input    type="button" id="moveup-<?php echo $key;?>" class="inputbox move-up"
                                                value="<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_UP'); ?>" /><br />
           <input    type="button" id="movedown-<?php echo $key;?>" class="inputbox move-down"
                                                value="<?php echo Text::_('JGLOBAL_DOWN'); ?>" />
          </td>
         </tr>
            <?php
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
        <input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
        <input type="hidden" name="project_id" value="<?php echo $this->project_id; ?>" />
        <input type="hidden" name="changes_check" value="0" id="changes_check" />
      
        <input type="hidden" name="positionscount" value="<?php echo count($this->positions); ?>" id="positioncount" />
        <input type="hidden" name="component" value="com_sportsmanagement" />
    <?php echo HTMLHelper::_('form.token')."\n"; ?>
    </form>
</div>
