<?php
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k�nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp�teren
* ver�ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n�tzlich sein wird, aber
* OHNE JEDE GEW�HELEISTUNG, bereitgestellt; sogar ohne die implizite
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
 
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file'); 

/**
 * sportsmanagementModeldatabasetool
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementModeldatabasetool extends JModelAdmin
{

    var $_sport_types_events = array();
    var $_sport_types_position = array();
    var $_sport_types_position_parent = array();
    var $_success_text = '';
    var $my_text = '';
	var $storeFailedColor = 'red';
	var $storeSuccessColor = 'green';
	var $existingInDbColor = 'orange';

    static $db_num_rows = 0;
    static $jsmtables = null;
    static $bar_value = 0;
    
    
    public function __construct($config = array())
        {   

                parent::__construct($config);
                parent::setDbo(sportsmanagementHelper::getDBConnection());
        }
        
    /**
     * sportsmanagementModeldatabasetool::runJoomlaQuery()
     * 
     * @param string $setModelVar
     * @return
     */
    public static function runJoomlaQuery($setModelVar='')
    {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $result = false;
        if(version_compare(JVERSION,'3.0.0','ge')) 
        {
            $result = JFactory::getDbo()->execute();
            if ( $setModelVar )
            {
            $setModelVar::$db_num_rows = JFactory::getDbo()->getAffectedRows();
            }
            //if ( JFactory::getDbo()->getAffectedRows() )
//            {
//                $result = JFactory::getDbo()->getAffectedRows();
//            }
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getAffectedRows<br><pre>'.print_r(JFactory::getDbo()->getAffectedRows(),true).'</pre>'),'');
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result<br><pre>'.print_r($result,true).'</pre>'),'');
            //try {
//                }
//            catch (Exception $e) {
//    // catch any database errors.
//    //$db->transactionRollback();
//    JErrorPage::render($e);
//}
        }
        else
        {
            $result = JFactory::getDbo()->query();
            if ( $setModelVar )
            {
            $setModelVar::$db_num_rows = JFactory::getDbo()->getAffectedRows();
            }
            //if ( $this->db_num_rows )
//            {
//                $result = $this->db_num_rows;
//            }
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' db_num_rows<br><pre>'.print_r($this->db_num_rows,true).'</pre>'),'');
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result<br><pre>'.print_r($result,true).'</pre>'),'');
        } 
        return $result;   
    }


    /**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true) 
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $cfg_which_media_tool = JComponentHelper::getParams($option)->get('cfg_which_media_tool',0);
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelagegroup getForm cfg_which_media_tool<br><pre>'.print_r($cfg_which_media_tool,true).'</pre>'),'Notice');
        // Get the form.
		$form = $this->loadForm('com_sportsmanagement.databasetool', 'databasetool', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
        /*        
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($option)->get('ph_icon',''));
        $form->setFieldAttribute('picture', 'directory', 'com_'.COM_SPORTSMANAGEMENT_TABLE.'/database/agegroups');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
        */
		return $form;
	}
    
    
    /**
     * sportsmanagementModeldatabasetool::getMemory()
     * 
     * @param mixed $startmemory
     * @param mixed $endmemory
     * @return
     */
    function getMemory($startmemory,$endmemory) 
    {
  $memory = array();
  $temp = new stdClass();
  $temp->name = 'start';
  $temp->memory = $startmemory;
  $memory[] = $temp;
  $temp = new stdClass();
  $temp->name = 'ende';
  $temp->memory = $endmemory;
  $memory[] = $temp;
  $temp = new stdClass();
  $temp->name = 'verbrauch';
  $temp->memory = $endmemory - $startmemory;
  $memory[] = $temp;
  return $memory;
}
    
    /**
     * sportsmanagementModeldatabasetool::getQueryTime()
     * 
     * @param mixed $starttime
     * @param mixed $endtime
     * @return
     */
    function getQueryTime($starttime,$endtime) 
    {
  $starttime=explode(" ",$starttime);
  $endtime=explode(" ",$endtime);
  return round($endtime[0]-$starttime[0]+$endtime[1]-$starttime[1],3);
}
    
    /**
     * sportsmanagementModeldatabasetool::getSportsManagementTables()
     * 
     * @return
     */
    function getSportsManagementTables()
    {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $db = JFactory::getDbo();
        //$jsmtables = array();
        $prefix = $mainframe->getCfg('dbprefix');
        
        $result = $db->getTableList();
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' prefix <br><pre>'.print_r($prefix,true).'</pre>'),'');
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getTableList <br><pre>'.print_r($result,true).'</pre>'),'');
        
        foreach ( $result as $key => $value )
        {
        if ( preg_match("/sportsmanagement/i", $value ) && preg_match("/".$prefix."/i", $value )  )
        {
        self::$jsmtables[] = $value;
        }   
        }
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jsmtables <br><pre>'.print_r($jsmtables,true).'</pre>'),'');
        
