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
defined( '_JEXEC' ) or die( 'Restricted access' );
$option = JRequest::getCmd('option');
$maxImportTime=JComponentHelper::getParams($option)->get('max_import_time',0);
if (empty($maxImportTime))
{
	$maxImportTime=480;
}
if ((int)ini_get('max_execution_time') < $maxImportTime){@set_time_limit($maxImportTime);}

$maxImportMemory=JComponentHelper::getParams($option)->get('max_import_memory',0);
if (empty($maxImportMemory))
{
	$maxImportMemory='350M';
}
if ((int)ini_get('memory_limit') < (int)$maxImportMemory){@ini_set('memory_limit',$maxImportMemory);}

//require_once( JPATH_COMPONENT_ADMINISTRATOR . DS. 'helpers' . DS . 'XMLParser.class.php' );
//require_once( JPATH_COMPONENT_ADMINISTRATOR . DS. 'helpers' . DS . 'crXml.php' );
require_once( JPATH_COMPONENT_ADMINISTRATOR . DS. 'helpers' . DS . 'SofeeXmlParser.php' );


jimport('joomla.application.component.model');
jimport('joomla.html.pane');
jimport('joomla.utilities.array');
jimport('joomla.utilities.arrayhelper') ;
// import JFile
jimport('joomla.filesystem.file');
jimport( 'joomla.utilities.utility' );
//require_once (JPATH_COMPONENT.DS.'models'.DS.'item.php');


class sportsmanagementModeljlextprofleagimport extends JModel
{
  var $_datas=array();
	var $_league_id=0;
	var $_season_id=0;
	var $_sportstype_id=0;
	var $import_version='';
  var $debug_info = false;

function __construct( )
	{
	   $option = JRequest::getCmd('option');
	$show_debug_info = JComponentHelper::getParams($option)->get('show_debug_info',0);
  if ( $show_debug_info )
  {
  $this->debug_info = true;
  }
  else
  {
  $this->debug_info = false;
  }

		parent::__construct( );
	
	}

private function dump_header($text)
	{
		echo "<h1>$text</h1>";
	}

	private function dump_variable($description, $variable)
	{
		echo "<b>$description</b><pre>".print_r($variable,true)."</pre>";
	}
    



	



 function _getXml()
	{
		if (JFile::exists(JPATH_SITE.DS.'tmp'.DS.'joomleague_import.xml'))
		{
			if (function_exists('simplexml_load_file'))
			{
				return @simplexml_load_file(JPATH_SITE.DS.'tmp'.DS.'joomleague_import.xml','SimpleXMLElement',LIBXML_NOCDATA);
			}
			else
			{
				JError::raiseWarning(500,JText::_('<a href="http://php.net/manual/en/book.simplexml.php" target="_blank">SimpleXML</a> does not exist on your system!'));
			}
		}
		else
		{
			JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_LMO_ERROR','Missing import file'));
			echo "<script> alert('".JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_LMO_ERROR','Missing import file')."'); window.history.go(-1); </script>\n";
		}
	}

/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.7
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_joomleague.'.$this->name, $this->name,
				array('load_data' => $loadData) );
		if (empty($form))
		{
			return false;
		}
		return $form;
	}

/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.7
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_joomleague.edit.'.$this->name.'.data', array());
		if (empty($data))
		{
			$data = $this->getData();
		}
		return $data;
	}

function xml2assoc($xml, array &$target = array()) { 
        while ($xml->read()) { 
            switch ($xml->nodeType) { 
                case XMLReader::END_ELEMENT: 
                    return $target; 
                case XMLReader::ELEMENT: 
                    $name = $xml->name; 
                    $target[$name] = $xml->hasAttributes ? array() : ''; 
                    if (!$xml->isEmptyElement) { 
                        $target[$name] = array(); 
                        self::xml2assoc($xml, $target[$name]); 
                    } 

                    if ($xml->hasAttributes) 
                        while($xml->moveToNextAttribute()) 
                            $target[$name]['@'.$xml->name] = $xml->value; 
                    break; 
                case XMLReader::TEXT: 
                case XMLReader::CDATA: 
                    $target = $xml->value; 
            } 
        } 
        return $target; 
    } 

        	
