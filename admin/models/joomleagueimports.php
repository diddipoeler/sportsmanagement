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

$maxImportTime = 1920;
if ((int)ini_get('max_execution_time') < $maxImportTime){@set_time_limit($maxImportTime);}

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
 * sportsmanagementModeljoomleagueimports::gettotals()
 * 
 * @return
 */
function gettotals()
{
    $mainframe = JFactory::getApplication();
        $db = JFactory::getDbo(); 
        $option = JRequest::getCmd('option');
        
        // retrieve the value of the state variable. First see if the variable has been passed
        // in the request. Otherwise retrieve the stored value. If none of these are specified,
        // the specified default value will be returned
        // function syntax is getUserStateFromRequest( $key, $request, $default );
        
        $jsm_table = $mainframe->getUserStateFromRequest( "$option.jsm_table", 'jsm_table', '' );
        $jl_table = $mainframe->getUserStateFromRequest( "$option.jl_table", 'jl_table', '' );
        $season_id = $mainframe->getUserState( "$option.season_id", '0' );
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jl_table<br><pre>'.print_r($jl_table,true).'</pre>'),'');
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' season_id<br><pre>'.print_r($season_id,true).'</pre>'),'');
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jsm_table<br><pre>'.print_r($jsm_table,true).'</pre>'),'');

        // Das "i" nach der Suchmuster-Begrenzung kennzeichnet eine Suche ohne
            // Berücksichtigung von Groß- und Kleinschreibung
            if ( preg_match("/project_team/i", $jsm_table) ) 
            {
            
            $query = $db->getQuery(true);
            $query->clear();
            $query->select('COUNT(pt.id) AS total');
            $query->from($jl_table.' AS pt');
            $query->join('INNER','#__sportsmanagement_project AS p ON p.id = pt.project_id');
            $query->where('pt.import = 0');
            
            if ( $season_id )
            {
                $query->where('p.season_id = '.$season_id);
            }
            
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            
            $db->setQuery($query);
            $total = $db->loadResult();
            
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'total<br><pre>'.print_r($total,true).'</pre>'),'');
            
            return $total;
            }
            
            if (preg_match("/team_player/i", $jsm_table)) 
            {
            $query = $db->getQuery(true);
            $query->clear();
            $query->select('COUNT(tp.id) AS total');
            $query->from($jl_table.' AS tp');
            $query->join('INNER','#__sportsmanagement_project_team AS pt ON pt.id = tp.projectteam_id');
            $query->join('INNER','#__sportsmanagement_project AS p ON p.id = pt.project_id');
            $query->where('tp.import = 0');
            
            if ( $season_id )
            {
                $query->where('p.season_id = '.$season_id);
            }
            
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            
            $db->setQuery($query);
            $total = $db->loadResult();
            
            return $total;    
            }    


}        

/**
 * sportsmanagementModeljoomleagueimports::checkimport()
 * 
 * @return void
 */
