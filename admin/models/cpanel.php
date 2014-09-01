<?php
/** Joomla Sports Management ein Programm zur Verwaltung für alle Sportarten
* @version 1.0.26
* @file		components/sportsmanagement/models/cpanel.php
* @author diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* Joomla Sports Management ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* Joomla Sports Management wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
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
 
// import Joomla modelform library
//jimport('joomla.application.component.model');
jimport('joomla.application.component.modeladmin'); 

/**
 * sportsmanagementModelcpanel
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
//class sportsmanagementModelcpanel extends JModel
class sportsmanagementModelcpanel extends JModelLegacy
{

var $_success_text = '';
	var $storeFailedColor = 'red';
	var $storeSuccessColor = 'green';
	var $existingInDbColor = 'orange';

/**
 * sportsmanagementModelcpanel::getVersion()
 * 
 * @return
 */
public function getVersion() 
	{
	   $mainframe = JFactory::getApplication();
       $option = JRequest::getCmd('option');
       // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        
	   $this->_db->setQuery('SELECT manifest_cache FROM #__extensions WHERE name = "com_sportsmanagement"');
       $manifest_cache = json_decode( $this->_db->loadResult(), true );
	   //$mainframe->enqueueMessage(JText::_('manifest_cache<br><pre>'.print_r($manifest_cache,true).'</pre>'   ),'');
       return $manifest_cache['version'];	
	}

/**
 * sportsmanagementModelcpanel::getGithubRequests()
 * 
 * @return
 */
public function getGithubRequests()
{
$mainframe = JFactory::getApplication(); 
$option = JRequest::getCmd('option');   
$paramsdata = JComponentHelper::getParams($option);
//$mainframe->enqueueMessage(JText::_('getGithubRequests paramsdata<br><pre>'.print_r($paramsdata,true).'</pre>'   ),'');


// Load the parameters
        $uname = JComponentHelper::getParams($option)->get('cfg_github_username','diddipoeler');
        $repo = JComponentHelper::getParams($option)->get('cfg_github_repository','sportsmanagement');
		//$uname		= $paramsdata->get('cfg_github_username', '');
		//$repo		= $paramsdata->get('cfg_github_repository', '');

		// Convert the list name to a useable string for the JSON
		if ($repo)
		{
			$frepo	= self::toAscii($repo);
		}

		// Initialize the array
		$github	= array();

		$req = 'https://api.github.com/repos/'.$uname.'/'.$frepo.'/commits';

		// Fetch the decoded JSON
		$obj = self::getJSON($req);

		if (is_null($obj))
		{
			$github->error	= 'Error';
			return $github;
		}

		// Process the filtering options and render the feed
		$github = self::processData($obj, $paramsdata);

		return $github;
    
    }
    
    
    /**
     * sportsmanagementModelcpanel::getInstalledPlugin()
     * 
     * @param mixed $plugin
     * @return
     */
    function getInstalledPlugin($plugin)
    {
    $mainframe = JFactory::getApplication();
  $option = JRequest::getCmd('option'); 
  $db = JFactory::getDBO();    
        $query = $db->getQuery(true);
    $query->select('a.extension_id');
  $query->from('#__extensions AS a');
  //$type = $db->Quote($type);
	$query->where("a.type LIKE 'plugin' ");
    $query->where("a.element LIKE '".$plugin."'");
	
  $db->setQuery($query);
  return $db->loadResult();    
    }
    
    /**
     * sportsmanagementModelcpanel::checkUpdateVersion()
     * 
     * @return
     */
    function checkUpdateVersion()
    {
        $mainframe = JFactory::getApplication(); 
        $option = JRequest::getCmd('option');  
        //$xml = JFactory::getXMLParser( 'Simple' );
        $return = 0;
        $version = sportsmanagementHelper::getVersion() ;
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($version,true).'</pre>'),'');
        
        $temp = explode(".",$version);  
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' temp<br><pre>'.print_r($temp,true).'</pre>'),'');
     
              
        //Laden
        $datei = "https://raw2.github.com/diddipoeler/sportsmanagement/master/sportsmanagement.xml";
