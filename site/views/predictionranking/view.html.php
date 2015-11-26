<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// pagination
require_once (JPATH_COMPONENT . DS . 'helpers' . DS . 'pagination.php');

jimport('joomla.application.component.view');


/**
 * sportsmanagementViewPredictionRanking
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewpredictionranking extends JViewLegacy
{
	/**
	 * sportsmanagementViewpredictionranking::display()
	 * 
	 * @param mixed $tpl
	 * @return
	 */
	function display($tpl=null)
	{
		// Get a refrence of the page instance in joomla
    $app = JFactory::getApplication();
		$document	= JFactory::getDocument();
		$uri = JFactory :: getURI();
//		$js ="registerhome('".JURI::base()."','Prediction Game Extension','".$app->getCfg('sitename')."','0');". "\n";
//    $document->addScriptDeclaration( $js );	
		$model		= $this->getModel();
    $option = JRequest::getCmd('option');
    //$optiontext = strtoupper(JRequest::getCmd('option').'_');
//    $this->assignRef( 'optiontext',			$optiontext );

		$this->assign('predictionGame',sportsmanagementModelPrediction::getPredictionGame());
        $this->assign('allowedAdmin',sportsmanagementModelPrediction::getAllowed());

    // Get data from the model
 	$items = $this->get('Data');	
 	$pagination = $this->get('Pagination');
 
	// push data into the template
	$this->assignRef('items', $items);	
	$this->assignRef('pagination', $pagination);
    
    $this->assign('headertitle', JText::_('COM_SPORTSMANAGEMENT_PRED_RANK_TITLE'));
		
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' items<br><pre>'.print_r($this->items,true).'</pre>'),'');
        
		if (isset($this->predictionGame))
		{
			$config	= sportsmanagementModelPrediction::getPredictionTemplateConfig($this->getName());
			$overallConfig	= sportsmanagementModelPrediction::getPredictionOverallConfig();
      $configavatar	= sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionusers');
      $configentries = sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionentry');
      
      //$this->assignRef('debuginfo',	$model->getDebugInfo());
      
			$this->assignRef('model', $model);
			$this->assignRef('roundID', sportsmanagementModelPrediction::$roundID);
			//$this->assignRef('config',				array_merge($overallConfig,$config));
      $this->assignRef('configavatar', $configavatar );
      $this->assignRef('configentries', $configentries );
      //array_merge($configentries,$config);
      $this->assign('config', array_merge($overallConfig,$config));
      
			$this->assign('predictionMember', sportsmanagementModelPrediction::getPredictionMember($configavatar));
			$this->assign('predictionProjectS',	sportsmanagementModelPrediction::getPredictionProjectS());
			$this->assign('actJoomlaUser', JFactory::getUser());
			
			//echo '<br /><pre>~' . print_r( $this->config, true ) . '~</pre><br />';
            
            $ranking_array = array();
			$ranking_array[] = JHTML ::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_PRED_RANK_SINGLE_RANK'));
			$ranking_array[] = JHTML ::_('select.option','1',JText::_('COM_SPORTSMANAGEMENT_PRED_RANK_GROUP_RANK'));
			$lists['ranking_array'] = $ranking_array;
			unset($ranking_array);

			$type_array = array();
			$type_array[] = JHTML ::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_PRED_RANK_FULL_RANKING'));
			$type_array[] = JHTML ::_('select.option','1',JText::_('COM_SPORTSMANAGEMENT_PRED_RANK_FIRST_HALF'));
			$type_array[] = JHTML ::_('select.option','2',JText::_('COM_SPORTSMANAGEMENT_PRED_RANK_SECOND_HALF'));
			$lists['type'] = $type_array;
			unset($type_array);

			$this->assignRef('lists',$lists);
      //$this->assign('show_debug_info', JComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info',0) );
			// Set page title
			$pageTitle = JText::_('COM_SPORTSMANAGEMENT_PRED_RANK_TITLE');
			
			$mdlProject = JModelLegacy::getInstance("Project", "sportsmanagementModel");
			foreach ( $this->predictionProjectS as $project )
			{
      $mdlProject->setProjectId($project->project_id);
      }
      
      $map_config = $mdlProject->getMapConfig($mdlProject::$cfg_which_database);
		  $this->assignRef('mapconfig',$map_config ); // Loads the project-template -settings for the GoogleMap
			
      $this->assign('PredictionMembersList',sportsmanagementModelPrediction::getPredictionMembersList($this->config,$this->configavatar) );
      
      $this->geo = new JSMsimpleGMapGeocoder();
	    $this->geo->genkml3prediction($this->predictionGame->id,$this->PredictionMembersList);
	  
			$document->setTitle($pageTitle);

			parent::display($tpl);
		}
		else
		{
			JError::raiseNotice(500,JText::_('COM_SPORTSMANAGEMENT_PRED_PREDICTION_NOT_EXISTING'));
		}
	}

}
?>