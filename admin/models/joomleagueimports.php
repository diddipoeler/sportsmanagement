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
jimport('joomla.filesystem.file');
jimport('joomla.application.component.modellist');


/**
 * sportsmanagementModeljoomleagueimports
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModeljoomleagueimports extends JModelList
{


/**
 * sportsmanagementModeljoomleagueimports::import()
 * 
 * @return void
 */
function import()
    {
        $mainframe = JFactory::getApplication();
        $db = JFactory::getDbo(); 
        $option = JRequest::getCmd('option');
        $post = JRequest::get('post');
        $exportfields = array();
        $cid = $post['cid'];
        $jl = $post['jl'];
        $jsm = $post['jsm'];
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($post,true).'</pre>'),'');
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($cid,true).'</pre>'),'');
        
        foreach ( $cid as $key => $value )
        {
            $jl_fields = $db->getTableFields($jl[$value]);
            $jsm_fields = $db->getTableFields($jsm[$value]);
            
            $jsm_table = $jsm[$value];
            // wenn in der jsm tabelle einträge vorhanden sind
            // dann wird nicht kopiert. es soll kein schaden entstehen
            $query = $db->getQuery(true);
            $query->clear();
            $query->select('COUNT(id) AS total');
            $query->from($jsm_table);
            $db->setQuery($query);
            $totals = $db->loadResult();
            
            // Das "i" nach der Suchmuster-Begrenzung kennzeichnet eine Suche ohne
            // Berücksichtigung von Groß- und Kleinschreibung
            if (preg_match("/project_team/i", $jsm_table)) 
            {
            $mainframe->enqueueMessage(JText::_('Sie muessen die Daten aus der Tabelle: ( '.$jsm_table.' ) noch in die neue Struktur umsetzen!'),'');
            } 
            else 
            {
            }

            if ( $totals )
            {
            $mainframe->enqueueMessage(JText::_('Daten aus der Tabelle: ( '.$jl[$value].' ) koennen nicht kopiert werden. Tabelle: ( '.$jsm[$value].' ) nicht leer!'),'Error');     
            }
            else
            {
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($jl_fields,true).'</pre>'),'');
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'jsm_fields<br><pre>'.print_r($jsm_fields,true).'</pre>'),'');
            
            $jsm_field_array = $jsm_fields[$jsm[$value]];
            
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'jsm_field_array<br><pre>'.print_r($jsm_field_array,true).'</pre>'),'');
            
            foreach ( $jl_fields[$jl[$value]] as $key2 => $value2 )
            {
                //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'key<br><pre>'.print_r($key,true).'</pre>'),'');
                //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'value<br><pre>'.print_r($value,true).'</pre>'),'');
                
                if (array_key_exists($key2, $jsm_field_array)) 
                {
                    $exportfields[] = $key2;
                }
            }
            
            $select_fields = implode(',', $exportfields);
            
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'exportfields<br><pre>'.print_r($exportfields,true).'</pre>'),'');
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'select_fields<br><pre>'.print_r($select_fields,true).'</pre>'),'');
            
            $query->clear();
            $query = 'INSERT INTO '.$jsm[$value].' ('.$select_fields.') SELECT '.$select_fields.' FROM '.$jl[$value];
            $db->setQuery($query);
            if (!$db->query())
		    {
			$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__FUNCTION__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
		    }
            else
            {
            $mainframe->enqueueMessage(JText::_('Daten aus der Tabelle: ( '.$jl[$value].' ) in die Tabelle: ( '.$jsm[$value].' ) importiert!'),'Notice');    
            }
            
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'query<br><pre>'.print_r($query,true).'</pre>'),'');
            
            unset($exportfields);
            }    
        
        
        }
     
        }
            
}    

?>