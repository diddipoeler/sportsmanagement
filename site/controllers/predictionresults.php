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

jimport('joomla.application.component.controller');


/**
 * sportsmanagementControllerPredictionResults
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllerPredictionResults extends JControllerLegacy
{

		/**
		 * sportsmanagementControllerPredictionResults::display()
		 * 
		 * @param bool $cachable
		 * @param bool $urlparams
		 * @return void
		 */
		function display($cachable = false, $urlparams = false)
	{

		parent::display($cachable, $urlparams = false);
	}

	/**
	 * sportsmanagementControllerPredictionResults::selectprojectround()
	 * 
	 * @return void
	 */
	function selectprojectround()
	{
		JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $pID = $jinput->getVar('prediction_id','0');
        $pggroup = $jinput->getVar('pggroup','0');
        $pggrouprank = $jinput->getVar('pggrouprank','0');
        $pjID = $jinput->getVar('pj','0');
        $rID = $jinput->getVar('r','0');
        $set_pj = $jinput->getVar('set_pj','0');
        $set_r = $jinput->getVar('set_r','0');
        $cfg_which_database = $jinput->getVar('cfg_which_database','0');
        
//		//echo '<br /><pre>~' . print_r($post,true) . '~</pre><br />'; die();
//		$pID	= JFactory::getApplication()->input->getVar('prediction_id',	null,	'post',	'int');
//		// diddipoeler
//        $pggroup	= JFactory::getApplication()->input->getVar('pggroup',	null,	'post',	'int');
//		$pjID	= JFactory::getApplication()->input->getVar('pj',	null,	'post',	'int');
//        $rID	= JFactory::getApplication()->input->getVar('r',				null,	'post',	'int');
        
        
        
		$link = JSMPredictionHelperRoute::getPredictionResultsRoute($pID,$rID,$pjID,NULL,'',$pggroup,$cfg_which_database);
		$this->setRedirect($link);
	}

}
?>