//        $query = "SHOW TABLES LIKE '%_sportsmanagement%'";
//		$db->setQuery($query);
//        if(version_compare(JVERSION,'3.0.0','ge')) 
//        {
//        $db->execute();
//        }
//        else
//        {
//        $db->query();
//        }
		return self::$jsmtables;
    }
    
    
    /**
     * sportsmanagementModeldatabasetool::checkImportTablesJlJsm()
     * 
     * @param mixed $tables
     * @return void
     */
    function checkImportTablesJlJsm($tables)
    {
        $mainframe = JFactory::getApplication();
        $db = JFactory::getDbo();
        $prefix = $mainframe->getCfg('dbprefix');  
        $storeFailedColor = 'red';
	    $storeSuccessColor = 'green';
	    $existingInDbColor = 'orange';
        $exporttable = array();
        $convert = array (
'joomleague' => 'sportsmanagement'
  );
        $convert2 = array (
$prefix.'joomleague_' => ''
  );
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($tables,true).'</pre>'),'');
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' prefix' .  ' <br><pre>'.print_r($prefix,true).'</pre>'),'');
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' convert2' .  ' <br><pre>'.print_r($convert2,true).'</pre>'),'');
        
        $count = 1;
        foreach ( $tables as $key => $value )
        {
            $jsmtable = str_replace(array_keys($convert), array_values($convert), $value->name  );
            $query = "SHOW TABLES LIKE '%".$jsmtable."%'";
		    $db->setQuery($query);
            $result = $db->loadResultArray();
            
            if ( $result )
            {
            $temptable = new stdClass();
            $temptable->id = $value->id;
            $temptable->jl = $value->name;
            
            $check_table = str_replace(array_keys($convert2), array_values($convert2), $value->name  );
            
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' check_table' .  ' <br><pre>'.print_r($check_table,true).'</pre>'),'');
            
            switch ($check_table)
            {
                
                case 'project_team':
                case 'team_player':
                case 'team_staff':
                $temptable->info = JText::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_JL_NEW_STRUCTUR');
                $temptable->color = $existingInDbColor;
                break;
                case 'match':
                case 'club':
                case 'league':
                case 'person':
                case 'playground':
                case 'project':
                case 'round':
                case 'season':
                case 'team':
                case 'match_commentary':
                case 'match_player':
                case 'match_statistic':
                case 'prediction_groups':
                case 'prediction_member':
                case 'template_config':
                $temptable->info = JText::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_JL_OK');
                $temptable->color = $storeSuccessColor;
                break;
                default:
                
                $temptable->info = JText::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_JL_NOT_IMPORT');
                $temptable->color = $storeFailedColor;
                break;
            }
            $temptable->import = $value->import;
            $temptable->import_data = $value->import_data;
            $temptable->checked_out = $value->checked_out;
            $temptable->jsm = $jsmtable;
            $exporttable[] = $temptable;
            $count++;
            }
            
        }
        
        return $exporttable;    
        
    }
    
    
    
    /**
     * sportsmanagementModeldatabasetool::getJoomleagueImportTables()
     * 
     * @return void
     */
    function getJoomleagueImportTables()
    {
        $mainframe = JFactory::getApplication();
        $db = JFactory::getDbo();  
        $option = JRequest::getCmd('option');
        $query = $db->getQuery(true);
        
        $query->select('*');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_jl_tables');
        $db->setQuery($query);
        $result = $db->loadObjectList();
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($result,true).'</pre>'),'');
        
        return $result;
    }
    
        
    /**
     * sportsmanagementModeldatabasetool::getJoomleagueTables()
     * 
     * @return
     */
    function getJoomleagueTables()
    {
        $mainframe = JFactory::getApplication();
        $query = JFactory::getDbo()->getQuery(true);
        //$db = JFactory::getDbo();  
        $option = JRequest::getCmd('option');
        $query = "SHOW TABLES LIKE '%_joomleague%'";
		JFactory::getDbo()->setQuery($query);
        $result = JFactory::getDbo()->loadResultArray();
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($result,true).'</pre>'),'');
        
        if ( $result )
        {
        foreach ( $result as $key => $value )
        {
        // Create and populate an object.
                $temp = new stdClass();
                $temp->name = $value;
                $temp->import = 0;
                $temp->import_data = 0;
                // Insert the object into the table.
                $result = JFactory::getDbo()->insertObject('#__sportsmanagement_jl_tables', $temp);
                if ( $result )
                {

                }
                else
                {
                    
                }    
        }
        }    
		return $result;
    }
    
    /**
     * sportsmanagementModeldatabasetool::setSportsManagementTableQuery()
     * 
     * @param mixed $table
     * @param mixed $command
     * @return
     */
    function setSportsManagementTableQuery($table, $command)
    {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $query = strtoupper($command).' TABLE `'.$table.'`'; 
            JFactory::getDbo()->setQuery($query);
            if (!self::runJoomlaQuery())
		{
			$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r(JFactory::getDbo()->getErrorMsg(),true).'</pre>'),'Error');
			return false;
		}
        
        
		return true;
    }
    
    /**
     * sportsmanagementModeldatabasetool::checkQuotes()
     * 
     * @param mixed $sm_quotes
     * @return
     */
    function checkQuotes($sm_quotes)
    {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        //$db = JFactory::getDbo();   
        //$xml = JFactory::getXMLParser( 'Simple' );
        
        foreach ( $sm_quotes as $key => $type )
        {
            $temp = explode(",",$type);
            $query = JFactory::getDbo()->getQuery(true);
            
            // Select some fields
        $query->select('count(*) AS count');
        // From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_rquote');
        $query->where('daily_number = '.$temp[1]);
            
		    JFactory::getDbo()->setQuery($query);
		    // sind zitate vorhanden ?
            if ( !JFactory::getDbo()->loadResult() )
            {
            /* Ein JDatabaseQuery Objekt beziehen */
            $query = JFactory::getDbo()->getQuery(true);
            $query->delete()->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_rquote')->where('daily_number = '.$temp[1].''  );
            JFactory::getDbo()->setQuery($query);
            $result = self::runJoomlaQuery();
            
            // joomla versionen
            if(version_compare(JVERSION,'3.0.0','ge')) 
        {
    $xml = JFactory::getXML(JPATH_ADMINISTRATOR.'/components/'.$option.'/helpers/xml_files/quote_'.$temp[0].'.xml',true); 
    $document = 'version';   
    $quotes = 'children()';     
            }
            else
            {
//                $xml = JFactory::getXMLParser( 'Simple' );
//    $xml->loadFile(JPATH_ADMINISTRATOR.'/components/'.$option.'/helpers/xml_files/quote_'.$temp[0].'.xml');
$xml = JFactory::getXML(JPATH_ADMINISTRATOR.'/components/'.$option.'/helpers/xml_files/quote_'.$temp[0].'.xml',true);
    $document = 'version';
    //$quotes = 'document->quotes'; 
    $quotes = 'children()'; 
            }
    
    //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($xml,true).'</pre>'),'Notice');
    
            //$xml->loadFile(JPATH_ADMINISTRATOR.'/components/'.$option.'/helpers/xml_files/quote_'.$temp[0].'.xml');
            
            $quote_version = (string)$xml->$document;
            
//            // joomla versionen
//            if(version_compare(JVERSION,'3.0.0','ge')) 
//        {
//    $quote_version = (string)$xml->$document;
//            }
//            else
//            {
//            foreach( $xml->$document as $version ) 
//            {
//            $quote_version = $version->data();
//            //$mainframe->enqueueMessage(JText::_('Zitate '.$temp[0].' Version : '.$quote_version.' wird installiert !'),'');
////            $this->my_text .= '<span style="color:'.$this->storeSuccessColor.'"><strong>';
////		    $this->my_text .= JText::_('Installiere Zitate').'</strong></span><br />';
////			$this->my_text .= JText::_('Zitate '.$temp[0].' Version : '.$quote_version.' wird installiert !').'<br />';
//            }
//            }
            
            $this->my_text .= '<span style="color:'.$this->storeSuccessColor.'"><strong>';
		    $this->my_text .= JText::_('Installiere Zitate').'</strong></span><br />';
			$this->my_text .= JText::_('Zitate '.$temp[0].' Version : '.$quote_version.' wird installiert !').'<br />';
            
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' quotes<br><pre>'.print_r($quotes,true).'</pre>'),'Notice');
            
            foreach( $xml->children() as $quote ) 
            {
            
            $author = str_replace("\\", "\\\\", (string)$quote->quote->attributes()->author );
            $notes = (string)$quote->quote->attributes()->notes;
            $daily_number = (string)$quote->quote->attributes()->daily_number;
            $zitat = (string)$quote->quote;
            
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' quote<br><pre>'.print_r($quote,true).'</pre>'),'Notice');
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' author<br><pre>'.print_r($author,true).'</pre>'),'Notice');
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' notes<br><pre>'.print_r($notes,true).'</pre>'),'Notice');
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' daily_number<br><pre>'.print_r($daily_number,true).'</pre>'),'Notice');
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' zitat<br><pre>'.print_r($zitat,true).'</pre>'),'Notice');
            
            if ( $zitat )
            {
            $insertquery = JFactory::getDbo()->getQuery(true);
            // Insert columns.
            $columns = array('daily_number','author','quote','notes');
            // Insert values.
            $values = array('\''.$temp[1].'\'','\''.$author.'\'','\''.$zitat.'\'','\''.$notes.'\'');
            // Prepare the insert query.
            $insertquery
            ->insert(JFactory::getDbo()->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_rquote'))
            ->columns(JFactory::getDbo()->quoteName($columns))
            ->values(implode(',', $values));
            // Set the query using our newly populated query object and execute it.
            JFactory::getDbo()->setQuery($insertquery);
                
	        if (!self::runJoomlaQuery())
			{
			self::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
            }
			else
			{
    	    } 
            
            }
            
            }
            
