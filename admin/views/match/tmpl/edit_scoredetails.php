<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       edit_scoredetails.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage match
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
?>          
        <fieldset class="adminform">
            <legend>
                <?php
                echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_SD');
                ?>
            </legend>
            <br/>
            <table class='admintable'>
                <tr>

                    <th>
        <?php
        echo $this->match->hometeam;
        ?>
                    </th>
                    <th>
                        &nbsp;
                    </th>
                    <th align="left">
        <?php
        echo $this->match->awayteam;
        ?>
                    </th>
                </tr>
                <!-- Header team names END -->
                <!-- match legs -->
                <?php
                if ($this->projectws->use_legs == 1 ) {
        ?>
                    <tr>
                        <td>
        <?php
        if ($this->table_config['alternative_legs'] == '' ) {
            echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_SD_SETS');
        }
        else
        {
            echo $this->table_config['alternative_legs'];
        }
        ?>:
                        </td>
                        <td>
                            <input    type="text" name="team1_legs" value="<?php echo $this->match->team1_legs; ?>" size="3"
                                    tabindex="100" class="inputbox" />
                        </td>
                        <td align="center">:</td>
                        <td>
                            <input    type="text" name="team2_legs" value="<?php echo $this->match->team2_legs; ?>" size="3"
                                    tabindex="101" class="inputbox" />
                        </td>
                    </tr>
        <?php
                }
                ?>
                <!-- END match legs -->
              
                <!-- Bonus points -->
                <tr>
                    <td class="key">
                        <label>
        <?php
        echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_SD_BONUS');
        ?>
                        </label>
                    </td>
                    <td>
                        <input    type="text" name="team1_bonus" value="<?php echo $this->match->team1_bonus;?>" size="3" class="inputbox" />
                    </td>
                    <td align="center">:</td>
                    <td>
                        <input    type="text" name="team2_bonus" value="<?php echo $this->match->team2_bonus;?>" size="3" class="inputbox" />
                    </td>
                </tr>
                <!-- Bonus points END -->

            <!-- Score Table END -->
            <!-- Additional Details Table START -->
                <!-- Result notice -->
                <tr>
                    <td class="key">
                        <label for="match_result_detail">
        <?php
        echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_SD_SCORE_NOTICE');
        ?>
                        </label>
                    </td>
                    <td colspan='3'>
                        <input    type="text" name="match_result_detail" value="<?php echo $this->match->match_result_detail; ?>" size="40"
                                class="inputbox" />
                    </td>
                </tr>
                <!-- Result notice END -->

            </table>
        </fieldset>