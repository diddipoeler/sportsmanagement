<?php 
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_show_help.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage predictionresults
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

if (!isset($this->config['show_scoring'])) {$this->config['show_scoring']=1;
}
$gameModeStr = ($this->model->predictionProject->mode==0) ? Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_STANDARD_MODE') : Text::_('JL_PRED_RESULTS_TOTO_MODE');
?><p style='font-weight: bold; '><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_NOTICE'); ?></p><p><ul><li><i><?php
                        echo Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_NOTICE_INFO_01');
if (!$this->config['show_all_user']) {
    ?></i></li><li><i><?php
echo Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_NOTICE_INFO_02');
?></i></li><li><i><?php
}
else
{
    echo ' ';
}
                        echo Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_NOTICE_INFO_03');
        ?></i></li><li><i><?php
                        echo Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_NOTICE_INFO_04');
        ?></i></li><li><i><?php
                        echo Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_NOTICE_INFO_05');
        ?></i></li><li><i><?php
                        echo Text::sprintf('COM_SPORTSMANAGEMENT_PRED_RESULTS_NOTICE_INFO_06', '<b>'.$gameModeStr.'</b>');
        ?><?php
if (($this->config['show_scoring']) && ($this->predictionMember->pmID > 0)) {
    ?></i></li><li><i><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_NOTICE_INFO_07');
?><table class='blog' cellpadding='0' cellspacing='0'>
  <tr>
<td class='sectiontableheader' style='text-align:center; '><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_RESULT'); ?></td>
   <td class='sectiontableheader' style='text-align:center; '><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_YOUR_PREDICTION'); ?></td>
   <td class='sectiontableheader' style='text-align:center; '><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_POINTS'); ?></td>
    <?php
    if (($this->model->predictionProject->joker) && ($this->model->predictionProject->mode==0)) {
        ?><td class='sectiontableheader' style='text-align:center; '><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_JOKER_POINTS'); ?></td><?php
    }
    ?>
  </tr>
  <tr class='sectiontableentry1'>
   <td class='info'><?php echo '2:1'; ?></td>
                                    <td class='info'><?php echo '2:1'; ?></td>
                                    <td class='info'><?php
                                    $result = sportsmanagementModelPrediction::createResultsObject(2, 1, 1, 2, 1, 0);
                                    echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($this->model->predictionProject, $result);
            ?></td>
            <?php
            if (($this->model->predictionProject->joker) && ($this->model->predictionProject->mode==0)) {
                ?>
                <td class='info'><?php
                 $result = sportsmanagementModelPrediction::createResultsObject(2, 1, 1, 2, 1, 1);
                 echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($this->model->predictionProject, $result);
                    ?></td><?php
            }
            ?>
                                </tr>
                                <tr class='sectiontableentry2'>
                                    <td class='info'><?php echo '2:1'; ?></td>
                                    <td class='info'><?php echo '3:2'; ?></td>
                                    <td class='info'><?php
                                    $result = sportsmanagementModelPrediction::createResultsObject(2, 1, 1, 3, 2, 0);
                                    echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($this->model->predictionProject, $result);
            ?></td>
            <?php
            if (($this->model->predictionProject->joker) && ($this->model->predictionProject->mode==0)) {
                ?>
                <td class='info'><?php
                 $result = sportsmanagementModelPrediction::createResultsObject(2, 1, 1, 3, 2, 1);
                 echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($this->model->predictionProject, $result);
                    ?></td><?php
            }
            ?>
                                </tr>
                                <tr class='sectiontableentry1'>
                                    <td class='info'><?php echo '1:1'; ?></td>
                                    <td class='info'><?php echo '2:2'; ?></td>
                                    <td class='info'><?php
                                    $result = sportsmanagementModelPrediction::createResultsObject(1, 1, 0, 2, 2, 0);
                                    echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($this->model->predictionProject, $result);
            ?></td>
            <?php
            if (($this->model->predictionProject->joker) && ($this->model->predictionProject->mode==0)) {
                ?>
                <td class='info'><?php
                 $result = sportsmanagementModelPrediction::createResultsObject(1, 1, 0, 2, 2, 1);
                 echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($this->model->predictionProject, $result);
                    ?></td><?php
            }
            ?>
                                </tr>
                                <tr class='sectiontableentry2'>
                                    <td class='info'><?php echo '1:2'; ?></td>
                                    <td class='info'><?php echo '1:3'; ?></td>
                                    <td class='info'><?php
                                    $result = sportsmanagementModelPrediction::createResultsObject(1, 2, 1, 1, 3, 0);
                                    echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($this->model->predictionProject, $result);
            ?></td>
            <?php
            if (($this->model->predictionProject->joker) && ($this->model->predictionProject->mode==0)) {
                ?>
                <td class='info'><?php
                 $result = sportsmanagementModelPrediction::createResultsObject(1, 2, 1, 1, 3, 1);
                 echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($this->model->predictionProject, $result);
                    ?></td><?php
            }
            ?>
                                </tr>
                                <tr class='sectiontableentry1'>
                                    <td class='info'><?php echo '2:1'; ?></td>
                                    <td class='info'><?php echo '0:1'; ?></td>
                                    <td class='info'><?php
                                    $result = sportsmanagementModelPrediction::createResultsObject(2, 1, 2, 0, 1, 0);
                                    echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($this->model->predictionProject, $result);
            ?></td>
            <?php
            if (($this->model->predictionProject->joker) && ($this->model->predictionProject->mode==0)) {
                ?>
                <td class='info'><?php
                 $result = sportsmanagementModelPrediction::createResultsObject(2, 1, 2, 0, 1, 1);
                 echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($this->model->predictionProject, $result);
                    ?></td><?php
            }
            ?>
                                </tr>
                            </table><?php
}
else
{
    ?></i></li><li><i><?php
echo Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_READ_RULES');
}
        ?></i></li></ul></p>
