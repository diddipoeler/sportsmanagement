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
 
// import Joomla modelform library
jimport('joomla.application.component.model');
 

/**
 * sportsmanagementModelcpanel
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementModelcpanel extends JModel
{

var $_success_text = '';
	var $storeFailedColor = 'red';
	var $storeSuccessColor = 'green';
	var $existingInDbColor = 'orange';

public function getVersion() 
	{
	   $mainframe = JFactory::getApplication();
       $option = JRequest::getCmd('option');
	   $this->_db->setQuery('SELECT manifest_cache FROM #__extensions WHERE name = "com_sportsmanagement"');
       $manifest_cache = json_decode( $this->_db->loadResult(), true );
	   //$mainframe->enqueueMessage(JText::_('manifest_cache<br><pre>'.print_r($manifest_cache,true).'</pre>'   ),'');
       return $manifest_cache['version'];	
	}

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
    
    function checkUpdateVersion()
    {
        $mainframe = JFactory::getApplication(); 
        $option = JRequest::getCmd('option');  
        $xml = JFactory::getXMLParser( 'Simple' );
        $return = true;
        $version = sportsmanagementHelper::getVersion();
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($version,true).'</pre>'),'');
                
        //Laden
		$content = file_get_contents('https://raw2.github.com/diddipoeler/sportsmanagement/master/sportsmanagement.xml');
		//Parsen
		$doc = DOMDocument::loadXML($content);
        $doc->save(JPATH_SITE.DS.'tmp'.DS.'sportsmanagement.xml');
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($doc,true).'</pre>'),'');
        
        $xml->loadFile(JPATH_SITE.DS.'tmp'.DS.'sportsmanagement.xml');
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($xml,true).'</pre>'),'');
        foreach( $xml->document->version as $version ) 
            {
            $github_version = $version->data();
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($github_version,true).'</pre>'),'');
            }
            
            if ( $github_version != $version )
            {
                $return =  false;
            }
            else
            {
                $return =  true;
            }
            
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' return<br><pre>'.print_r($return,true).'</pre>'),'');
            
            return $return;
                    
    }
    
    function checkcountry()
    {
        $query='SELECT count(*) AS count
		FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_countries';
		$this->_db->setQuery($query);
		return $this->_db->loadResult();
    }
    
    function checksporttype($type)
    {
        $type = strtoupper($type);
        $query="SELECT count(*) AS count
		FROM #__".COM_SPORTSMANAGEMENT_TABLE."_sports_type where name LIKE '%".$type."%' ";
		$this->_db->setQuery($query);
		return $this->_db->loadResult();
        
        
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
		// Initialize
		$github = array();
		$i = 0;

		// Load the parameters
        $uname = JComponentHelper::getParams('com_sportsmanagement')->get('cfg_github_username','');
        $repo = JComponentHelper::getParams('com_sportsmanagement')->get('cfg_github_repository','');
		//$uname		= $params->get('cfg_github_username', '');
		//$repo		= $params->get('cfg_github_repository', '');
		$count		= 15;

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
				// Initialize a new object
                //$github[$i]->commit	= '';
				$github[$i]->commit	= new stdClass;

				// The commit message linked to the commit
				$github[$i]->commit->message = '<a href="https://github.com/'.$uname.'/'.$frepo.'/commit/'.$o['sha'].'" target="_blank" rel="nofollow">'.substr($o['sha'], 0, 7).'</a> - ';
				$github[$i]->commit->message .= preg_replace("/#(\w+)/", '#<a href="https://github.com/'.$uname.'/'.$frepo.'/issues/\\1" target="_blank" rel="nofollow">\\1</a>', htmlspecialchars($o['commit']['message']));

				// Check if the committer information
				if ($o['author']['id'] != $o['committer']['id'])
				{
					// The committer name formatted with link
					$github[$i]->commit->committer	= JText::_('COM_SPORTSMANAGEMENT_GITHUB_AND_COMMITTED_BY').'<a href="https://github.com/'.$o['committer']['login'].'" target="_blank" rel="nofollow">'.$o['commit']['committer']['name'].'</a>';

					// The author wasn't the committer
					$github[$i]->commit->author		= JText::_('COM_SPORTSMANAGEMENT_GITHUB_AUTHORED_BY');
				}
				else
				{
					// The author is also the committer
					$github[$i]->commit->author		= JText::_('COM_SPORTSMANAGEMENT_GITHUB_COMMITTED_BY');
				}

				// The author name formatted with link
				$github[$i]->commit->author .= '<a href="https://github.com/'.$o['author']['login'].'" target="_blank" rel="nofollow">'.$o['commit']['author']['name'].'</a>';

				// The time of commit
				$date = date_create($o['commit']['committer']['date']);
				$date = date_format($date, 'r');
				if ($params->get('relativeTime', '1') == '1')
				{
					$ISOtime = JHtml::date($date, 'Y-m-d H:i:s');

					// Load the JavaScript; first ensure we have MooTools Core
					JHtml::_('behavior.framework');
					JHtml::script(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/assets/js/prettydate.js', false, false);
					$github[$i]->commit->time = ' <span class="commit-time" title="'.$ISOtime.'">'.JHtml::date($date, 'D M d H:i:s O Y').'</span>';
				}
				else
				{
					$github[$i]->commit->time = ' '.JHtml::date($date);
				}

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