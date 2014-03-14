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
     * sportsmanagementModeldatabasetool::getSportsManagementTables()
     * 
     * @return
     */
    function getSportsManagementTables()
    {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $query="SHOW TABLES LIKE '%_".COM_SPORTSMANAGEMENT_TABLE."%'";
		$this->_db->setQuery($query);
		return $this->_db->loadResultArray();
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
            $this->_db->setQuery($query);
            if (!$this->_db->query())
		{
			$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($this->_db->getErrorMsg(),true).'</pre>'),'Error');
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
        $db = JFactory::getDbo();   
        $xml = JFactory::getXMLParser( 'Simple' );
        
        foreach ( $sm_quotes as $key => $type )
        {
            $temp = explode(",",$type);
            
            $query='SELECT count(*) AS count
            FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_rquote where daily_number = '.$temp[1];
		    $this->_db->setQuery($query);
		    // sind zitate vorhanden ?
            if ( !$this->_db->loadResult() )
            {
            /* Ein JDatabaseQuery Objekt beziehen */
            $query = $db->getQuery(true);
            $query->delete()->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_rquote')->where('daily_number = '.$temp[1].''  );
            $db->setQuery($query);
            $result = $db->query();
    
            $xml->loadFile(JPATH_ADMINISTRATOR.'/components/'.$option.'/helpers/xml_files/quote_'.$temp[0].'.xml');
            foreach( $xml->document->version as $version ) 
            {
            $quote_version = $version->data();
            //$mainframe->enqueueMessage(JText::_('Zitate '.$temp[0].' Version : '.$quote_version.' wird installiert !'),'');
            $this->my_text .= '<span style="color:'.$this->storeSuccessColor.'"><strong>';
					$this->my_text .= JText::_('Installiere Zitate').'</strong></span><br />';
					$this->my_text .= JText::_('Zitate '.$temp[0].' Version : '.$quote_version.' wird installiert !').'<br />';
            }
            
            foreach( $xml->document->quotes as $quote ) 
            {
            $author = '';
            $zitat = '';
            $name = $quote->getElementByPath('quote');
            $attributes = $name->attributes();
            $author = $attributes['author'];
            $notes = $attributes['notes'];
            $author = str_replace("\\", "\\\\", $author);
            $zitat = $name->data();

            $insertquery = $db->getQuery(true);
            // Insert columns.
            $columns = array('daily_number','author','quote','notes');
            // Insert values.
            $values = array('\''.$temp[1].'\'','\''.$author.'\'','\''.$zitat.'\'','\''.$notes.'\'');
            // Prepare the insert query.
            $insertquery
            ->insert($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_rquote'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
            // Set the query using our newly populated query object and execute it.
            $db->setQuery($insertquery);
                
	        if (!$db->query())
			{
			self::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
            }
			else
			{
    	    } 
            
            
            
            }
            
            }
            else
            {
            
            $xml->loadFile(JPATH_ADMINISTRATOR.'/components/'.$option.'/helpers/xml_files/quote_'.$temp[0].'.xml');
            foreach( $xml->document->version as $version ) 
            {
            $quote_version = $version->data();
            }
            
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
    
    $mdl = JModel::getInstance("sportstype", "sportsmanagementModel");
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
                    
        $xml = JFactory::getXMLParser( 'Simple' );
       $xml->loadFile($filename); 
       
       // schleife altersgruppen anfang
       foreach( $xml->document->agegroups as $agegroup ) 
{
   $name = $agegroup->getElementByPath('agegroup');
   $attributes = $name->attributes();
   
   //$mainframe->enqueueMessage(JText::_(get_class($this).'<br><pre>'.print_r($name->data(),true).'</pre>'),'Notice');
   
   $agegroup = $name->data();
   $info = $attributes['info'];
   $picture = 'images/com_sportsmanagement/database/agegroups/'.$attributes['picture'];
   
   $query="SELECT id
            FROM #__".COM_SPORTSMANAGEMENT_TABLE."_agegroup where name like '".$agegroup."' and country like '".$search_nation."' and sportstype_id = ".$filter_sports_type;
		    $this->_db->setQuery($query);
		    // altersgruppe nicht vorhanden ?
            if ( !$this->_db->loadResult() )
            {
   // Get a db connection.
        $db = JFactory::getDbo();
        // Create a new query object.
        $query = $db->getQuery(true);
        // Insert columns.
        $columns = array('name','picture','info','sportstype_id','country');
        // Insert values.
        $values = array('\''.$agegroup.'\'','\''.$picture.'\'' ,'\''.$info.'\'' ,'\''.$filter_sports_type.'\'' ,'\''.$search_nation.'\''  );
        // Prepare the insert query.
        $query
            ->insert($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_agegroup'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
        // Set the query using our newly populated query object and execute it.
        $db->setQuery($query);
        
        if (!$db->query())
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
    $xml = JFactory::getXMLParser( 'Simple' );
    $xml->loadFile(JPATH_ADMINISTRATOR.'/components/'.$option.'/helpers/xml_files/associations.xml');
    
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
    $query = $db->getQuery(true);
    $query->delete()->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_associations')->where('country NOT IN ('.$country_assoc_del.')'  );
    $db->setQuery($query);
    $result = $db->query();
    
    //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'');
     
    $image_path = '/images/'.$option.'/database/associations/';
    
    foreach( $xml->document->associations as $association ) 
{
   $name = $association->getElementByPath('assocname');
   $attributes = $name->attributes();
   $country = $attributes['country'];
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
   $main = $attributes['main'];
   $parentmain = $attributes['parentmain'];
   
   $icon = $image_path.$attributes['icon'];
   $flag = $attributes['flag'];
   $website = $attributes['website'];
   $shortname = $attributes['shortname'];
   
   $assocname = $name->data();
   $query1 = "SELECT id FROM #__".COM_SPORTSMANAGEMENT_TABLE."_associations WHERE country LIKE '" . $country ."' and name LIKE '".$assocname."'";
    $this->_db->setQuery( $query1 );
	$result = $this->_db->loadResult();
   
    //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($query1,true).'</pre>'),'Notice');
   
   $export = array();
   if ( !$result )
   {
   if ( empty($parentmain) )
   {
   // Create a new query object.
                $insertquery = $db->getQuery(true);
                // Insert columns.
                $columns = array('country','name','picture','assocflag','website','short_name');
                // Insert values.
                $values = array('\''.$country.'\'','\''.$assocname.'\'','\''.$icon.'\'','\''.$flag.'\'','\''.$website.'\'','\''.$shortname.'\'');
                // Prepare the insert query.
                $insertquery
                ->insert($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_associations'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));
                // Set the query using our newly populated query object and execute it.
                $db->setQuery($insertquery);
                
	            if (!$db->query())
			    {
			    //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error'); 
			    self::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
                }
			    else
			    {
			    $temp = new stdClass();
                $temp->id = $db->insertid();
                $export[] = $temp;
                $this->_assoclist[$country][$main] = array_merge($export); 
			    } 
   }
   else
   {
   $parent_id = $this->_assoclist[$country][$parentmain];
   //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($parent_id,true).'</pre>'),'');
    // Create a new query object.
                $insertquery = $db->getQuery(true);
                // Insert columns.
                $columns = array('country','name','parent_id','picture','assocflag','website','short_name');
                // Insert values.
                $values = array('\''.$country.'\'','\''.$assocname.'\'',$parent_id[0]->id,'\''.$icon.'\'','\''.$flag.'\'','\''.$website.'\'','\''.$shortname.'\'');
                // Prepare the insert query.
                $insertquery
                ->insert($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_associations'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));
                // Set the query using our newly populated query object and execute it.
                $db->setQuery($insertquery);
                
	            if (!$db->query())
			    {
			    //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
                self::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__); 
			    }
			    else
			    {
			    $temp = new stdClass();
                $temp->id = $db->insertid();
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
                $query = $db->getQuery(true);
                $fields = array(
                $db->quoteName('picture') . '=' . '\''.$icon.'\''
                );
                // Conditions for which records should be updated.
                $conditions = array(
                $db->quoteName('id') . '=' . $result
                );
                $query->update($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_associations'))->set($fields)->where($conditions);
                $db->setQuery($query);
                $result = $db->query(); 
   
   
    
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
    
    $xml = JFactory::getXMLParser( 'Simple' );
    $xml->loadFile(JPATH_ADMINISTRATOR.'/components/'.$option.'/helpers/sp_structur/'.$type.'.xml');
    
    if (!JFile::exists(JPATH_ADMINISTRATOR.'/components/'.$option.'/helpers/sp_structur/'.$type.'.xml')) 
    {
        return false;
    }    
    //$xml = JFactory::getXML(JPATH_ADMINISTRATOR.'/components/'.$option.'/helpers/sp_structur/'.$type.'.xml');
    //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml<br><pre>'.print_r($xml,true).'</pre>'),'Notice');
    
    
    // We can now step through each element of the file
 if ( isset($xml->document->events) )
    {    
foreach( $xml->document->events as $event ) 
{
   $name = $event->getElementByPath('name');
   $attributes = $name->attributes();
   //$icon = $event->getElementByPath('icon');
   //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml name<br><pre>'.print_r($name->data(),true).'</pre>'),'Notice');
   //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml icon<br><pre>'.print_r($icon->data(),true).'</pre>'),'Notice');
   
   $temp = new stdClass();
   $temp->name = strtoupper($option).'_'.strtoupper($type).'_E_'.strtoupper($name->data());
    $temp->icon = 'images/'.$option.'/database/events/'.$type.'/'.strtolower($attributes['icon']);
    $export[] = $temp;
    $this->_sport_types_events[$type] = array_merge($export);
   }
    }    
   //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool createSportTypeArray _sport_types_events<br><pre>'.print_r($this->_sport_types_events,true).'</pre>'),'Notice'); 
   //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml parent mainpositions<br><pre>'.print_r($xml->document->mainpositions,true).'</pre>'),'Notice');
   
   unset ($export); 
 if ( isset($xml->document->mainpositions) )
    {   
    foreach( $xml->document->mainpositions as $position ) 
{
   $name = $position->getElementByPath('mainname');
   $attributes = $name->attributes();
   
   //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml mainpositions<br><pre>'.print_r($name,true).'</pre>'),'Notice');
   
//   $switch = $position->getElementByPath('mainswitch');
//   $parent = $position->getElementByPath('mainparent');
//   $content = $position->getElementByPath('maincontent');
   //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml name<br><pre>'.print_r($name->data(),true).'</pre>'),'Notice');
   //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml icon<br><pre>'.print_r($icon->data(),true).'</pre>'),'Notice');
   
   
        $temp = new stdClass();
        $temp->name = strtoupper($option).'_'.strtoupper($type).'_F_'.strtoupper($name->data());
        $temp->switch = strtolower($attributes['switch']);
        $temp->parent = $attributes['parent'];
        $temp->content = $attributes['content'];
        $export[] = $temp;
        $this->_sport_types_position[$type] = array_merge($export);
   }
    }  
    //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool createSportTypeArray _sport_types_position<br><pre>'.print_r($this->_sport_types_position,true).'</pre>'),'Notice');
    
    
    //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml parent parentpositions<br><pre>'.print_r($xml->document->parentpositions,true).'</pre>'),'Notice');
    
    unset ($export); 
    if ( isset($xml->document->parentpositions) )
    {
    foreach( $xml->document->parentpositions as $parent ) 
{
   $name = $parent->getElementByPath('parentname');
   $attributes = $name->attributes();
   
   
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
        $temp->name = strtoupper($option).'_'.strtoupper($type).'_'.strtoupper($attributes['art']).'_'.strtoupper($name->data());
        $temp->switch = strtolower($attributes['switch']);
        $temp->parent = $attributes['parent'];
        $temp->content = $attributes['content'];
        $temp->events = $attributes['events'];
        //$export = array();
        $export[] = $temp;
        $this->_sport_types_position_parent[strtoupper($option).'_'.strtoupper($type).'_F_'.strtoupper($attributes['main'])] = array_merge($export);
        
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
       $this->_db->setQuery($query);
       $result = $this->_db->loadResult();
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
        // Get a db connection.
        $db = JFactory::getDbo();
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
        $db = JFactory::getDbo();
        
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
        $query = self::build_SelectQuery('eventtypes',$event->name,$id); 
        $db->setQuery($query);
        
        if ( !$object = $db->loadObject() )
		{
        $query				= self::build_InsertQuery_Event('eventtype',$event->name,$event->icon,$id,2);
         $db->setQuery($query);
			$result				= $db->query();
			$events_player[$i]	= $db->insertid();
			$events_staff[$i]		= $db->insertid();
			$events_clubstaff[$i]	= $db->insertid();
			$events_referees[$i]	= $db->insertid();
            $event->tableid = $db->insertid();
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
    $db->setQuery($query);    
    if (!$dbresult=$db->loadObject())
			{
				$query					= self::build_InsertQuery_Position('position',$position->name,$position->switch,$position->parent,$position->content,$id,1); 
                $db->setQuery($query);
				$result					= $db->query();
				$ParentID				= $db->insertid();
				$PlayersPositions[$i]	= $db->insertid();
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
            $db->setQuery($query);
            if (!$object=$db->loadObject())
				{
					$query					= self::build_InsertQuery_Position('position',$parent->name,$parent->switch,$ParentID,$parent->content,$id,2); 
                    $db->setQuery($query);
					$result					= $db->query();
					$PlayersPositions[$j]	= $db->insertid();
                    $parent->tableid = $db->insertid();
				}
				else
				{
					$PlayersPositions[$j]	= $object->id;
                    $parent->tableid = $object->id;
				}
                
                if ( $parent->events )
                {
                foreach ( $this->_sport_types_events[$type] as $event )
                {
                $query	= self::build_InsertQuery_PositionEventType($parent->tableid,$event->tableid);
                $db->setQuery($query);
				$result = $db->query(); 
                if ( !$update )
        {
                if ( $result )
                {
                    //$mainframe->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_PARENT_POSITION_INSERT_EVENT_SUCCESS',$event->name),'Notice');
                    $this->my_text .= '<span style="color:'.$this->storeSuccessColor.'"><strong>';
		$this->my_text .= JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_PARENT_POSITION_INSERT_EVENT_SUCCESS',$event->name).'</strong></span><br />';
                }   
                else
                {
                    //$mainframe->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_PARENT_POSITION_INSERT_EVENT_ERROR',$event->name),'Error');
                }
                }
                }
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
function writeErrorLog($class, $function, $file, $text, $line)
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