function getData()
	{
$option = JRequest::getCmd('option');

  $mainframe = JFactory::getApplication();
  $document	= JFactory::getDocument();
  
  $lang = JFactory::getLanguage();
  $teile = explode("-",$lang->getTag());
  
  $post = JRequest::get('post');
  $country = $post['country'];
  //$country = Countries::convertIso2to3($teile[1]);
  
  $mainframe->enqueueMessage(JText::_('land '.$country.''),'');
  
  $option = JRequest::getCmd('option');
	$project = $mainframe->getUserState( $option . 'project', 0 );
	
		
	
  
  $temp = new stdClass();
  $temp->id = 1;
  $temp->name = 'COM_SPORTSMANAGEMENT_ST_SOCCER';
  $this->_datas['sportstype'] = $temp;

$file = JPATH_SITE.DS.'tmp'.DS.'joomleague_import.xml';

/*
$xml = self::_getXml();
$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' xml<br><pre>'.print_r($xml,true).'</pre>'),'Notice');
*/

/*
$xml = new XMLReader(); 
$xml->open($file); 
$assoc = self::xml2assoc($xml); 
$xml->close(); 
$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' assoc<br><pre>'.print_r($assoc,true).'</pre>'),'Notice');
*/


$xml = new SofeeXmlParser(); 
$xml->parseFile($file); 
$tree = $xml->getTree(); 
unset($xml); 

$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' [tournament][title][value]<br><pre>'.print_r($tree[tournament][title][value],true).'</pre>'),'Notice');

$temp = new stdClass();
$temp->name = $tree[tournament][title][value];
$this->_datas['exportversion'] = $temp;
$temp = new stdClass();
$temp->name = $tree[tournament][season][value];
$this->_datas['season'] = $temp;
$temp = new stdClass();
$temp->name = $tree[tournament][title][value];
$this->_datas['league'] = $temp;
$temp = new stdClass();
$temp->name = $tree[tournament][title][value].' '.$tree[tournament][season][value];
$temp->project_type = 'SIMPLE_LEAGUE';
$this->_datas['project'] = $temp;


/*
$xml = JFactory::getXMLParser( 'Simple' );
$xml->loadFile($file);
//$xml = JFactory::getXML( $file );
//$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' xml<br><pre>'.print_r($xml,true).'</pre>'),'Notice');
*/

/*
$xml = new XMLParser;
$output = $xml->parse($file);
$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' xml<br><pre>'.print_r($output,true).'</pre>'),'Notice');    
*/


/*
$xml = new SofeeXmlParser(); 
$xml->parseFile($file); 
$tree = $xml->getTree(); 
$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' xml<br><pre>'.print_r($tree,true).'</pre>'),'Notice');
*/


/*
$content = utf8_encode(file_get_contents($file));
$xml = simplexml_load_string($content);
$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' source<br><pre>'.print_r($xml,true).'</pre>'),'Notice');
*/


//$convert = array (
//'Ä' => '&#196;',
//'Ö' => '&#214;',
//'Ü' => '&#220;',
//'ä' => '&#228;',
//'ö' => '&#246;',
//'ü' => '&#252;',
//'ß' => '&#223;'
//  );
  
//$source	= JFile::read($file);
//$source = str_replace(array_keys($convert), array_values($convert), $source  );
//$return = JFile::write($file, $source);
//$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' source<br><pre>'.print_r($source,true).'</pre>'),'Notice');


//$xml = JFactory::getXML( $file );


//$xml = JFactory::getXMLParser( 'Simple' );
//$xml->loadFile($file);

/*
$content = file_get_contents($file);
$content = str_replace(array_keys($convert), array_values($convert), $content  );
$xml = simplexml_load_string($content);

//Create a blank crXml object.
$crxml = new crxml;
//$xmlstr contains the xml String
$crxml->loadXML($xml);
//Outputs the XML document 
$crxml->xml();
*/        
//$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' xml<br><pre>'.print_r($tree,true).'</pre>'),'Notice');


