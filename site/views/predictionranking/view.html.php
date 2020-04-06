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
 * @subpackage predictionranking
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementViewPredictionRanking
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewpredictionranking extends sportsmanagementView
{
    
    /**
     * sportsmanagementViewpredictionranking::init()
     * 
     * @return void
     */
    function init()
    {

        $this->predictionGame = sportsmanagementModelPrediction::getPredictionGame();
        $this->allowedAdmin = sportsmanagementModelPrediction::getAllowed();

        $this->limit = $this->model->getLimit();
        $this->limitstart = $this->model->getLimitStart();
        $this->ausgabestart = $this->limitstart + 1;
        $this->ausgabeende = $this->limitstart + $this->limit;
    
        // push data into the template
        $this->state = $this->get('State');    
        $this->items = $this->get('Data');    
        $this->pagination =$this->get('Pagination');
    
          $this->headertitle = Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_TITLE');
       
        if (isset($this->predictionGame)) {
            $config    = sportsmanagementModelPrediction::getPredictionTemplateConfig($this->getName());
            $overallConfig    = sportsmanagementModelPrediction::getPredictionOverallConfig();
            $configavatar    = sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionusers');
            $configentries = sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionentry');
      
            $this->roundID = sportsmanagementModelPrediction::$roundID;
            $this->configavatar = $configavatar;
            $this->configentries = $configentries;
            $this->config = array_merge($overallConfig, $config);
      
            $this->predictionMember = sportsmanagementModelPrediction::getPredictionMember($configavatar);
            $this->predictionProjectS = sportsmanagementModelPrediction::getPredictionProjectS();
            $this->actJoomlaUser = Factory::getUser();
            
            
            $ranking_array = array();
            $ranking_array[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_SINGLE_RANK'));
            $ranking_array[] = HTMLHelper::_('select.option', '1', Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_GROUP_RANK'));
            $lists['ranking_array'] = $ranking_array;
            unset($ranking_array);

            $type_array = array();
            $type_array[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_FULL_RANKING'));
            $type_array[] = HTMLHelper::_('select.option', '1', Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_FIRST_HALF'));
            $type_array[] = HTMLHelper::_('select.option', '2', Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_SECOND_HALF'));
            $lists['type'] = $type_array;
            unset($type_array);

            $this->lists = $lists;
            // Set page title
            $pageTitle = Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_TITLE');
            
            $mdlProject = BaseDatabaseModel::getInstance("Project", "sportsmanagementModel");
            foreach ( $this->predictionProjectS as $project )
            {
                  $mdlProject->setProjectId($project->project_id);
            }
      
            $map_config = $mdlProject->getMapConfig($mdlProject::$cfg_which_database);
             $this->mapconfig = $map_config; // Loads the project-template -settings for the GoogleMap
            
            $this->PredictionMembersList = sportsmanagementModelPrediction::getPredictionMembersList($this->config, $this->configavatar);
      
            $this->geo = new JSMsimpleGMapGeocoder();
              $this->geo->genkml3prediction($this->predictionGame->id, $this->PredictionMembersList);
      
            $this->document->setTitle($pageTitle);

        }
        else
        {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_PRED_PREDICTION_NOT_EXISTING'), Log::INFO, 'jsmerror');
        }
    }

}
?>
