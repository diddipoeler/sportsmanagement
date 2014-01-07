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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_COMPONENT_SITE.DS.'helpers'.DS.'html.php' );
require_once(JPATH_COMPONENT_SITE.DS.'helpers'.DS.'countries.php');
require_once(JPATH_COMPONENT_SITE.DS.'helpers'.DS.'ranking.php' );
require_once(JPATH_COMPONENT_SITE.DS.'helpers'.DS.'route.php' );
require_once(JPATH_COMPONENT_SITE.DS.'helpers'.DS.'pagination.php' );
require_once(JPATH_COMPONENT_SITE.DS.'helpers'.DS.'simpleGMapGeocoder.php' );

require_once(JPATH_COMPONENT_SITE.DS.'models'.DS.'project.php' );
require_once(JPATH_COMPONENT_SITE.DS.'models'.DS.'results.php');

require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'divisions.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'rounds.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'round.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'teams.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'team.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'club.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'playground.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'projectteams.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'match.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'databasetool.php');

require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'sportsmanagement.php');    

// sprachdatei aus dem backend laden
$langtag = JFactory::getLanguage();
//echo 'Current language is: ' . $langtag->getTag();

$lang = JFactory::getLanguage();
$extension = 'com_sportsmanagement';
$base_dir = JPATH_ADMINISTRATOR;
$language_tag = $langtag->getTag();
$reload = true;
$lang->load($extension, $base_dir, $language_tag, $reload);
    
// welche tabelle soll genutzt werden
$params = JComponentHelper::getParams( 'com_sportsmanagement' );
$database_table	= $params->get( 'cfg_which_database_table' ); 
DEFINE( 'COM_SPORTSMANAGEMENT_TABLE',$database_table );

 
// import joomla controller library
jimport('joomla.application.component.controller');
 
// Get an instance of the controller prefixed by HelloWorld
$controller = JController::getInstance('sportsmanagement');
 
// Perform the Request task
$controller->execute(JRequest::getCmd('task'));
 
// Redirect if set by the controller
$controller->redirect();
