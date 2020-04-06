<?php 
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_info.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage predictionrules
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
?><h3><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_01'); ?></h3>
<p><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_01_01'); ?></p>
<p><?php
if ($this->actJoomlaUser->id < 62) {
    echo Text::sprintf('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_01_02', '<a href="index.php?option=com_user&view=register"><b><i>', '</i></b></a>');
}
else
{
    if (!$this->predictionMember->pmID) {echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_01_03');
    }
}
    ?></p>
<h3><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_02'); ?></h3>
<p><?php
if ($this->predictionGame->auto_approve) {
    echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_02_01');
}
else
{
    echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_02_02');
}
    echo '<br />';
    echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_02_03');
    ?></p>
<h3><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_03'); ?></h3>
<p><?php
    echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_03_01') . '<br />';
if (!$this->predictionGame->admin_tipp) {
    echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_03_02');
}
else
{
    echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_03_03');
}
    ?></p>
<h3><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_04'); ?></h3>
<p><?php  echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_04_01'); ?></p>
<p><?php  echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_04_02'); ?></p>
<h3><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_05'); ?></h3>
<p><?php  echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_05_01'); ?></p>
<?php
if ($this->config['show_points']) {
    ?>
    <p><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_05_02'); ?></p>
    <?php
    foreach (sportsmanagementModelPrediction::$_predictionProjectS AS $predictionProject)
    {
        if ($predictionProjectSettings = sportsmanagementModelPrediction::getPredictionProject($predictionProject->project_id)) {
            ?>
            <table class='blog' cellpadding='0' cellspacing='0' border='1'>
       <tr>
        <td class='sectiontableheader' style='text-align:center; '><?php
            echo $predictionProjectSettings->name . ' - ';
        if ($predictionProject->mode=='0') {
            echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_05_STANDARD_MODE');
        }
        else
            {
            echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_05_TOTO_MODE');
        }
            ?></td>
             </tr>
            </table>
            <table class='blog' cellpadding='0' cellspacing='0'>
             <tr>
              <td class='sectiontableheader' style='text-align:center; '><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_05_RESULT'); ?></td>
              <td class='sectiontableheader' style='text-align:center; '><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_05_YOUR_PREDICTION'); ?></td>
              <td class='sectiontableheader' style='text-align:center; '><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_05_POINTS'); ?></td>
                <?php
                if (($predictionProject->joker) && ($predictionProject->mode==0)) {
                ?><td class='sectiontableheader' style='text-align:center; '><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_05_JOKER_POINTS'); ?></td><?php
                }
                ?>
             </tr>
             <tr class='sectiontableentry1'>
              <td class='info'><?php echo '2:1'; ?></td>
              <td class='info'><?php echo '2:1'; ?></td>
              <td class='info'><?php
                $result = sportsmanagementModelPrediction::createResultsObject(2, 1, 1, 2, 1, 0);
                echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($predictionProject, $result);
            ?></td>
                <?php
                if (($predictionProject->joker) && ($predictionProject->mode==0)) {
                ?>
              <td class='info'><?php
                $result = sportsmanagementModelPrediction::createResultsObject(2, 1, 1, 2, 1, 1);
                echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($predictionProject, $result);
                ?></td><?php
                }
                ?>
             </tr>
             <tr class='sectiontableentry2'>
              <td class='info'><?php echo '2:1'; ?></td>
              <td class='info'><?php echo '3:2'; ?></td>
              <td class='info'><?php
                $result = sportsmanagementModelPrediction::createResultsObject(2, 1, 1, 3, 2, 0);
                echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($predictionProject, $result);
            ?></td>
                <?php
                if (($predictionProject->joker) && ($predictionProject->mode==0)) {
                ?>
              <td class='info'><?php
                $result = sportsmanagementModelPrediction::createResultsObject(2, 1, 1, 3, 2, 1);
                echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($predictionProject, $result);
                ?></td><?php
                }
                ?>
             </tr>
             <tr class='sectiontableentry1'>
              <td class='info'><?php echo '1:1'; ?></td>
              <td class='info'><?php echo '2:2'; ?></td>
              <td class='info'><?php
                $result = sportsmanagementModelPrediction::createResultsObject(1, 1, 0, 2, 2, 0);
                echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($predictionProject, $result);
            ?></td>
                <?php
                if (($predictionProject->joker) && ($predictionProject->mode==0)) {
                ?>
              <td class='info'><?php
                $result = sportsmanagementModelPrediction::createResultsObject(1, 1, 0, 2, 2, 1);
                echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($predictionProject, $result);
                ?></td><?php
                }
                ?>
             </tr>
             <tr class='sectiontableentry2'>
              <td class='info'><?php echo '1:2'; ?></td>
              <td class='info'><?php echo '1:3'; ?></td>
              <td class='info'><?php
                $result = sportsmanagementModelPrediction::createResultsObject(1, 2, 1, 1, 3, 0);
                echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($predictionProject, $result);
            ?></td>
                <?php
                if (($predictionProject->joker) && ($predictionProject->mode==0)) {
                ?>
              <td class='info'><?php
                $result = sportsmanagementModelPrediction::createResultsObject(1, 2, 1, 1, 3, 1);
                echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($predictionProject, $result);
                ?></td><?php
                }
                ?>
             </tr>
             <tr class='sectiontableentry1'>
              <td class='info'><?php echo '2:1'; ?></td>
              <td class='info'><?php echo '0:1'; ?></td>
              <td class='info'><?php
                $result = sportsmanagementModelPrediction::createResultsObject(2, 1, 2, 0, 1, 0);
                echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($predictionProject, $result);
            ?></td>
                <?php
                if (($predictionProject->joker) && ($predictionProject->mode==0)) {
                ?>
              <td class='info'><?php
                $result = sportsmanagementModelPrediction::createResultsObject(2, 1, 2, 0, 1, 1);
                echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($predictionProject, $result);
                ?></td><?php
                }
                ?>
             </tr>
            </table>
            <?php
        }
    }
}
?>
<h3><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_06'); ?></h3>
<p><?php
    echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_06_01');
    ?></p><ul><?php
foreach (sportsmanagementModelPrediction::$_predictionProjectS AS $predictionProject)
{
    if ($predictionProjectSettings = sportsmanagementModelPrediction::getPredictionProject($predictionProject->project_id)) {
        if ($predictionProject->champ > 0) {
            ?><li><?php
if ($predictionProject->overview) {
    echo Text::sprintf(
        'COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_06_HALF_SEASON',
        '<b>'.$predictionProject->points_tipp_champ.'</b>',
        '<b><i>'.$predictionProjectSettings->name.'</i></b>'
    );
}
else
                    {
    echo Text::sprintf(
        'COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_06_FULL_SEASON',
        '<b>'.$predictionProject->points_tipp_champ.'</b>',
        '<b><i>'.$predictionProjectSettings->name.'</i></b>'
    );
}
        ?></li>
            <?php
        }
    }
}
    ?></ul>
<p><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_06_02'); ?></p>
<p><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_06_03'); ?></p>
<h3><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_07'); ?></h3>
<p><?php  echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_07_01'); ?></p>
<h3><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_08'); ?></h3>
<p><?php  echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_08_01'); ?></p>
<p><?php  echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_08_02'); ?></p>
<br />
