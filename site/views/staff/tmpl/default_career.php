<?php 
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       defaul_career.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage staff
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

if (count($this->history) > 0) {
    ?>
<div class="<?php echo $this->divclassrow;?> table-responsive" id="staff">
    <!-- staff history START -->
    <h4><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_STAFF_CAREER'); ?></h4>    
    <table class="<?php echo $this->config['table_class'];?>">
        <tr>
            <td>
                <br/>
                <table id="player_history" class="<?php echo $this->config['table_class'];?>">
                    <tr class="sectiontableheader"><th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_COMPETITION'); ?></th>
                        <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_SEASON'); ?></th>
                        <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_TEAM'); ?></th>
                        <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_PLAYERS_PICTURE');?></th> 
                        <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_POSITION'); ?></th>
                    </tr>
        <?php
        $k=0;
        foreach ($this->history AS $station)
        {
            $routeparameter = array();
             $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
             $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
             $routeparameter['p'] = $station->project_slug;
             $routeparameter['tid'] = $station->team_slug;
             $routeparameter['pid'] = $this->person->slug;
            $link1 = sportsmanagementHelperRoute::getSportsmanagementRoute('staff', $routeparameter);
            $routeparameter = array();
             $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
             $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
             $routeparameter['p'] = $station->project_slug;
             $routeparameter['tid'] = $station->team_slug;
             $routeparameter['ptid'] = 0;
            $link2 = sportsmanagementHelperRoute::getSportsmanagementRoute('roster', $routeparameter);
                        
            ?>
            <tr class="">
             <td class="td_l"><?php echo HTMLHelper::link($link1, $station->project_name); ?></td>
             <td class="td_l"><?php echo $station->season_name; ?></td>
             <td class="td_l"><?php echo HTMLHelper::link($link2, $station->team_name); ?></td>
                            
                            <td>
                <?PHP
                //echo $player_hist->season_picture;
                echo sportsmanagementHelperHtml::getBootstrapModalImage('career'.$station->project_id.'-'.$station->team_id, $station->season_picture, $station->team_name, '50');
                ?>
                </td>
                            
             <td class="td_l"><?php echo Text::_($station->position_name); ?></td>
            </tr>
            <?php
            $k=(1-$k);
        }
        ?>
                </table>
            </td>
        </tr>
    </table>
    <br /><br />
</div>
    <!-- staff history END -->
    <?php
}
?>
