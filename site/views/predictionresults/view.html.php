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
class sportsmanagementViewPredictionResults extends JViewLegacy
{
	/**
	 * sportsmanagementViewPredictionResults::display()
	 * 
	 * @param mixed $tpl
	 * @return
	 */
	function display($tpl=null)
	{
		// Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
		$model		= $this->getModel();
    $option = JRequest::getCmd('option');
    //$optiontext = strtoupper(JRequest::getCmd('option').'_');
    //$this->assignRef( 'optiontext',			$optiontext );
    
		$mainframe = JFactory::getApplication();

		$this->assign('predictionGame',sportsmanagementModelPrediction::getPredictionGame());

		if (isset($this->predictionGame))
		{
			$config			= sportsmanagementModelPrediction::getPredictionTemplateConfig($this->getName());
			$configavatar			= sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionusers');
			$configentry			= sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionentry');
			$config = array_merge($configentry,$config);
			$overallConfig	= sportsmanagementModelPrediction::getPredictionOverallConfig();

      //$this->assignRef('debuginfo',	$model->getDebugInfo());
      
			$this->assignRef('model',				$model);
			$this->assignRef('roundID',				$this->model->roundID);
			$this->assign('config',				array_merge($overallConfig,$config) );
            $model->config = $this->config;
			$this->assignRef('configavatar',				$configavatar );
            $model->configavatar = $this->configavatar;

			$this->assign('predictionMember',	sportsmanagementModelPrediction::getPredictionMember($configavatar));
			//$this->assignRef('predictionMember',	$model->getPredictionMemberAvatar($this->predictionMember, $configavatar ));
			$this->assign('predictionProjectS',	sportsmanagementModelPrediction::getPredictionProjectS());
			$this->assign('actJoomlaUser',		JFactory::getUser());
			//$this->assignRef('rounds',				$model->getRounds());
			//echo '<br /><pre>~' . print_r($this->predictionMember,true) . '~</pre><br />';

      $predictionRounds[] = JHTML::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_PRED_SELECT_ROUNDS'),'value','text');
      if ( $res = sportsmanagementModelPrediction::getRoundNames($this->predictionGame->id) ){$predictionRounds = array_merge($predictionRounds,$res);}
			$lists['predictionRounds']=JHTML::_('select.genericList',$predictionRounds,'r','class="inputbox" onchange="this.form.submit(); "','value','text',$this->model->roundID);
			unset($res);
			unset($predictionRounds);
			
			$this->assignRef('lists',$lists);
			$this->assign('show_debug_info', JComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info',0) );
			// Set page title
			$pageTitle = JText::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_TITLE');
        
        // Get data from the model
 	//$items = $model->getPredictionMembersList($this->config,$this->configavatar,false);
     $items = $this->get('Data');	
 	$pagination = $this->get('Pagination');
            $this->assignRef('memberList', $items );
            $this->assignRef('pagination', $pagination);

/*    
    // limit, limitstart und limitende
    $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
    $limitstart = JRequest::getVar('limitstart', 0, '', 'int');
    $limitend = $limitstart + $limit;
    $this->assignRef('limit',$limit);
    $this->assignRef('limitstart',$limitstart);
    $this->assignRef('limitend',$limitend);
*/

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