//            foreach( $xml->$quotes as $quote ) 
//            {
//                
//            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' quote<br><pre>'.print_r($quote,true).'</pre>'),'Notice');
//                
//            $author = '';
//            $zitat = '';
//            $name = $quote->getElementByPath('quote');
//            $attributes = $name->attributes();
//            $author = $attributes['author'];
//            $notes = $attributes['notes'];
//            $author = str_replace("\\", "\\\\", $author);
//            $zitat = $name->data();
//
//            $insertquery = $db->getQuery(true);
//            // Insert columns.
//            $columns = array('daily_number','author','quote','notes');
//            // Insert values.
//            $values = array('\''.$temp[1].'\'','\''.$author.'\'','\''.$zitat.'\'','\''.$notes.'\'');
//            // Prepare the insert query.
//            $insertquery
//            ->insert($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_rquote'))
//            ->columns($db->quoteName($columns))
//            ->values(implode(',', $values));
//            // Set the query using our newly populated query object and execute it.
//            $db->setQuery($insertquery);
//                
//	        if (!$db->query())
//			{
//			self::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
//            }
//			else
//			{
//    	    } 
//            
//            
//            
//            }
            
            }
            else
            {
            
            // joomla versionen
            if(version_compare(JVERSION,'3.0.0','ge')) 
        {
    $xml = JFactory::getXML(JPATH_ADMINISTRATOR.'/components/'.$option.'/helpers/xml_files/quote_'.$temp[0].'.xml'); 
    $document = 'version';         
            }
            else
            {
//                $xml = JFactory::getXMLParser( 'Simple' );
//    $xml->loadFile(JPATH_ADMINISTRATOR.'/components/'.$option.'/helpers/xml_files/quote_'.$temp[0].'.xml');
$xml = JFactory::getXML(JPATH_ADMINISTRATOR.'/components/'.$option.'/helpers/xml_files/quote_'.$temp[0].'.xml');
    $document = 'version';
            }
            
            $quote_version = (string)$xml->$document;
            
//// joomla versionen
//            if(version_compare(JVERSION,'3.0.0','ge')) 
//        {
//    $quote_version = (string)$xml->$document;
//            }
//            else
//            {
//            foreach( $xml->$document as $version ) 
//            {
//            $quote_version = $version->data();
//            }
//            }
            
            $this->my_text .= '<span style="color:'.$this->existingInDbColor.'"><strong>';
					$this->my_text .= JText::_('Installierte Zitate').'</strong></span><br />';
					$this->my_text .= JText::_('Zitate '.$temp[0].' Version : '.$quote_version.' ist installiert !').'<br />';    
            }
        }    
        return $this->my_text;
    }

    
    /**
     * sportsmanagementModeldatabasetool::insertAgegroup()
     * 
     * @param mixed $search_nation
     * @param mixed $filter_sports_type
     * @return
     */
    function insertAgegroup($search_nation,$filter_sports_type)
    {
    $mainframe = JFactory::getApplication();
    $option = JRequest::getCmd('option'); 
    
    //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($search_nation,true).'</pre>'),'Notice');
    //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($filter_sports_type,true).'</pre>'),'Notice');
    
    $mdl = JModelLegacy::getInstance("sportstype", "sportsmanagementModel");
    $p_sportstype = $mdl->getTable();
    $p_sportstype->load((int) $filter_sports_type);
    $temp = explode("_",$p_sportstype->name);
    $sport_type_name = strtolower(array_pop($temp));
    
    //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($sport_type_name,true).'</pre>'),'Notice');
    $filename = JPATH_ADMINISTRATOR.'/components/'.$option.'/helpers/xml_files/'.'agegroup_'.strtolower($search_nation).'_'.$sport_type_name.'.xml';
    
    if (!JFile::exists($filename)) 
    {
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($filename,true).'</pre>'),'Error');
        $this->my_text = '<span style="color:'.$this->storeFailedColor.'"><strong>';
					$this->my_text .= JText::_('Fehlende Altersgruppen').'</strong></span><br />';
					$this->my_text .= JText::sprintf('Die Datei %1$s ist nicht vorhanden!','agegroup_'.strtolower($search_nation).'_'.$sport_type_name.'.xml').'<br />';
					
					//$this->_success_text['Altersgruppen:'] = $my_text;
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($this->my_text,true).'</pre>'),'');
        return $this->my_text;
    }  
    else
    {
      $this->my_text = '<span style="color:'.$this->existingInDbColor.'"><strong>';
					$this->my_text .= JText::_('Installierte Altersgruppen').'</strong></span><br />';
					$this->my_text .= JText::sprintf('Die Datei %1$s ist vorhanden!','agegroup_'.strtolower($search_nation).'_'.$sport_type_name.'.xml').'<br />';
                    
                    if(version_compare(JVERSION,'3.0.0','ge')) 
        {
    $xml = JFactory::getXML($filename);        
            }
            else
            {
//                $xml = JFactory::getXMLParser( 'Simple' );
//    $xml->loadFile($filename);
    $xml = JFactory::getXML($filename); 
            }
//        $xml = JFactory::getXMLParser( 'Simple' );
//       $xml->loadFile($filename); 
       
       if ( $xml )
       {
       
       //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' xml -><br><pre>'.print_r($xml,true).'</pre>'),'');
        
       // schleife altersgruppen anfang
       foreach( $xml->agegroups as $agegroups ) 
{
   
//   $name = $agegroup->getElementByPath('agegroup');
//   $attributes = $name->attributes();
   
   //$mainframe->enqueueMessage(JText::_(get_class($this).'<br><pre>'.print_r($name->data(),true).'</pre>'),'Notice');
   
   $agegroup = (string)$agegroups->agegroup;
   $info = (string)$agegroups->agegroup->attributes()->info;
   $picture = 'images/com_sportsmanagement/database/agegroups/'.(string)$agegroups->agegroup->attributes()->picture;
   
   $query = JFactory::getDbo()->getQuery(true);
   // Select some fields
        $query->select('id');
		// From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_agegroup');
        $query->where('name LIKE '.JFactory::getDbo()->Quote(''.addslashes(stripslashes($agegroup)).''));
        $query->where('country LIKE '.JFactory::getDbo()->Quote(''.addslashes(stripslashes($search_nation)).''));
        $query->where('sportstype_id = '.$filter_sports_type);
        
//   $query="SELECT id
//            FROM #__".COM_SPORTSMANAGEMENT_TABLE."_agegroup where name like '".$agegroup."' and country like '".$search_nation."' and sportstype_id = ".$filter_sports_type;
		    
            
            JFactory::getDbo()->setQuery($query);
		    // altersgruppe nicht vorhanden ?
            if ( !JFactory::getDbo()->loadResult() )
            {
//   // Get a db connection.
//        $db = JFactory::getDbo();
        // Create a new query object.
        $query = JFactory::getDbo()->getQuery(true);
        // Insert columns.
        $columns = array('name','picture','info','sportstype_id','country');
        // Insert values.
        $values = array('\''.$agegroup.'\'','\''.$picture.'\'' ,'\''.$info.'\'' ,'\''.$filter_sports_type.'\'' ,'\''.$search_nation.'\''  );
        // Prepare the insert query.
        $query
            ->insert(JFactory::getDbo()->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_agegroup'))
            ->columns(JFactory::getDbo()->quoteName($columns))
            ->values(implode(',', $values));
        // Set the query using our newly populated query object and execute it.
        JFactory::getDbo()->setQuery($query);
        
        if (!self::runJoomlaQuery())
		{
			
            //$mainframe->enqueueMessage(JText::_(get_class($this).' insertSportType<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
			$result = false;
		}
        else
        {
        //$mainframe->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_AGEGROUP_SUCCESS',$agegroup),'Notice');
        $this->my_text .= '<span style="color:'.$this->storeSuccessColor.'"><strong>';
					$this->my_text .= JText::_('Installierte Altersgruppen').'</strong></span><br />';
					$this->my_text .= JText::sprintf('Die Altersgruppe %1$s wurde angelegt!!',$agegroup).'<br />';
        }
        
   }
   
   }
   // schleife altersgruppen ende    
      } 
       
       
    return $this->my_text;   
    }
                   
    
    
    }
    
    /**
     * sportsmanagementModeldatabasetool::checkAssociations()
     * 
     * @return
     */
    function checkAssociations()
    {
    $mainframe = JFactory::getApplication();
    $option = JRequest::getCmd('option');    
    /* Ein Datenbankobjekt beziehen */
    $db = JFactory::getDbo();   
    
    if(version_compare(JVERSION,'3.0.0','ge')) 
        {
    $xml = JFactory::getXML(JPATH_ADMINISTRATOR.'/components/'.$option.'/helpers/xml_files/associations.xml'); 
    $document = 'associations';         
            }
            else
            {
//                $xml = JFactory::getXMLParser( 'Simple' );
//    $xml->loadFile(JPATH_ADMINISTRATOR.'/components/'.$option.'/helpers/xml_files/associations.xml');
    $xml = JFactory::getXML(JPATH_ADMINISTRATOR.'/components/'.$option.'/helpers/xml_files/associations.xml');
    $document = 'associations';
            }

    
    if (!JFile::exists(JPATH_ADMINISTRATOR.'/components/'.$option.'/helpers/xml_files/associations.xml')) 
    {
        return false;
    } 
    
    $params = JComponentHelper::getParams( $option );
    $country_assoc = $params->get( 'cfg_country_associations' );
    if ( $country_assoc )
   {
    $country_assoc_del = "'".implode("','",$country_assoc)."'";    
    }
    
    //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($country_assoc,true).'</pre>'),'Notice');
    //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($country_assoc_del,true).'</pre>'),'Notice');
    
   
    /* Ein JDatabaseQuery Objekt beziehen */
    if ( $country_assoc_del )
   {
    $query = JFactory::getDbo()->getQuery(true);
    $query->delete()->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_associations')->where('country NOT IN ('.$country_assoc_del.')'  );
    JFactory::getDbo()->setQuery($query);
    $result = self::runJoomlaQuery();
    }
    
    //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'');
     
    $image_path = '/images/'.$option.'/database/associations/';
    
    // schleife
    foreach( $xml->$document as $association ) 
{
   //$name = $association->getElementByPath('assocname');
   //$attributes = $name->attributes();
   //$country = $attributes['country'];
   $country = (string)$association->assocname->attributes()->country;
   
   //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($attributes['country'],true).'</pre>'),'Notice');
   //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($name->data(),true).'</pre>'),'Notice');
   
   if ( $country_assoc )
   {
   // welche l�nder m�chte denn der user haben ?
   foreach( $country_assoc as $key => $value )
   {
   if ( $value == $country  ) 
   {
   //$country = $attributes['country'];
   $main = (string)$association->assocname->attributes()->main;
   $parentmain = (string)$association->assocname->attributes()->parentmain;
   
   $icon = $image_path.(string)$association->assocname->attributes()->icon;
   $flag = (string)$association->assocname->attributes()->flag;
   $website = (string)$association->assocname->attributes()->website;
   $shortname = (string)$association->assocname->attributes()->shortname;
   
   $assocname = (string)$association->assocname;
   
   $query1 = JFactory::getDbo()->getQuery(true);
   // Select some fields
        $query1->select('id');
		// From the table
		$query1->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_associations');
        $query1->where('country LIKE '.JFactory::getDbo()->Quote(''.addslashes(stripslashes($country)).''));
        $query1->where('name LIKE '.JFactory::getDbo()->Quote(''.addslashes(stripslashes($assocname)).''));
        
//   $query1 = "SELECT id FROM #__".COM_SPORTSMANAGEMENT_TABLE."_associations WHERE country LIKE '" . $country ."' and name LIKE '".$assocname."'";
    JFactory::getDbo()->setQuery( $query1 );
	$result = JFactory::getDbo()->loadResult();
   
    //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($query1,true).'</pre>'),'Notice');
   
   $export = array();
   if ( !$result )
   {
   if ( empty($parentmain) )
   {
   // Create a new query object.
                $insertquery = JFactory::getDbo()->getQuery(true);
                // Insert columns.
                $columns = array('country','name','picture','assocflag','website','short_name');
                // Insert values.
                $values = array('\''.$country.'\'','\''.$assocname.'\'','\''.$icon.'\'','\''.$flag.'\'','\''.$website.'\'','\''.$shortname.'\'');
                // Prepare the insert query.
                $insertquery
                ->insert(JFactory::getDbo()->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_associations'))
                ->columns(JFactory::getDbo()->quoteName($columns))
                ->values(implode(',', $values));
                // Set the query using our newly populated query object and execute it.
                JFactory::getDbo()->setQuery($insertquery);
                
	            if (!self::runJoomlaQuery())
			    {
			    //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error'); 
			    self::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
                }
			    else
			    {
			    $temp = new stdClass();
                $temp->id = JFactory::getDbo()->insertid();
                $export[] = $temp;
                $this->_assoclist[$country][$main] = array_merge($export); 
			    } 
   }
   else
   {
   $parent_id = $this->_assoclist[$country][$parentmain];
   //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($parent_id,true).'</pre>'),'');
    // Create a new query object.
                $insertquery = JFactory::getDbo()->getQuery(true);
                // Insert columns.
                $columns = array('country','name','parent_id','picture','assocflag','website','short_name');
                // Insert values.
                $values = array('\''.$country.'\'','\''.$assocname.'\'',$parent_id[0]->id,'\''.$icon.'\'','\''.$flag.'\'','\''.$website.'\'','\''.$shortname.'\'');
                // Prepare the insert query.
                $insertquery
                ->insert(JFactory::getDbo()->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_associations'))
                ->columns(JFactory::getDbo()->quoteName($columns))
                ->values(implode(',', $values));
                // Set the query using our newly populated query object and execute it.
                JFactory::getDbo()->setQuery($insertquery);
                
	            if (!self::runJoomlaQuery())
			    {
			    //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
                self::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__); 
			    }
			    else
			    {
			    $temp = new stdClass();
                $temp->id = JFactory::getDbo()->insertid();
                $export[] = $temp;
                $this->_assoclist[$country][$main] = array_merge($export); 
			    } 
   
   }
   
   }
   else
   {
   $temp = new stdClass();
   $temp->id = $result;
   $export[] = $temp;
   $this->_assoclist[$country][$main] = array_merge($export); 
   
   // Fields to update.
                $query = JFactory::getDbo()->getQuery(true);
                $fields = array(
                JFactory::getDbo()->quoteName('picture') . '=' . '\''.$icon.'\''
                );
                // Conditions for which records should be updated.
                $conditions = array(
                JFactory::getDbo()->quoteName('id') . '=' . $result
                );
                $query->update(JFactory::getDbo()->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_associations'))->set($fields)->where($conditions);
                JFactory::getDbo()->setQuery($query);
                $result = self::runJoomlaQuery(); 
   
   
    
   }
   }
   }
   }
   }
   
    //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($this->_assoclist,true).'</pre>'),'');   
    }
    
    
    /**
     * sportsmanagementModeldatabasetool::checkSportTypeStructur()
     * 
     * @param mixed $type
     * @return
     */
    function checkSportTypeStructur($type)
    {
    $mainframe = JFactory::getApplication();
    $option = JRequest::getCmd('option');    
    //$db_table = JPATH_ADMINISTRATOR.'/components/'.$option.'/helpers/sp_structur/'.$type.'.txt';    
    //$fileContent = JFile::read($db_table);    
    //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur fileContent<br><pre>'.print_r($fileContent,true).'</pre>'),'Notice');
    
//    $xml = JFactory::getXMLParser( 'Simple' );
//    $xml->loadFile(JPATH_ADMINISTRATOR.'/components/'.$option.'/helpers/sp_structur/'.$type.'.xml');
    
    if(version_compare(JVERSION,'3.0.0','ge')) 
        {
    $xml = JFactory::getXML(JPATH_ADMINISTRATOR.'/components/'.$option.'/helpers/sp_structur/'.$type.'.xml');        
            }
            else
            {
//                $xml = JFactory::getXMLParser( 'Simple' );
//    $xml->loadFile(JPATH_ADMINISTRATOR.'/components/'.$option.'/helpers/sp_structur/'.$type.'.xml');
    $xml = JFactory::getXML(JPATH_ADMINISTRATOR.'/components/'.$option.'/helpers/sp_structur/'.$type.'.xml');
            }
            
    if (!JFile::exists(JPATH_ADMINISTRATOR.'/components/'.$option.'/helpers/sp_structur/'.$type.'.xml')) 
    {
        return false;
    }    
    //$xml = JFactory::getXML(JPATH_ADMINISTRATOR.'/components/'.$option.'/helpers/sp_structur/'.$type.'.xml');
    //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml<br><pre>'.print_r($xml,true).'</pre>'),'Notice');
    
    
    // We can now step through each element of the file
 if ( isset($xml->events) )
    {    
foreach( $xml->events as $event ) 
{
   //$name = $event->getElementByPath('name');
//   $attributes = $name->attributes();
   
   //$main = (string)$association->assocname->attributes()->main;
   
   //$icon = $event->getElementByPath('icon');
   //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml name<br><pre>'.print_r($name->data(),true).'</pre>'),'Notice');
   //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml icon<br><pre>'.print_r($icon->data(),true).'</pre>'),'Notice');
   
   $temp = new stdClass();
   $temp->name = strtoupper($option).'_'.strtoupper($type).'_E_'.strtoupper((string)$event->name);
    $temp->icon = 'images/'.$option.'/database/events/'.$type.'/'.strtolower((string)$event->name->attributes()->icon );
    $export[] = $temp;
    $this->_sport_types_events[$type] = array_merge($export);
   }
    }    
   //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool createSportTypeArray _sport_types_events<br><pre>'.print_r($this->_sport_types_events,true).'</pre>'),'Notice'); 
   //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml parent mainpositions<br><pre>'.print_r($xml->document->mainpositions,true).'</pre>'),'Notice');
   
   unset ($export); 
   
   
 if ( isset($xml->mainpositions) )
    {   
    foreach( $xml->mainpositions as $position ) 
{
   //$name = $position->getElementByPath('mainname');
//   $attributes = $name->attributes();
   
   //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml mainpositions<br><pre>'.print_r($name,true).'</pre>'),'Notice');
   
//   $switch = $position->getElementByPath('mainswitch');
//   $parent = $position->getElementByPath('mainparent');
//   $content = $position->getElementByPath('maincontent');
   //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml name<br><pre>'.print_r($name->data(),true).'</pre>'),'Notice');
   //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml icon<br><pre>'.print_r($icon->data(),true).'</pre>'),'Notice');
   
   
        $temp = new stdClass();
        $temp->name = strtoupper($option).'_'.strtoupper($type).'_F_'.strtoupper((string)$position->mainname);
        $temp->switch = strtolower((string)$position->mainname->attributes()->switch);
        $temp->parent = (string)$position->mainname->attributes()->parent ;
        $temp->content = (string)$position->mainname->attributes()->content;
        $export[] = $temp;
        $this->_sport_types_position[$type] = array_merge($export);
   }
    }  
    //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool createSportTypeArray _sport_types_position<br><pre>'.print_r($this->_sport_types_position,true).'</pre>'),'Notice');
    
    
    //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml parent parentpositions<br><pre>'.print_r($xml->document->parentpositions,true).'</pre>'),'Notice');
    
    unset ($export); 
    if ( isset($xml->parentpositions) )
    {
    foreach( $xml->parentpositions as $parent ) 
{
   //$name = $parent->getElementByPath('parentname');
//   $attributes = $name->attributes();
   
   
   //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml parent parentname<br><pre>'.print_r($name,true).'</pre>'),'Notice');
   //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml parent attributes<br><pre>'.print_r($name->attributes(),true).'</pre>'),'Notice');
   
//   $switch = $parent->getElementByPath('parentswitch');
//   $parent = $parent->getElementByPath('parentparent');
//   $content = $parent->getElementByPath('parentcontent');
//   $mainparentposition = $parent->getElementByPath('mainparentposition');
   
//   $mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml parent name<br><pre>'.print_r($name->data(),true).'</pre>'),'Notice');
//   $mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml parent switch<br><pre>'.print_r($switch->data(),true).'</pre>'),'Notice');
//   $mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml parent parent<br><pre>'.print_r($parent->data(),true).'</pre>'),'Notice');
//   $mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml parent content<br><pre>'.print_r($content->data(),true).'</pre>'),'Notice');
//   $mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml parent mainparentposition<br><pre>'.print_r($mainparentposition->data(),true).'</pre>'),'Notice');
   
//   $mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml parent name<br><pre>'.print_r($name,true).'</pre>'),'Notice');
//   $mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml parent switch<br><pre>'.print_r($switch,true).'</pre>'),'Notice');
//   $mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml parent parent<br><pre>'.print_r($parent,true).'</pre>'),'Notice');
//   $mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml parent content<br><pre>'.print_r($content,true).'</pre>'),'Notice');
//   $mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml parent mainparentposition<br><pre>'.print_r($mainparentposition,true).'</pre>'),'Notice');
   
  
        $temp = new stdClass();
        $temp->name = strtoupper($option).'_'.strtoupper($type).'_'.strtoupper((string)$parent->parentname->attributes()->art).'_'.strtoupper((string)$parent->parentname);
        $temp->switch = strtolower((string)$parent->parentname->attributes()->switch);
        $temp->parent = (string)$parent->parentname->attributes()->parent;
        $temp->content = (string)$parent->parentname->attributes()->content;
        $temp->events = (string)$parent->parentname->attributes()->events;
        //$export = array();
        $export[] = $temp;
        $this->_sport_types_position_parent[strtoupper($option).'_'.strtoupper($type).'_F_'.strtoupper((string)$parent->parentname->attributes()->main)] = array_merge($export);
        
   }
    
    }   
    
    
    
    
    
    //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool createSportTypeArray _sport_types_position_parent<br><pre>'.print_r($this->_sport_types_position_parent,true).'</pre>'),'Notice');
    
    return true;
    }
    
    
    /**
     * sportsmanagementModeldatabasetool::insertCountries()
     * 
     * @return
     */
    function insertCountries()
    {
    $mainframe = JFactory::getApplication();
    $option = JRequest::getCmd('option');
    require_once( JPATH_ADMINISTRATOR.'/components/'.$option.'/'. 'helpers' . DS . 'jinstallationhelper.php' );    
    $db = JFactory::getDBO();
    $db_table = JPATH_ADMINISTRATOR.'/components/'.$option.'/sql/countries.sql';
// echo '<br>'.$db_table.'<br>';
// $fileContent = JFile::read($db_table);
// $sql_teil = explode(";",$fileContent);

    $cols = $db->getTableColumns('#__'.COM_SPORTSMANAGEMENT_TABLE.'_countries');
    if ( $cols )
    {
    $result = JInstallationHelper::populateDatabase($db, $db_table, $errors);
    if ( $result )
    {
    //$mainframe->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNTRIES_INSERT_ERROR'),'Error'); 
    $this->my_text = '<span style="color:'.$this->storeFailedColor.'"><strong>';
					$this->my_text .= JText::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNTRIES_INSERT_ERROR').'</strong></span><br />';
					
					
					//$this->_success_text['Altersgruppen:'] = $my_text;
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($this->my_text,true).'</pre>'),'');
        return $this->my_text;    
    }   
    else
    {
    //$mainframe->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNTRIES_INSERT_SUCCESS'),'');
    $this->my_text = '<span style="color:'.$this->storeSuccessColor.'"><strong>';
					$this->my_text .= JText::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNTRIES_INSERT_SUCCESS').'</strong></span><br />';
					
					
					//$this->_success_text['Altersgruppen:'] = $my_text;
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($this->my_text,true).'</pre>'),'');
        return $this->my_text;         
    }
    
    }
    else
    {
    $this->my_text = '<span style="color:'.$this->storeFailedColor.'"><strong>';
					$this->my_text .= JText::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_JOOMLEAGUE_COUNTRIES_INSERT_ERROR').'</strong></span><br />';
        return $this->my_text;     
    } 
    //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool insertCountries result<br><pre>'.print_r($result,true).'</pre>'),'Notice');    
    
    }
    
    /**
     * sportsmanagementModeldatabasetool::insertSportType()
     * 
     * @param mixed $type
     * @return
     */
    function insertSportType($type)
    {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $db = sportsmanagementHelper::getDBConnection(FALSE,FALSE);
        //$mainframe->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_SPORT_TYPE_INSERT',strtoupper($type)),'Notice');
        
        //self::createSportTypeArray();
        $available = self::checkSportTypeStructur($type);
        
        if ( !$available )
        {
            //$mainframe->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_SPORT_TYPE_INSERT_XML_ERROR',strtoupper($type)),'Error');
            $this->my_text = '<span style="color:'.$this->storeFailedColor.'"><strong>';
            $this->my_text .= JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_SPORT_TYPE_INSERT_XML_ERROR',strtoupper($type)).'</strong></span><br />';
            return false;
        }
        
       $query = "SELECT id FROM #__".COM_SPORTSMANAGEMENT_TABLE."_sports_type"." WHERE name='"."COM_SPORTSMANAGEMENT_ST_".strtoupper($type)."' ";
       $db->setQuery($query);
       $result = $db->loadResult();
       if ( $result )
       {
       //$mainframe->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_SPORT_TYPE_AVAILABLE',strtoupper($type)),'Notice');
     // $mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool insertSportType result<br><pre>'.print_r($result,true).'</pre>'),'Notice'); 
      $sports_type_id = $result;
        $sports_type_name = 'COM_SPORTSMANAGEMENT_ST_'.strtoupper($type);
      self::addStandardForSportType($sports_type_name, $sports_type_id, $type,$update=1);
      
       }
       else
       { 
        //// Get a db connection.
//        $db = JFactory::getDbo();
        // Create a new query object.
        $query = $db->getQuery(true);
        // Insert columns.
        $columns = array('name','icon');
        // Insert values.
        $values = array('\''.'COM_SPORTSMANAGEMENT_ST_'.strtoupper($type).'\'','\''.'images/com_sportsmanagement/database/placeholders/placeholder_21.png'.'\'');
        // Prepare the insert query.
        $query
            ->insert($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
        // Set the query using our newly populated query object and execute it.
        $db->setQuery($query);
        
        if (!$db->query())
		{
			
            $mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool insertSportType<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
			$result = false;
		}
        else
        {
        //$mainframe->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_SPORT_TYPE_INSERT_SUCCESS',strtoupper($type)),'Notice');
        $this->my_text .= '<span style="color:'.$this->storeSuccessColor.'"><strong>';
		$this->my_text .= JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_SPORT_TYPE_INSERT_SUCCESS',strtoupper($type)).'</strong></span><br />';
        
        $sports_type_id = $db->insertid();
        $sports_type_name = 'COM_SPORTSMANAGEMENT_ST_'.strtoupper($type);
        self::addStandardForSportType($sports_type_name, $sports_type_id, $type,$update=0);
        }
        }
        
        return $sports_type_id;
        //return $this->my_text;
    }
    
    
    /**
     * sportsmanagementModeldatabasetool::addStandardForSportType()
     * 
     * @param mixed $name
     * @param mixed $id
     * @param mixed $type
     * @param integer $update
     * @return void
     */
    function addStandardForSportType($name, $id, $type,$update=0)
{
    $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Get a db connection.
        //$db = JFactory::getDbo();
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool addStandardForSportType name<br><pre>'.print_r($name,true).'</pre>'),'Notice');
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool addStandardForSportType id<br><pre>'.print_r($id,true).'</pre>'),'Notice');
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool addStandardForSportType events<br><pre>'.print_r($this->_sport_types_events[$type],true).'</pre>'),'Notice');
        
	$events_player		= array();
	$events_staff		= array();
	$events_referees	= array();
	$events_clubstaff	= array();
	$PlayersPositions	= array();
	$StaffPositions		= array();
	$RefereePositions	= array();
	$ClubStaffPositions	= array();

	$result				= false;
	
    // insert events
    $i = 0;
    if ( isset($this->_sport_types_events[$type]) )
    {
    foreach ( $this->_sport_types_events[$type] as $event )
    {
        $query = self::build_SelectQuery('eventtype',$event->name,$id); 
        JFactory::getDbo()->setQuery($query);
        
        if ( !$object = JFactory::getDbo()->loadObject() )
		{
        $query				= self::build_InsertQuery_Event('eventtype',$event->name,$event->icon,$id,2);
         JFactory::getDbo()->setQuery($query);
			$result				= JFactory::getDbo()->query();
			$events_player[$i]	= JFactory::getDbo()->insertid();
			$events_staff[$i]		= JFactory::getDbo()->insertid();
			$events_clubstaff[$i]	= JFactory::getDbo()->insertid();
			$events_referees[$i]	= JFactory::getDbo()->insertid();
            $event->tableid = JFactory::getDbo()->insertid();
            }
            else
		{
		  $events_player[$i]	= $object->id;
			$events_staff[$i]		= $object->id;
			$events_clubstaff[$i]	= $object->id;
			$events_referees[$i]	= $object->id;
			$event->tableid = $object->id;
		}
        
        if ( !$update )
        {
        //$mainframe->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_EVENTS_INSERT_SUCCESS',$event->name),'Notice');
        $this->my_text .= '<span style="color:'.$this->storeSuccessColor.'"><strong>';
		$this->my_text .= JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_EVENTS_INSERT_SUCCESS',$event->name).'</strong></span><br />';
        }
        $i++;
    }
}
    // standardpositionen einf�gen
    $i = 0;
    $j = 0;
    if ( isset($this->_sport_types_position[$type]) )
    {
    foreach ( $this->_sport_types_position[$type] as $position )
    {
    $query = self::build_SelectQuery('position',$position->name,$id); 
    JFactory::getDbo()->setQuery($query);    
    if (!$dbresult=JFactory::getDbo()->loadObject())
			{
				$query					= self::build_InsertQuery_Position('position',$position->name,$position->switch,$position->parent,$position->content,$id,1); 
                JFactory::getDbo()->setQuery($query);
				$result					= JFactory::getDbo()->query();
				$ParentID				= JFactory::getDbo()->insertid();
				$PlayersPositions[$i]	= JFactory::getDbo()->insertid();
			}
			else
			{
				$ParentID				= $dbresult->id;
				$PlayersPositions[$i]	= $dbresult->id;
			}
    
   if ( !$update )
        {
    //$mainframe->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_POSITION_INSERT_SUCCESS',$position->name),'Notice');
    $this->my_text .= '<span style="color:'.$this->storeSuccessColor.'"><strong>';
		$this->my_text .= JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_POSITION_INSERT_SUCCESS',$position->name).'</strong></span><br />';
    }
    
    // parent position
    if ( isset($this->_sport_types_position_parent[$position->name])  )
    {
        foreach ( $this->_sport_types_position_parent[$position->name] as $parent )
        {
            $query = self::build_SelectQuery('position',$parent->name,$id); 
            JFactory::getDbo()->setQuery($query);
            if (!$object=JFactory::getDbo()->loadObject())
				{
					$query					= self::build_InsertQuery_Position('position',$parent->name,$parent->switch,$ParentID,$parent->content,$id,2); 
                    JFactory::getDbo()->setQuery($query);
					$result					= self::runJoomlaQuery();
					$PlayersPositions[$j]	= JFactory::getDbo()->insertid();
                    $parent->tableid = JFactory::getDbo()->insertid();
				}
				else
				{
					$PlayersPositions[$j]	= $object->id;
                    $parent->tableid = $object->id;
				}
                
                if ( $parent->events )
                {
//                foreach ( $this->_sport_types_events[$type] as $event )
//                {
//                $query	= self::build_InsertQuery_PositionEventType($parent->tableid,$event->tableid);
//                JFactory::getDbo()->setQuery($query);
//				$result = self::runJoomlaQuery(); 
//                if ( !$result )
//                {
//                
//                }
//                else
//                {    
//                if ( !$update )
//                {
//                if ( $result )
//                {
//                    //$mainframe->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_PARENT_POSITION_INSERT_EVENT_SUCCESS',$event->name),'Notice');
//                    $this->my_text .= '<span style="color:'.$this->storeSuccessColor.'"><strong>';
//		$this->my_text .= JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_PARENT_POSITION_INSERT_EVENT_SUCCESS',$event->name).'</strong></span><br />';
//                }   
//                else
//                {
//                    //$mainframe->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_PARENT_POSITION_INSERT_EVENT_ERROR',$event->name),'Error');
//                }
//                }
//                }
//                }
                
                }
                
            $j++;
        
        if ( !$update )
        {    
        //$mainframe->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_PARENT_POSITION_INSERT_SUCCESS',$parent->name),'Notice');    
        $this->my_text .= '<span style="color:'.$this->storeSuccessColor.'"><strong>';
		$this->my_text .= JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_PARENT_POSITION_INSERT_SUCCESS',$parent->name).'</strong></span><br />';
         }
            
        }    
    }
    
    
    
    
    $i++;
    }
    }
    
    }
    
    /**
     * sportsmanagementModeldatabasetool::build_SelectQuery()
     * 
     * @param mixed $tablename
     * @param mixed $param1
     * @param integer $st_id
     * @return
     */
    function build_SelectQuery($tablename,$param1,$st_id = 0)
{
	$query="SELECT * FROM #__".COM_SPORTSMANAGEMENT_TABLE."_".$tablename." WHERE name='".$param1."' and sports_type_id = ".$st_id." ";
	return $query;
}

/**
 * sportsmanagementModeldatabasetool::build_InsertQuery_Position()
 * 
 * @param mixed $tablename
 * @param mixed $param1
 * @param mixed $param2
 * @param mixed $param3
 * @param mixed $param4
 * @param mixed $sports_type_id
 * @param mixed $order_count
 * @return
 */
function build_InsertQuery_Position($tablename,$param1,$param2,$param3,$param4,$sports_type_id,$order_count)
{
	$alias=JFilterOutput::stringURLSafe($param1);
	$query="INSERT INTO #__".COM_SPORTSMANAGEMENT_TABLE."_".$tablename." (`name`,`alias`,`".$param2."`,`parent_id`,`sports_type_id`,`published`,`ordering`) VALUES ('".$param1."','".$alias."','".$param4."','".$param3."','".$sports_type_id."','1','".$order_count."')";
	return $query;
}

/**
 * sportsmanagementModeldatabasetool::build_InsertQuery_Event()
 * 
 * @param mixed $tablename
 * @param mixed $param1
 * @param mixed $param2
 * @param mixed $sports_type_id
 * @param mixed $order_count
 * @return
 */
function build_InsertQuery_Event($tablename,$param1,$param2,$sports_type_id,$order_count)
{
	$alias=JFilterOutput::stringURLSafe($param1);
	$query="INSERT INTO #__".COM_SPORTSMANAGEMENT_TABLE."_".$tablename." (`name`,`alias`,`icon`,`sports_type_id`,`published`,`ordering`) VALUES ('".$param1."','".$alias."','".$param2."','".$sports_type_id."','1','".$order_count."')";
	return $query;
}

/**
 * sportsmanagementModeldatabasetool::build_InsertQuery_PositionEventType()
 * 
 * @param mixed $param1
 * @param mixed $param2
 * @return
 */
function build_InsertQuery_PositionEventType($param1,$param2)
{
	$query="	INSERT INTO	#__".COM_SPORTSMANAGEMENT_TABLE."_position_eventtype
				(`position_id`,`eventtype_id`)
				VALUES
				('".$param1."','".$param2."')";
	return $query;
}


/**
 * sportsmanagementModeldatabasetool::writeErrorLog()
 * 
 * @param mixed $class
 * @param mixed $function
 * @param mixed $file
 * @param mixed $text
 * @param mixed $line
 * @return void
 */
public static function writeErrorLog($class, $function, $file, $text, $line)
{
$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Get a db connection.
        $db = JFactory::getDbo();   
        $date = date("Y-m-d");
        $time = date("H:i:s");
        
        //$path_parts = pathinfo($file); 
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($path_parts,true).'</pre>'),'');
        
        $file = str_replace("\\", "\\\\", $file);
        
$insertquery = $db->getQuery(true);
                // Insert columns.
                $columns = array('date','time','class','file','text','function','line');
                // Insert values.
                $values = array('\''.$date.'\'','\''.$time.'\'','\''.$class.'\'','"'.$file.'"','"'.$text.'"','\''.$function.'\'','\''.$line.'\'');
                // Prepare the insert query.
                $insertquery
                ->insert($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_error_log'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));
                // Set the query using our newly populated query object and execute it.
                $db->setQuery($insertquery);
                
                //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($file,true).'</pre>'),'');
                //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($insertquery,true).'</pre>'),'');
                
	            if (!$db->query())
			    {
			    $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error'); 
			    }
			    else
			    {
			     
			    }     
}   

 

}
