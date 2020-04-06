<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_gamehistory.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage referee
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

?>
<!-- Player stats History START -->
<?php if (count($this->games)) {
?>
<h2>
<?php
echo Text::_('COM_SPORTSMANAGEMENT_PERSON_GAMES_HISTORY');
?>
</h2>
<div class="<?php echo $this->divclassrow;?> table-responsive" id="referee_gameshistory">
<table class="<?php echo $this->config['history_table_class']; ?>">
    <tr>
        <td><br />
            <table class="<?php echo $this->config['history_table_class']; ?>">
                <thead>
                    <tr class="sectiontableheader">
                        <th colspan="6"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_GAMES'); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $k=0;
                foreach ($this->games as $game)
                {

                    $routeparameter = array();
                    $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
                    $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
                    $routeparameter['p'] = $this->project->slug;
                    $routeparameter['mid'] = $game->id;
                    $report_link = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport', $routeparameter);
                  
                    //$report_link=sportsmanagementHelperRoute::getMatchReportRoute($this->project->slug,$game->id);
        ?>

                    <tr class="">
                        <td><?php
                        echo HTMLHelper::link($report_link, strftime($this->config['games_date_format'], strtotime($game->match_date)));
        ?>
                        </td>
                        <td class="td_r">
                        <?php
                        echo sportsmanagementHelperHtml::getBootstrapModalImage('gamehistory'.$game->id.'-'.$game->projectteam1_id, $game->home_logo, $game->home_name, '20');                      
                        echo $this->teams[$game->projectteam1_id]->name;
                        ?>
                        </td>
                        <td class="td_r"><?php echo $game->team1_result; ?></td>
                        <td class="td_c"><?php echo $this->overallconfig['seperator']; ?>
                        </td>
                        <td class="td_l"><?php echo $game->team2_result; ?></td>
                        <td class="td_l">
                        <?php
                        echo sportsmanagementHelperHtml::getBootstrapModalImage('gamehistory'.$game->id.'-'.$game->projectteam2_id, $game->away_logo, $game->away_name, '20');                      
                        echo $this->teams[$game->projectteam2_id]->name;
                        ?>
                        </td>
                    </tr>
        <?php
        $k=(1-$k);
                }
        ?>
                    </tbody>
            </table>
        </td>
    </tr>
</table>
</div>	
<br />
    <?php
}
?>