/*
foreach( $xml->document->tournament as $tournament ) 
{
    $name = $tournament->getElementByPath('season');
    $temp = new stdClass();
  $temp->name = $name->data();
  $this->_datas['season'] = $temp;
}
*/

$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' season<br><pre>'.print_r($this->_datas['season'],true).'</pre>'),'Notice');



/**
 * das ganze für den standardimport aufbereiten
 */
$output = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
// open the project
$output .= "<project>\n";
// set the version of JoomLeague
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setJoomLeagueVersion());
// set the project datas
if ( isset($this->_datas['project']) )
{
$mainframe->enqueueMessage(JText::_('project daten '.'generiert'),'');    
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setProjectData($this->_datas['project']));
}
// set league data of project
if ( isset($this->_datas['league']) )
{
$mainframe->enqueueMessage(JText::_('league daten '.'generiert'),'');      
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setLeagueData($this->_datas['league']));
}
// set season data of project
if ( isset($this->_datas['season']) )
{
$mainframe->enqueueMessage(JText::_('season daten '.'generiert'),'');      
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setSeasonData($this->_datas['season']));
}

// set the rounds sportstype
if ( isset($this->_datas['sportstype']) )
{
$mainframe->enqueueMessage(JText::_('sportstype daten '.'generiert'),'');      
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setSportsType($this->_datas['sportstype']));    
}

// set the rounds data
if ( isset($this->_datas['round']) )
{
$mainframe->enqueueMessage(JText::_('round daten '.'generiert'),'');      
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['round'], 'Round') );
}
// set the teams data
if ( isset($this->_datas['team']) )
{
$mainframe->enqueueMessage(JText::_('team daten '.'generiert'),'');    
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['team'], 'JL_Team'));
}
// set the clubs data
if ( isset($this->_datas['club']) )
{
$mainframe->enqueueMessage(JText::_('club daten '.'generiert'),'');    
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['club'], 'Club'));
}
// set the matches data
if ( isset($this->_datas['match']) )
{
$mainframe->enqueueMessage(JText::_('match daten '.'generiert'),'');    
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['match'], 'Match'));
}
// set the positions data
if ( isset($this->_datas['position']) )
{
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['position'], 'Position'));
}
// set the positions parent data
if ( isset($this->_datas['parentposition']) )
{
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['parentposition'], 'ParentPosition'));
}
// set position data of project
if ( isset($this->_datas['projectposition']) )
{
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['projectposition'], 'ProjectPosition'));
}
// set the matchreferee data
if ( isset($this->_datas['matchreferee']) )
{
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['matchreferee'], 'MatchReferee'));
}
// set the person data
if ( isset($this->_datas['person']) )
{
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['person'], 'Person'));
}
// set the projectreferee data
if ( isset($this->_datas['projectreferee']) )
{
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['projectreferee'], 'ProjectReferee'));
}
// set the projectteam data
if ( isset($this->_datas['projectteam']) )
{
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['projectteam'], 'ProjectTeam'));
}
// set playground data of project
if ( isset($this->_datas['playground']) )
{
$output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['playground'], 'Playground'));
}            
            
// close the project
$output .= '</project>';
// mal als test
$xmlfile = $output;
$file = JPATH_SITE.DS.'tmp'.DS.'joomleague_import.jlg';
JFile::write($file, $xmlfile);


if ( $this->debug_info )
{
echo $this->pane->endPane();    
}
  
    $this->import_version='NEW';
    //$this->import_version='';
    return $this->_datas;
    
}


    

    
    



    

    

        

    

    





	














    
          	
function _loadData()
	{
  global $mainframe, $option;
  $this->_data =  $mainframe->getUserState( $option . 'project', 0 );
   
  return $this->_data;
	}

function _initData()
	{
	global $mainframe, $option;
  $this->_data =  $mainframe->getUserState( $option . 'project', 0 );
  return $this->_data;
	}




}


?>

