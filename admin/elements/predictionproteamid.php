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

defined('_JEXEC') or die('Restricted access');


/**
 * JFormFieldpredictionproteamid
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class JFormFieldpredictionproteamid extends JFormField
{

	var	$_name = 'predictionproteamid';

	
	/**
	 * JFormFieldpredictionproteamid::getInput()
	 * 
	 * @return
	 */
	protected function getInput()
  {
		$db = sportsmanagementHelper::getDBConnection();
    $app = JFactory::getApplication();
		$option = 'com_sportsmanagement';
        
        $prediction_id = $app->getUserState( "$option.prediction_id", '0' );
        
        // welche tabelle soll genutzt werden
        $params = JComponentHelper::getParams( 'com_sportsmanagement' );
        $database_table	= $params->get( 'cfg_which_database_table' );
        
        $query	= $db->getQuery(true);
    $query->select('tl.id AS id,t.name as teamname');
    $query->from('#__sportsmanagement_project_team AS tl');
    $query->join('LEFT', '#__sportsmanagement_season_team_id AS st on tl.team_id = st.id');   
    $query->join('LEFT', '#__sportsmanagement_team AS t on st.team_id = t.id');
    $query->join('INNER', '#__sportsmanagement_prediction_project as prepro on prepro.project_id = tl.project_id');

    
    $query->where('prepro.prediction_id = '. $prediction_id);
    $query->group('tl.id');

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <pre> '.print_r($prediction_id,true).'</pre><br>' ),'Notice');			


		$db->setQuery( $query );
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        }
        
		$teams = $db->loadObjectList();
        
        if ( !$teams )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }
  


    $mitems = array();
    
		foreach ( $teams as $team ) {
			$mitems[] = JHTML::_('select.option',  $team->id, '&nbsp;'. ' ( '.$team->teamname.' ) '  );
		}
		
		//$output= JHTML::_('select.genericlist',  $mitems, ''.$control_name.'['.$name.'][]', 'class="inputbox" size="50" multiple="multiple" ', 'value', 'text', $value );
        $output= JHtml::_('select.genericlist',  $mitems, $this->name.'[]', 'class="inputbox" multiple="multiple" size="'.count($mitems).'"', 'value', 'text', $this->value, $this->id );
		return $output;
	}
}
 