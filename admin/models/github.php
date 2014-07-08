<?php
/** Joomla Sports Management ein Programm zur Verwaltung f�r alle Sportarten
* @version 1.0.26
* @file		components/sportsmanagement/models/cpanel.php
* @author diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license This file is part of Joomla Sports Management.
*
* Joomla Sports Management is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Joomla Sports Management is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Joomla Sports Management. If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von Joomla Sports Management.
*
* Joomla Sports Management ist Freie Software: Sie k�nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp�teren
* ver�ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* Joomla Sports Management wird in der Hoffnung, dass es n�tzlich sein wird, aber
* OHNE JEDE GEW�HRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew�hrleistung der MARKTF�HIGKEIT oder EIGNUNG F�R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f�r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla  library
//jimport('joomla.github.issues');
//jimport('joomla.github.github');
jimport('joomla.application.component.model');

require_once(JPATH_ROOT.DS.'libraries'.DS.'joomla'.DS. 'github' . DS . 'object.php');
require_once(JPATH_ROOT.DS.'libraries'.DS.'joomla'.DS. 'github' . DS . 'issues.php');

class sportsmanagementModelgithub extends JModelLegacy
{
    var $client = '';
    
    function getGithubList()
    {
        $option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        
        //$this->client = JApplicationHelper::getClientInfo($this->getState('filter.client_id', 0));
        //JApplicationHelper::addClientInfo($this->client);
        $this->client = JApplicationHelper::getClientInfo();
        
        $github_user = JComponentHelper::getParams($option)->get('cfg_github_username','');
        $github_repo = JComponentHelper::getParams($option)->get('cfg_github_repository','');
        
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' client<br><pre>'.print_r($this->client,true).'</pre>'),'');
        //JGithubIssues::$client = $this->client;
        //$GithubList = JGithubIssues::getListByRepository($github_user,$github_repo);
        
        
    }
    
}    

?>