if (function_exists('curl_version'))
{
   $curl = curl_init();
    //Define header array for cURL requestes
    $header = array('Contect-Type:application/xml');
    curl_setopt($curl, CURLOPT_URL, $datei);
    curl_setopt($curl, CURLOPT_VERBOSE, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    //curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER , $header);
    
    
    if (curl_errno($curl)) 
    {
        // moving to display page to display curl errors
          //echo curl_errno($curl) ;
          //echo curl_error($curl);
          //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__. '<br><pre>'.print_r(curl_errno($curl),true).'</pre>'),'Error');
          //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__. '<br><pre>'.print_r(curl_error($curl),true).'</pre>'),'Error');
          
          
    }
    else
    {
        $content = curl_exec($curl);
        //print_r($content);
        curl_close($curl);
    } 
    
    //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__. '<br><pre>'.print_r($content,true).'</pre>'),'');
}
else if (file_get_contents(__FILE__) && ini_get('allow_url_fopen'))
{
    $content = file_get_contents($datei);
    //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.'<br><pre>'.print_r($content,true).'</pre>'),'');
}
else
{
    //echo 'Sie haben weder cURL installiert, noch allow_url_fopen aktiviert. Bitte aktivieren/installieren allow_url_fopen oder Curl!';
    $mainframe->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_ERROR_ALLOW_URL_FOPEN'),'Error');
}
		//$content = file_get_contents('https://raw2.github.com/diddipoeler/sportsmanagement/master/sportsmanagement.xml');
		//Parsen
        
        if ( $content )
        {
		$doc = DOMDocument::loadXML($content);
        $doc->save(JPATH_SITE.DS.'tmp'.DS.'sportsmanagement.xml');
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($doc,true).'</pre>'),'');
        }
        
        if(version_compare(JVERSION,'3.0.0','ge')) 
        {
        $xml = JFactory::getXML(JPATH_SITE.DS.'tmp'.DS.'sportsmanagement.xml');   
        //$xml = JFactory::getFeedParser(JPATH_SITE.DS.'tmp'.DS.'sportsmanagement.xml');
        //if (function_exists('simplexml_load_file'))
//			{
//				$xml =  @simplexml_load_file(JPATH_SITE.DS.'tmp'.DS.'sportsmanagement.xml','SimpleXMLElement',LIBXML_NOCDATA);
//			}  
        }
        else
        {
//        $xml = JFactory::getXMLParser( 'Simple' );
//        $xml->loadFile(JPATH_SITE.DS.'tmp'.DS.'sportsmanagement.xml');    
        $xml = JFactory::getXML(JPATH_SITE.DS.'tmp'.DS.'sportsmanagement.xml');
        }    

//        if ( COM_SPORTSMANAGEMENT_JOOMLAVERSION == '2.5' )
//        {
//        }
//        else
//        {
//        }
        
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' xml <br><pre>'.print_r($xml,true).'</pre>'),'');
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' xml version<br><pre>'.print_r((string)$xml->version,true).'</pre>'),'');
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' version<br><pre>'.print_r((string)$version,true).'</pre>'),'');
        
        $github_version = (string)$xml->version;
        
//        foreach( $xml->document->version as $version ) 
//            {
//            $github_version = $version->data();
//            //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($github_version,true).'</pre>'),'');
//            }
                     
            //$temp2 = explode(".",$github_version);  
            //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' temp2<br><pre>'.print_r($temp2,true).'</pre>'),'');
        
        if(version_compare($github_version,$version,'gt')) 
        {
        $return =  false;    
        }
        else
        {
        $return =  true;    
        }    
//            if ( $github_version !== $version )
//            {
//                $return =  false;
//            }
//            else
//            {
//                $return =  true;
//            }
            
//            foreach( $temp as $key => $value )
//            {
//            if ( (int)$temp[$key] !== (int)$temp2[$key] )
//            {
//                $return = $temp[$key];
//                //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' temp key<br><pre>'.print_r($temp[$key],true).'</pre>'),'');
//                break;
//            }    
//            }
            
            //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' return<br><pre>'.print_r($return,true).'</pre>'),'');
            
            //$anzahl = strcspn($github_version,$version);
            //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' anzahl<br><pre>'.print_r($anzahl,true).'</pre>'),'');
            
            //$return = strcmp (trim($github_version), trim($version));
            //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' return<br><pre>'.print_r($return,true).'</pre>'),'');
            
//            return $return;
                    
    }
    
    /**
     * sportsmanagementModelcpanel::checkcountry()
     * 
     * @return
     */
    function checkcountry()
    {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $starttime = microtime(); 
        // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        
        //$cols = $this->_db->getTableColumns('#__'.COM_SPORTSMANAGEMENT_TABLE.'_countries');
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($cols,true).'</pre>'),'');
//        $query='SELECT count(*) AS count
//		FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_countries';
		
        // Select some fields
		$query->select('count(*) AS count');
		// From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_countries');
        
        $db->setQuery($query);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		return $db->loadResult();
    }
    
    /**
     * sportsmanagementModelcpanel::checksporttype()
     * 
     * @param mixed $type
     * @return
     */
    function checksporttype($type)
    {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $starttime = microtime(); 
        // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        
        $type = strtoupper($type);
        
//        $query="SELECT count(*) AS count
//		FROM #__".COM_SPORTSMANAGEMENT_TABLE."_sports_type where name LIKE '%".$type."%' ";
		
        // Select some fields
		$query->select('count(*) AS count');
		// From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type');
        $query->where('name LIKE '.$db->Quote('%'.$type.'%'));
        
        $db->setQuery($query);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		return $db->loadResult();
        
        
    }

    /**
	 * Function to fetch a JSON feed
	 *
	 * @param   string  $req  The URL of the feed to load
	 *
	 * @return  array  The decoded JSON query
	 *
	 * @since	1.0
	 */
	static function getJSON($req)
	{
		// Create a new CURL resource
		$ch = curl_init($req);

		// Set options
		curl_setopt($ch, CURLOPT_HEADER, false);
        $t_vers = curl_version();
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/' . $t_vers['version'] );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		// Grab URL and pass it to the browser and store it as $json
		$json = curl_exec($ch);

		// Close CURL resource
		curl_close($ch);

		// Decode the fetched JSON
		$obj = json_decode($json, true);

		return $obj;
	}

