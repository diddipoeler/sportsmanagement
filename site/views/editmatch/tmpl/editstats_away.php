<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editmatch
 * @file       editstats_away.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
//row index
$i = 0;
$j = 0;
?>

<fieldset class="adminform">
    <legend><?php echo Text::_($this->teams->team2);?></legend>
    <?php foreach ($this->positions as $position): ?>
    <h3><?php echo $position->text; ?></h3>
    <table class='adminlist'>
        <thead>
            <tr>
                <th style="text-align: left; width: 10px;"></th>
                <th style="text-align: left;"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ES_NAME'); ?></th>
                <?php
                foreach ( $this->stats as $stat)
                {
                    ?>
                    <th style="text-align: center;"><?php	
                    //                    echo 'getCalculated -> '.$stat->getCalculated().'<br>';	
                    //                    echo 'position_id -> '.$stat->position_id.'<br>';
                    //                    echo 'value -> '.$position->value;
                    //                    echo 'posid -> '.$position->posid;
                    ?>
                    </th>
        <?php
                  
        if (!$stat->getCalculated() && $stat->position_id == $position->posid) {
            ?>
           <th style="text-align: center;"><?php	echo $stat->getImage();    ?></th>
        <?php
        }
                }
                ?>
            </tr>
        </thead>
        <tbody>
    <?php
    foreach ( $this->awayRoster as $row )
    {
        if ($row->tpid == 0 || $row->position_id == $position->posid ) :
            ?>
           <tr id="row<?php echo $i;?>" class="statrow">
       <td style="text-align: left;">
        <input type="hidden" name="teamplayer_id[]"    value="<?php echo $row->tpid;?>" />
        <input type="hidden" name="projectteam_id[]" value="<?php echo $row->projectteam_id;?>" />
        <input type="checkbox" class="statcheck" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $i;?>" /></td>
       <td style="text-align: left; width:200px;">
        <?php echo sportsmanagementHelper::formatName(null, $row->firstname, $row->nickname, $row->lastname, 0) ?>
       </td>
        <?php	foreach ( $this->stats as $ev): ?>
        <?php if (!$ev->getCalculated() && $ev->position_id == $position->posid) : ?>
                    <td  style="text-align: center;">
        <?php $val = isset($this->playerstats[$row->projectteam_id][$row->tpid][$ev->id]) ? $this->playerstats[$row->projectteam_id][$row->tpid][$ev->id] : ""; ?>
                            <input type="text" size="3"    class="inputbox stat" title="<?php echo $ev->name; ?>" name="stat<?php echo $row->tpid;?>_<?php echo $ev->id; ?>"    value="<?php echo $val; ?>" />
                    </td>
        <?php endif;    ?>
        <?php endforeach; ?>
         </tr>
            <?php
            $i++;
        endif;  
    }
    ?>
        </tbody>
    </table>
    <?php endforeach; ?>
  
    <?php if (count($this->awayStaff)) :?>
    <hr/>
    <?php foreach ($this->staffpositions as $position): ?>
    <h3><?php echo $position->text; ?></h3>
    <table>  
        <thead>
            <tr>
                <th style="text-align: left;"></th>
                <th style="text-align: left; width: 200px;"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ES_NAME'); ?></th>
                <?php
                foreach ( $this->stats as $stat)
                {
                    if (!$stat->getCalculated() && $stat->position_id == $position->posid) {
                        ?>
                       <th style="text-align: center;"><?php	echo $stat->getImage();    ?></th>
                    <?php
                    }          
                }
                ?>
            </tr>
        </thead>
      
        <tbody>
    <?php
    foreach ( $this->awayStaff as $row )
    {
        if ($row->team_staff_id == 0 || $row->position_id == $position->posid ) :
            ?>
           <tr id="staffrow<?php echo $j;?>" class="staffstatrow">
       <td style="text-align: left;">
        <input type="hidden" name="team_staff_id[]" value="<?php echo $row->team_staff_id;?>" />
        <input type="hidden" name="sprojectteam_id[]" value="<?php echo $row->projectteam_id;?>" />
        <input type="checkbox" class="staffstatcheck" id="staffcb<?php echo $i;?>" name="staffcid[]" value="<?php echo $j;?>" /></td>
       <td style="text-align: left; width:200px;">
        <?php echo sportsmanagementHelper::formatName(null, $row->firstname, $row->nickname, $row->lastname, 0) ?>
       </td>
        <?php	foreach ( $this->stats as $ev): ?>
                <?php if (!$ev->getCalculated() && $ev->position_id == $position->posid) : ?>
                    <td>
        <?php $val = isset($this->staffstats[$row->projectteam_id][$row->team_staff_id][$ev->id]) ? $this->staffstats[$row->projectteam_id][$row->team_staff_id][$ev->id] : 0; ?>
                        <input type="text" size="3" class="inputbox staffstat" title="<?php echo $ev->name; ?>" name="staffstat<?php echo $row->team_staff_id;?>_<?php echo $ev->id; ?>" value="<?php echo $val; ?>" />
                    </td>
                <?php endif;    ?>
        <?php endforeach; ?>
         </tr>
            <?php
            $j++;
        endif;  
    }
    ?>
        </tbody>
    </table>
    <?php endforeach; ?>
    <?php endif;    ?>
</fieldset>
