<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       editeventsbb_home.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage match
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
?>
        <fieldset class="adminform">
            <legend>
                <?php
                echo Text::_($this->teams->team1);
                ?>
            </legend>
    <table class='adminlist'>
        <thead>
            <tr>
                <th style="text-align: left; width: 10px;"></th>
                <th style="text-align:left;"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EEBB_PERSON'); ?></th>
                <?php
                foreach ( $this->events as $ev)
                {
        ?>
                    <th style="text-align: center;">
        <?php
        if (File::exists(JPATH_SITE.DIRECTORY_SEPARATOR.$ev->icon) ) {
            $imageTitle = Text::sprintf('%1$s', Text::_($ev->text));
            $iconFileName = $ev->icon;
            echo HTMLHelper::_('image', $iconFileName, $imageTitle, 'title= "' . $imageTitle . '"');
        } else {
            echo Text::_($ev->text);
        }
        ?>
                    </th>
        <?php
                }
                ?>
            </tr>
        </thead>
        <tbody>
    <?php
    $model = $this->getModel();
    $tehp = 0;
    for( $i=0 , $n = count($this->homeRoster); $i < $n; $i++ )
    {
        $row =& $this->homeRoster[$i];
        if($row->value == 0) { continue;
        }
        ?>
       <tr id="row<?php echo $i;?>">
        <td style="text-align: left;">
        <input type="hidden" name="player_id_h_<?php echo $i;?>" value="<?php echo $row->value;?>" />
        <input type="hidden" name="team_id_h_<?php echo $i;?>" value="<?php echo $row->projectteam_id;?>" />
        <input type="checkbox" id="cb_h<?php echo $i;?>" name="cid_h<?php echo $i;?>" value="cb_h" onclick="isChecked(this.checked);"/>
        </td>
        <td style="text-align: left;">
        <?php echo '('.Text::_($row->positionname).') - '.sportsmanagementHelper::formatName(null, $row->firstname, $row->nickname, $row->lastname, 14) ?>
        </td>
        <?php
        //total events home player
        $tehp = 0;
        foreach ( $this->events as $ev)
        {
            $tehp++;  
            $this->evbb = $model->getPlayerEventsbb($row->value, $ev->value, $this->item->id);  
            ?>
          <td style="text-align: center;">
          <input type="hidden" name="event_type_id_h_<?php echo $i.'_'.$tehp;?>" value="<?php echo $ev->value;?>" />
                          
                            <input type="hidden" name="event_id_h_<?php echo $i.'_'.$tehp;?>" value="<?php echo $this->evbb[0]->id;?>" />
                          
                            <input type="text" size="1" class="inputbox" name="event_sum_h_<?php echo $i.'_'.$tehp; ?>" value="<?php echo (($this->evbb[0]->event_sum > 0) ? $this->evbb[0]->event_sum : '' ); ?>" title="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_VALUE_SUM')?>" onchange="document.getElementById('cb_h<?php echo $i;?>').checked=true" />

                            <input type="text" size="2" class="inputbox" name="event_time_h_<?php echo $i.'_'.$tehp; ?>" value="<?php echo (($this->evbb[0]->event_time > 0) ? $this->evbb[0]->event_time : '' ); ?>" title="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_TIME')?>" onchange="document.getElementById('cb_h<?php echo $i;?>').checked=true" />

                            <input type="text" size="2" class="inputbox" name="notice_h_<?php echo $i.'_'.$tehp; ?>" value="<?php echo ((strlen($this->evbb[0]->notice) > 0) ? $this->evbb[0]->notice : '' ); ?>" title="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_MATCH_NOTICE')?>" onchange="document.getElementById('cb_h<?php echo $i;?>').checked=true" />
                            &nbsp;&nbsp;
                            </td>

            <?php
        }
        ?>
       </tr>
        <?php
    }
    ?>
            <input type="hidden" name="total_h_players" value="<?php echo $i;?>" />
            <input type="hidden" name="tehp" value="<?php echo $tehp;?>" />
        </tbody>
    </table>
        </fieldset>