/**
	 * Function to process the GitHub data into a formatted object
	 *
	 * @param   array   $obj     The JSON data
	 * @param   object  $params  The module parameters
	 *
	 * @return  array  An array of data for output
	 *
	 * @since   1.0
	 */
	static function processData($obj, $params)
	{
		$mainframe = JFactory::getApplication();
        // Initialize
		$github = array();
		$i = 0;

		// Load the parameters
        $uname = JComponentHelper::getParams('com_sportsmanagement')->get('cfg_github_username','');
        $repo = JComponentHelper::getParams('com_sportsmanagement')->get('cfg_github_repository','');
		//$uname		= $params->get('cfg_github_username', '');
		//$repo		= $params->get('cfg_github_repository', '');
		$count		= 15;
        
//        if ($i <= $count)
//			{
//			$github[]	= new stdClass; 
//            } 

		// Convert the list name to a useable string for the JSON
		if ($repo)
		{
			$frepo	= self::toAscii($repo);
		}

		// Process the feed
		foreach ($obj as $o)
		{
			if ($i <= $count)
			{
				
                //$mainframe->enqueueMessage(get_class($this).' '.__FUNCTION__.' github<br><pre>'.print_r($github, true).'</pre><br>','Notice');
                
                // Initialize a new object
                //$github[] = '';
                $temp = new stdClass();
				$temp->commit	= new stdClass;

				// The commit message linked to the commit
				$temp->commit->message = '<a href="https://github.com/'.$uname.'/'.$frepo.'/commit/'.$o['sha'].'" target="_blank" rel="nofollow">'.substr($o['sha'], 0, 7).'</a> - ';
				$temp->commit->message .= preg_replace("/#(\w+)/", '#<a href="https://github.com/'.$uname.'/'.$frepo.'/issues/\\1" target="_blank" rel="nofollow">\\1</a>', htmlspecialchars($o['commit']['message']));

				// Check if the committer information
				if ($o['author']['id'] != $o['committer']['id'])
				{
					// The committer name formatted with link
					$temp->commit->committer	= JText::_('COM_SPORTSMANAGEMENT_GITHUB_AND_COMMITTED_BY').'<a href="https://github.com/'.$o['committer']['login'].'" target="_blank" rel="nofollow">'.$o['commit']['committer']['name'].'</a>';

					// The author wasn't the committer
					$temp->commit->author		= JText::_('COM_SPORTSMANAGEMENT_GITHUB_AUTHORED_BY');
				}
				else
				{
					// The author is also the committer
					$temp->commit->author		= JText::_('COM_SPORTSMANAGEMENT_GITHUB_COMMITTED_BY');
				}

				// The author name formatted with link
				$temp->commit->author .= '<a href="https://github.com/'.$o['author']['login'].'" target="_blank" rel="nofollow">'.$o['commit']['author']['name'].'</a>';

				// The time of commit
				$date = date_create($o['commit']['committer']['date']);
				$date = date_format($date, 'r');
				if ($params->get('relativeTime', '1') == '1')
				{
					$ISOtime = JHtml::date($date, 'Y-m-d H:i:s');

					// Load the JavaScript; first ensure we have MooTools Core
					JHtml::_('behavior.framework');
					JHtml::script(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/assets/js/prettydate.js', false, false);
					$temp->commit->time = ' <span class="commit-time" title="'.$ISOtime.'">'.JHtml::date($date, 'D M d H:i:s O Y').'</span>';
				}
				else
				{
					$temp->commit->time = ' '.JHtml::date($date);
				}
                
                $github[] = $temp;

				$i++;
			}
		}
        
        
		return $github;
	}
    
/**
	 * Function to convert a formatted repo name into it's URL equivalent
	 *
	 * @param   string  $repo  The user inputted repo name
	 *
	 * @return  string  The repo name converted
	 *
	 * @since   1.0
	 */
	static function toAscii($repo)
	{
		$clean = preg_replace("/[^a-z'A-Z0-9\/_|+ -]/", '', $repo);
		$clean = strtolower(trim($clean, '-'));
		$repo  = preg_replace("/[\/_|+ -']+/", '-', $clean);

		return $repo;
	}    

    
}


?>    