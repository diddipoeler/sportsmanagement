<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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


/**
 * sportsmanagementViewPredictionRules
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewPredictionRules extends JViewLegacy
{
	/**
	 * sportsmanagementViewPredictionRules::display()
	 * 
	 * @param mixed $tpl
	 * @return
	 */
	function display($tpl=null)
	{
		// Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
		$model		= $this->getModel();
    $option = JFactory::getApplication()->input->getCmd('option');
    
		$app = JFactory::getApplication();

		$this->predictionGame = sportsmanagementModelPrediction::getPredictionGame();
        $this->allowedAdmin = sportsmanagementModelPrediction::getAllowed();
        $this->headertitle = JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_SECTION_TITLE');

		if (isset($this->predictionGame))
		{
			$config			= sportsmanagementModelPrediction::getPredictionTemplateConfig($this->getName());
			$overallConfig	= sportsmanagementModelPrediction::getPredictionOverallConfig();

			$this->model = $model;
			$this->config = array_merge($overallConfig,$config);
      $configavatar	= sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionusers');
      $this->configavatar = $configavatar;
			$this->predictionMember = sportsmanagementModelPrediction::getPredictionMember($configavatar);
			$this->predictionProjectS = sportsmanagementModelPrediction::getPredictionProjectS();
			$this->actJoomlaUser = JFactory::getUser();
			// Set page title
			$pageTitle = JText::_('COM_SPORTSMANAGEMENT_PRED_USERS_TITLE'); // 'Tippspiel Regeln'

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