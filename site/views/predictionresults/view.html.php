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

jimport('joomla.application.component.view');
jimport( 'joomla.filesystem.file' );

// pagination
require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'pagination.php');


/**
 * sportsmanagementViewPredictionResults
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewPredictionResults extends sportsmanagementView
{
	
	function init()
	{
		//// Get a refrence of the page instance in joomla
//		$document = JFactory::getDocument();
//		$model = $this->getModel();
//    $option = JRequest::getCmd('option');
//    //$optiontext = strtoupper(JRequest::getCmd('option').'_');
//    //$this->assignRef( 'optiontext',			$optiontext );
//    
//		$app = JFactory::getApplication();

		$this->predictionGame = sportsmanagementModelPrediction::getPredictionGame();
        $this->allowedAdmin = sportsmanagementModelPrediction::getAllowed();

		if (isset($this->predictionGame))
		{
			$config	= sportsmanagementModelPrediction::getPredictionTemplateConfig($this->getName());
			$configavatar = sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionusers');
			$configentry = sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionentry');
			$config = array_merge($configentry,$config);
			$overallConfig	= sportsmanagementModelPrediction::getPredictionOverallConfig();

      //$this->assignRef('debuginfo',	$model->getDebugInfo());
      
			//$this->model = $model;
			$this->roundID = sportsmanagementModelPredictionResults::$roundID;
			$this->config = array_merge($overallConfig,$config);
            $this->model->config = $this->config;
			$this->configavatar = $configavatar;
            $this->model->configavatar = $this->configavatar;

			$this->predictionMember = sportsmanagementModelPrediction::getPredictionMember($configavatar);
			//$this->assignRef('predictionMember',	$model->getPredictionMemberAvatar($this->predictionMember, $configavatar ));
			$this->predictionProjectS = sportsmanagementModelPrediction::getPredictionProjectS();
			$this->actJoomlaUser = JFactory::getUser();
			//$this->assignRef('rounds',				$model->getRounds());
			//echo '<br /><pre>~' . print_r($this->predictionMember,true) . '~</pre><br />';

      $predictionRounds[] = JHTML::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_PRED_SELECT_ROUNDS'),'value','text');
      if ( $res = sportsmanagementModelPrediction::getRoundNames($this->predictionGame->id) )
      {
        $predictionRounds = array_merge($predictionRounds,$res);
        }
			$lists['predictionRounds'] = JHTML::_('select.genericList',$predictionRounds,'r','class="inputbox" onchange="this.form.submit(); "','value','text',sportsmanagementModelPrediction::$roundID);
			unset($res);
			unset($predictionRounds);
			
			$this->lists = $lists;
			$this->show_debug_info = JComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info',0);
			// Set page title
			$pageTitle = JText::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_TITLE');
        
        // Get data from the model
 	//$items = $model->getPredictionMembersList($this->config,$this->configavatar,false);
     //$items = $this->get('Data');	
 	//$pagination = $this->get('Pagination');
            $this->memberList = $this->get('Data');
            $this->pagination = $this->get('Pagination');

//$headertitle
$this->headertitle = $pageTitle;

if ( !isset($this->config['table_class']) )
{
$this->config['table_class'] = 'table';    
}

			$this->document->setTitle($pageTitle);

			//parent::display($tpl);
		}
		else
		{
			JError::raiseNotice(500,JText::_('COM_SPORTSMANAGEMENT_PRED_PREDICTION_NOT_EXISTING'));
		}
	}

}
?>
