<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_predictionheading.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage predictionheading
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

?>
<script>
console.log("jquery version : "+jQuery().jquery);
console.log("bootstrap version : "+jQuery.fn.tooltip.Constructor.VERSION);

</script>
<?php
if (( isset($this->config['show_prediction_heading']) ) && ( $this->config['show_prediction_heading'] ) ) {
?>
    <table class="table" >
        <tr>
        <td class=''>
    <?php
    echo Text::sprintf('COM_SPORTSMANAGEMENT_PRED_HEAD_ACTUAL_PRED_GAME', '<b><i>'.$this->predictionGame->name.'</i></b>');
    if ((isset($this->showediticon)) && ($this->showediticon) && ($this->predictionMember->pmID > 0)) {
        echo '&nbsp;&nbsp;';
        $link = JSMPredictionHelperRoute::getPredictionMemberRoute(sportsmanagementModelPrediction::$predictionGameID, $this->predictionMember->pmID, 'edit', sportsmanagementModelPrediction::$pjID, sportsmanagementModelPrediction::$pggroup, sportsmanagementModelPrediction::$roundID, sportsmanagementModelPrediction::$cfg_which_database);
        $imgTitle = Text::_('COM_SPORTSMANAGEMENT_PRED_HEAD_EDIT_IMAGE_TITLE');
        $desc = HTMLHelper::image('media/com_sportsmanagement/jl_images/edit.png', $imgTitle, array('border' => 0, 'title' => $imgTitle));
        echo HTMLHelper::link($link, $desc);
    }
    /*
                if ( $this->allowedAdmin )
                {
                echo '&nbsp;&nbsp;';
                $imgTitle = Text::_('COM_SPORTSMANAGEMENT_PRED_HEAD_ENTRY_IMAGE_TITLE');
    $desc = HTMLHelper::image(Uri::root().'media/com_sportsmanagement/jl_images/prediction_entry.png',$imgTitle,array('border' => 0, 'title' => $imgTitle));
    $link = JSMPredictionHelperRoute::getPredictionTippEntryRoute(sportsmanagementModelPrediction::$predictionGameID,$this->predictionMember->pmID,sportsmanagementModelPrediction::$roundID,sportsmanagementModelPrediction::$pjID,'',sportsmanagementModelPrediction::$pggroup,sportsmanagementModelPrediction::$cfg_which_database);
                echo HTMLHelper::link($link,$desc);
                }
                */
                ?>
            </td>
    <?php
    if (!isset($this->allowedAdmin)) {
                $this->allowedAdmin = false;
    }
    if (( $this->getName() == 'predictionusers' )
        || ( ( $this->allowedAdmin ) && ( $this->getName() == 'predictionentry' ) )
        //        || ( $this->getName() == 'predictionresults' )
    ) {
        ?>
      <td class='' style='text-align:right;' >
       <form name='predictionMemberSelect' method='post' >
       <input type='hidden' name='prediction_id' value='<?php echo sportsmanagementModelPrediction::$predictionGameID; ?>' />
                  
                    <input type='hidden' name='pj' value='<?php echo sportsmanagementModelPrediction::$pjID; ?>' />
                    <input type='hidden' name='r' value='<?php echo sportsmanagementModelPrediction::$roundID; ?>' />
                    <input type='hidden' name='pggroup' value='<?php echo sportsmanagementModelPrediction::$pggroup; ?>' />
                    <input type='hidden' name='uid' value='<?php echo sportsmanagementModelPrediction::$predictionMemberID; ?>' />
                  
      <input type='hidden' name='task' value='predictionusers.select' />
      <input type='hidden' name='option' value='com_sportsmanagement' />
                  
        <?php echo HTMLHelper::_('form.token'); ?>
        <?php
        if ($this->getName() == 'predictionresults' ) {
            //echo $this->lists['predictionRounds'] ;
        }
        else
            {
            echo $this->lists['predictionMembers'];
        }
          
           
          
          
            ?>
       </form>
      </td>
        <?php
    }
    ?>
            <td class='' style='text-align:right;'>
            <ul class="list-inline">
                <?php
                $output = '';
                if ($this->config['show_prediction_button'] ) {      
                            $imgTitle = Text::_('COM_SPORTSMANAGEMENT_PRED_HEAD_ENTRY_IMAGE_TITLE');
                    $img = HTMLHelper::image(Uri::root().'media/com_sportsmanagement/jl_images/prediction_entry.png', $imgTitle, array('border' => 0, 'title' => $imgTitle));
                    $link = JSMPredictionHelperRoute::getPredictionTippEntryRoute(sportsmanagementModelPrediction::$predictionGameID, sportsmanagementModelPrediction::$predictionMemberID, sportsmanagementModelPrediction::$roundID, sportsmanagementModelPrediction::$pjID, '', 0, sportsmanagementModelPrediction::$cfg_which_database);
              
                            ?>
                            <li class="list-inline-item">
                            <?php
                            echo HTMLHelper::link($link, $img, array('title' => $imgTitle));
                            ?>
                            </li>
                            <?php
                }
                if ($this->config['show_prediction_button'] ) {
                    $imgTitle = Text::_('COM_SPORTSMANAGEMENT_PRED_HEAD_MEMBER_IMAGE_TITLE');
                    $img = HTMLHelper::image(Uri::root().'media/com_sportsmanagement/jl_images/prediction_member.png', $imgTitle, array('border' => 0, 'title' => $imgTitle));
                    if ($this->predictionMember->pmID > 0 ) {
                            $pmVar = $this->predictionMember->pmID;
                    }
                    else
                    {
                          $pmVar = 0;
                    }
                      
                    $link = JSMPredictionHelperRoute::getPredictionMemberRoute(sportsmanagementModelPrediction::$predictionGameID, $pmVar, '', sportsmanagementModelPrediction::$pjID, sportsmanagementModelPrediction::$pggroup, sportsmanagementModelPrediction::$roundID, sportsmanagementModelPrediction::$cfg_which_database);

                            ?>
              <li class="list-inline-item">
                <?php
                echo HTMLHelper::link($link, $img, array('title' => $imgTitle));
                ?>
              </li>
                <?php
                }
                if ($this->config['show_prediction_button'] ) {
                            $imgTitle = Text::_('COM_SPORTSMANAGEMENT_PRED_HEAD_RESULTS_IMAGE_TITLE');
                    $img = HTMLHelper::image(Uri::root().'media/com_sportsmanagement/jl_images/prediction_results.png', $imgTitle, array('border' => 0, 'title' => $imgTitle));
                    $link = JSMPredictionHelperRoute::getPredictionResultsRoute(sportsmanagementModelPrediction::$predictionGameID, sportsmanagementModelPrediction::$roundID, sportsmanagementModelPrediction::$pjID, $pmVar, '', sportsmanagementModelPrediction::$pggroup, sportsmanagementModelPrediction::$cfg_which_database);

                            ?>
                            <li class="list-inline-item">
                            <?php
                            echo HTMLHelper::link($link, $img, array('title' => $imgTitle));
                            ?>
                            </li>
                            <?php
                }
                if ($this->config['show_prediction_button'] ) {
                    $imgTitle = Text::_('COM_SPORTSMANAGEMENT_PRED_HEAD_RANKING_IMAGE_TITLE');
                    $img = HTMLHelper::image(Uri::root().'media/com_sportsmanagement/jl_images/prediction_ranking.png', $imgTitle, array('border' => 0, 'title' => $imgTitle));
                    $link = JSMPredictionHelperRoute::getPredictionRankingRoute(sportsmanagementModelPrediction::$predictionGameID, sportsmanagementModelPrediction::$pjID, sportsmanagementModelPrediction::$roundID, '', sportsmanagementModelPrediction::$pggroup, 0, 0, 0, 0, sportsmanagementModelPrediction::$cfg_which_database);

                            ?>
                            <li class="list-inline-item">
                            <?php
                            echo HTMLHelper::link($link, $img, array('title' => $imgTitle));
                            ?>
                            </li>
                            <?php
                }
                if ($this->config['show_pred_group_link'] ) {
                    if ($this->config['show_prediction_button'] ) {
                            $imgTitle = Text::_('COM_SPORTSMANAGEMENT_PRED_HEAD_RANKING_GROUP_IMAGE_TITLE');
                              $img = HTMLHelper::image(Uri::root().'media/com_sportsmanagement/jl_images/teaminfo_icon.png', $imgTitle, array('border' => 0, 'title' => $imgTitle));
                              $link = JSMPredictionHelperRoute::getPredictionRankingRoute(sportsmanagementModelPrediction::$predictionGameID, sportsmanagementModelPrediction::$pjID, sportsmanagementModelPrediction::$roundID, '', sportsmanagementModelPrediction::$pggroup, 1, 0, 0, 0, sportsmanagementModelPrediction::$cfg_which_database);

                            ?>
                            <li class="list-inline-item">
                            <?php
                            echo HTMLHelper::link($link, $img, array('title' => $imgTitle));
                            ?>
                            </li>
                            <?php
                    }
                }
                if ($this->config['show_prediction_button'] ) {              
                    $imgTitle = Text::_('COM_SPORTSMANAGEMENT_PRED_HEAD_RULES_IMAGE_TITLE');
                    $img = HTMLHelper::image(Uri::root().'media/com_sportsmanagement/jl_images/prediction_rules.png', $imgTitle, array('border' => 0, 'title' => $imgTitle));
                    $link = JSMPredictionHelperRoute::getPredictionRulesRoute(sportsmanagementModelPrediction::$predictionGameID, sportsmanagementModelPrediction::$cfg_which_database);

                            ?>
                            <li class="list-inline-item">
                            <?php
                            echo HTMLHelper::link($link, $img, array('title' => $imgTitle));
                            ?>
                            </li>
                            <?php
                }
                ?>
                </ul>
            </td>
    <?php
          
    ?>
        </tr>
    </table>
    <?php
}
?>
