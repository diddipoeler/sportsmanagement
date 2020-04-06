<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage predictionentry
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementViewPredictionEntry
 *
 * @package 
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewPredictionEntry extends sportsmanagementView
{

  
    /**
     * sportsmanagementViewPredictionEntry::init()
     *
     * @return void
     */
    function init()
    {
 
          $this->headertitle = Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_SECTION_TITLE');
        $this->predictionGame = sportsmanagementModelPrediction::getPredictionGame();
     
        if (isset($this->predictionGame)) {
            $config    = sportsmanagementModelPrediction::getPredictionTemplateConfig($this->getName());
            $overallConfig = sportsmanagementModelPrediction::getPredictionOverallConfig();

            $this->config = array_merge($overallConfig, $config);
            $configavatar    = sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionusers');
            $this->configavatar = $configavatar;
            $this->predictionMember = sportsmanagementModelPrediction::getPredictionMember($configavatar);
            $this->predictionProjectS = sportsmanagementModelPrediction::getPredictionProjectS();
            $this->actJoomlaUser = Factory::getUser();
          
            $this->allowedAdmin = sportsmanagementModelPrediction::getAllowed();

            $this->isPredictionMember = sportsmanagementModelPrediction::checkPredictionMembership();
            $this->isNotApprovedMember = sportsmanagementModelPrediction::checkIsNotApprovedPredictionMember();
            $this->isNewMember = $this->model->newMemberCheck();
            $this->tippEntryDone = $this->model->tippEntryDoneCheck();
          
            if(version_compare(JVERSION, '3.0.0', 'ge')) {
                        $this->websiteName = Factory::getConfig()->get('config.sitename');
            }
            else
            {
                $this->websiteName = Factory::getConfig()->getValue('config.sitename');
            }

            if ($this->allowedAdmin ) {
                $lists = array();
                if ($this->predictionMember->pmID > 0 ) {
                    $dMemberID = (int)$this->predictionMember->pmID;
                }
                else
                    {
                    $dMemberID = 0;
                }
                      
                $predictionMembers[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_PRED_SELECT_MEMBER'), 'value', 'text');
                if ($res = sportsmanagementModelPrediction::getPredictionMemberList($this->config) ) {
                    $predictionMembers = array_merge($predictionMembers, $res);
                }
                  
                $lists['predictionMembers'] = HTMLHelper::_('select.genericList', $predictionMembers, 'uid', 'class="inputbox" onchange="this.form.submit(); "', 'value', 'text', $dMemberID);
                unset($res);
                unset($predictionMembers);
                $this->lists = $lists;
            }

            $this->show_debug_info = ComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info', 0);
            // Set page title
            $pageTitle = Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_TITLE');

            $this->document->setTitle($pageTitle);

        }
        else
        {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_PRED_PREDICTION_NOT_EXISTING'), Log::INFO, 'jsmerror');
        }
    }
  
    /**
     * sportsmanagementViewPredictionEntry::createStandardTippSelect()
     *
     * @param  mixed  $tipp_home
     * @param  mixed  $tipp_away
     * @param  mixed  $tipp
     * @param  string $pid
     * @param  string $mid
     * @param  mixed  $seperator
     * @param  mixed  $allow
     * @return
     */
    function createStandardTippSelect($tipp_home=null,$tipp_away=null,$tipp=null,$pid='0',$mid='0',$seperator,$allow)
    {
        if (!$allow) {
            $disabled=' disabled="disabled" ';
            $css = "readonly";
        } else {
            $disabled='';
            $css = "inputbox";
        }
      
      
        $output = '';
        $output .= '<input type="hidden" name="tipps[' . $pid . '][' . $mid . ']" value="' . $tipp . '" />';
      
        $output .= '<input name="homes[' . $pid . '][' . $mid . ']" class="'.$css.' " style="text-align:center;height:4em; color:blue;font-weight:bold; background: white; max-width: 50px"  size="2" value="' . $tipp_home . '" tabindex="1" type="tel" ' . $disabled . '/>';
      
        /*$output .= '<input name="homes[' . $pid . '][' . $mid . ']" class="'.$css.' '.tippen.'" style="text-align:center;color:blue;font-weight:bold; " size="2" value="' . $tipp_home . '" tabindex="1" type="text" ' . $disabled . '/>';*/
        $output .= ' <b>' . $seperator . '</b> ';
      
        $output .= '<input name="aways[' . $pid . '][' . $mid . ']" class="'.$css.' " style="text-align:center; height:4em; color:blue;font-weight:bold; background: white; max-width: 50px"  size="2" value="' . $tipp_away . '" tabindex="1" type="tel" ' . $disabled . '/>';
      
        /*$output .= '<input name="aways[' . $pid . '][' . $mid . ']" class="'.$css.' '.tippen.'" style="text-align:center;color:blue;font-weight:bold; " size="2" value="' . $tipp_away . '" tabindex="1" type="text" ' . $disabled . '/>';*/
        if (!$allow) {
            $output .= '<input type="hidden" name="homes[' . $pid . '][' . $mid . ']" value="' . $tipp_home . '" />';
            $output .= '<input type="hidden" name="aways[' . $pid . '][' . $mid . ']" value="' . $tipp_away . '" />';
        }
      
      
        return $output;
    }

    /**
     * sportsmanagementViewPredictionEntry::createTotoTippSelect()
     *
     * @param  mixed  $tipp_home
     * @param  mixed  $tipp_away
     * @param  mixed  $tipp
     * @param  string $pid
     * @param  string $mid
     * @param  mixed  $allow
     * @return
     */
    function createTotoTippSelect($tipp_home=null,$tipp_away=null,$tipp=null,$pid='0',$mid='0',$allow)
    {
      
        if ($this->debuginfo ) {
            echo 'tipp_home -> ' . $tipp_home. '<br>';
            echo 'tipp_away -> ' . $tipp_away. '<br>';
            echo 'tipp -> ' . $tipp. '<br>';
            echo 'pid -> ' . $pid. '<br>';
            echo 'mid -> ' . $mid. '<br>';
            echo 'allow -> ' . $allow. '<br>';
        }
  
  
        if (!$allow) {$disabled=' disabled="disabled" ';
        }else{$disabled='';
        }
        $output = '';
        $output .= '<input type="hidden" name="homes[' . $pid . '][' . $mid . ']" value="' . $tipp_home . '" />';
        $output .= '<input type="hidden" name="aways[' . $pid . '][' . $mid . ']" value="' . $tipp_away . '" />';
        $outputArray = array    (
                                    HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_NO_TIPP'), 'id', 'name'),
                                    HTMLHelper::_('select.option', '1', Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_HOME_WIN'), 'id', 'name'),
                                    HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_DRAW'), 'id', 'name'),
                                    HTMLHelper::_('select.option', '2', Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_AWAY_WIN'), 'id', 'name')
           );
        $output .= HTMLHelper::_('select.genericlist', $outputArray, 'tipps['.$pid.']['.$mid.']', 'class="inputbox" size="1" ' . $disabled, 'id', 'name', $tipp);
        unset($outputArray);
        if (!$allow) {
               $output .= '<input type="hidden" name="tipps['.$pid.']['.$mid.']" value="' . $tipp . '" />';
        }
        return $output;
    }

}
?>
