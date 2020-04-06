<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       editlineup_players.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage editmatch
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
?>
        <fieldset class="adminform">
            <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUP_START_LU'); ?></legend>
            <table class='adminlist'>
            <thead>
                <tr>
                    <th>
        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUP_ROSTER'); ?>
                    </th>
                    <th>
        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUP_ASSIGNED'); ?>
                    </th>                  
                </tr>
            </thead>
                <tr>
                    <td colspan="2">
                    <span class="red">
        <?php
        if (isset($this->preFillSuccess) ) {
            if($this->preFillSuccess) {
                echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_PREFILL_DONE') . '<br /><br />';
            }
        }
        ?>
                    </span>
                    </td>
                </tr>              
                <tr>
                    <td style="text-align:center; vertical-align:middle; ">                  
        <?php
        // echo select list of non assigned players from team roster
        if (isset($this->lists['team_players']) ) {
            echo $this->lists['team_players'];
        }
        else
        {
            echo Text::_('JGLOBAL_NO_MATCHING_RESULTS');
        }
        ?>
                    </td>
                    <td style="text-align:center; vertical-align:top; ">
                        <table>
        <?php
        if (isset($this->positions) ) {
            foreach ($this->positions AS $position_id => $pos)
            {
                    ?>
                  <tr>
                   <td style='text-align:center; vertical-align:middle; '>
                    <!-- left / right buttons -->
                    <br />
                                      
                                      
          <input id="moveright" type="button" value="<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_RIGHT'); ?>" onclick="move_list_items('roster','position<?php echo $position_id;?>');" />
          <input id="moveleft" type="button" value="<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_LEFT'); ?>" onclick="move_list_items('position<?php echo $position_id;?>','roster');" />
                                      
              </td>
              <td>
               <!-- player affected to this position -->
               <b><?php echo Text::_($pos->text);?></b><br />
                <?php echo $this->lists['team_players'.$position_id];?>
              </td>
              <td style='text-align:center; vertical-align:middle; '>
               <!-- up/down buttons -->
               <br />
               <input    type="button" onclick="move_up('position<?php echo $position_id;?>');" class="inputbox move-up"
                 value="<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_UP'); ?>" /><br />
               <input    type="button" onclick="move_down('position<?php echo $position_id;?>');" class="inputbox move-down"
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