function checkimport()
{
$mainframe = JFactory::getApplication();
        $db = JFactory::getDbo(); 
        $option = JRequest::getCmd('option');
        $post = JRequest::get('post');
        $exportfields = array();
        $cid = $post['cid'];
        $jl = $post['jl'];
        $jsm = $post['jsm'];
        $season_id= $post['filter_season'];
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'post<br><pre>'.print_r($post,true).'</pre>'),'');
        
        
  foreach ( $cid as $key => $value )
        {
        $jsm_table = $jsm[$value];
        $jl_table = $jl[$value];
        
        // Das "i" nach der Suchmuster-Begrenzung kennzeichnet eine Suche ohne
            // Berücksichtigung von Groß- und Kleinschreibung
            if (preg_match("/project_team/i", $jsm_table)) 
            {
                // store the variable that we would like to keep for next time
                // function syntax is setUserState( $key, $value );
                $mainframe->setUserState( "$option.jsm_table", $jsm_table );
                $mainframe->setUserState( "$option.jl_table", $jl_table );
                $mainframe->setUserState( "$option.season_id", $season_id );
                //JRequest::setVar('jsm_table', $jsm_table);
            return true;    
            }
            elseif (preg_match("/team_player/i", $jsm_table)) 
            {
                // store the variable that we would like to keep for next time
                // function syntax is setUserState( $key, $value );
                $mainframe->setUserState( "$option.jsm_table", $jsm_table );
                $mainframe->setUserState( "$option.jl_table", $jl_table );
                $mainframe->setUserState( "$option.season_id", $season_id );
                //JRequest::setVar('jsm_table', $jsm_table);
            return true;    
            }
            else
            {
            return false;
            }
        }        
    
}



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
        $jlid = $post['jlid'];
        $jsm = $post['jsm'];
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($post,true).'</pre>'),'');
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($cid,true).'</pre>'),'');
        
        foreach ( $cid as $key => $value )
        {
            $jl_fields = $db->getTableFields($jl[$value]);
            $jsm_fields = $db->getTableFields($jsm[$value]);
            
            $jsm_table = $jsm[$value];
            $jl_table = $jl[$value];
            // wenn in der jsm tabelle einträge vorhanden sind
            // dann wird nicht kopiert. es soll kein schaden entstehen
            $query = $db->getQuery(true);
            $query->clear();
            $query->select('COUNT(id) AS total');
            $query->from($jsm_table);
            $db->setQuery($query);
            
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            
            $totals = $db->loadResult();
            
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'totals<br><pre>'.print_r($totals,true).'</pre>'),'');
            
            // noch die zu importierenden tabellen prüfen
            // Das "i" nach der Suchmuster-Begrenzung kennzeichnet eine Suche ohne
            // Berücksichtigung von Groß- und Kleinschreibung
            if ( preg_match("/project_team/i", $jsm_table) || preg_match("/team_player/i", $jsm_table) ) 
            {
            $mainframe->enqueueMessage(JText::_('Sie muessen die Daten aus der Tabelle: ( '.$jl_table.' ) in die neue Struktur umsetzen!'),'');
            // wir müssen ein neues feld an die tabelle zum import einfügen
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jl_fields<br><pre>'.print_r($jl_fields,true).'</pre>'),'');
            if (array_key_exists('import', $jl_fields[$jl_table]  ) )
            {
                $mainframe->enqueueMessage(JText::_('Importfeld ist vorhanden'),'');
            }
            else
            {
                $mainframe->enqueueMessage(JText::_('importfeld ist nicht vorhanden'),'');
                $query = $db->getQuery(true);
                $query = 'ALTER TABLE '.$jl_table.' ADD import TINYINT(1)  NOT NULL DEFAULT 0 ';
                $db->setQuery($query);
                $db->query();
            }
            
            } 
            else 
            {
            

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
            
            // Create an object for the record we are going to update.
            $object = new stdClass();
            // Must be a valid primary key value.
            $object->id = $jlid[$value];
            $object->import = 1;
            // Update their details in the users table using id as the primary key.
            $result = JFactory::getDbo()->updateObject('#__'.COM_SPORTSMANAGEMENT_TABLE.'_jl_tables', $object, 'id');   
            
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'query<br><pre>'.print_r($query,true).'</pre>'),'');
            
            unset($exportfields);
            
            // in der template tabelle stehen die parameter nicht im json format
            if (preg_match("/template_config/i", $jsm_table)) 
            {
                $mainframe->enqueueMessage(JText::_('Die Parameter aus der Tabelle: ( '.$jsm_table.' ) werden in das JSON-Format umgesetzt!'),'');
                $query = $db->getQuery(true);
                $query->clear();
                $query->select('id,params,template');
                $query->from($jsm_table);
                $db->setQuery($query);
                $results = $db->loadObjectList();
                
                foreach($results as $param )
                {
                    $xmlfile = JPATH_COMPONENT_SITE.DS.'settings'.DS.'default'.DS.$param->template.'.xml';
                    
                    if ( JFile::exists($xmlfile) )
			        {
                    $form = JForm::getInstance($param->template, $xmlfile,array('control'=> ''));
		            $form->bind($param->params);
                    $newparams = array();
                    foreach($form->getFieldset($fieldset->name) as $field)
                    {
                    $newparams[$field->name] = $field->value;
                    }
                    $t_params = json_encode( $newparams );

                    // Create an object for the record we are going to update.
                    $object = new stdClass();
                    // Must be a valid primary key value.
                    $object->id = $param->id;
                    $object->params = $t_params;
                    // Update their details in the users table using id as the primary key.
                    $result = JFactory::getDbo()->updateObject($jsm_table, $object, 'id');
                    }   	
                }
            
            }
            
            } 
            
            }   
        
        
        }
     
        }
            
}    

?>