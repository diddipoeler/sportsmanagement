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

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * modJSMStatistikRekordHelper
 * 
 * @package 
 * @author abcde
 * @copyright 2015
 * @version $Id$
 * @access public
 */
class modJSMStatistikRekordHelper
{

	/**
	 * modJSMStatistikRekordHelper::getData()
	 * 
	 * @param mixed $params
	 * @param mixed $module
	 * @return
	 */
	static function getData($params,$module)
	{
// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
    $db = sportsmanagementHelper::getDBConnection();
$query = $db->getQuery(true);
$result = array();

if ( $params->get('jsm_stat_spielpaarungen'))
{

$query->select('count(*) as total');
$query->from('#__sportsmanagement_match');
    
$db->setQuery( $query );
$anzahl  = $db->loadResult();

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' anzahl<br><pre>'.print_r($anzahl,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jsm_stat_paarungen<br><pre>'.print_r($params->get('jsm_stat_paarungen'),true).'</pre>'),'');

$temp  = new stdClass();
$temp->image  = 'modules/'.$module->module.'/images/matches.png';
$temp->anzahl  = $anzahl;
$temp->anzahlbis  = $params->get('jsm_stat_paarungen');
$temp->anzahldiff  = $params->get('jsm_stat_paarungen') - $anzahl;
$temp->text  = JText::sprintf( 'SHOW_MATCHES_DIFF',"<strong>".number_format($temp->anzahldiff,0, ",", ".")."</strong>","<strong>".number_format($temp->anzahlbis,0, ",", ".")."</strong>" );

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' temp<br><pre>'.print_r($temp,true).'</pre>'),'');

$result[]  = $temp;
$result  = array_merge($result);
unset($temp);
}
		
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result<br><pre>'.print_r($result,true).'</pre>'),'');		
		 $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
		return $result;
		
	}
	
	

	
}
