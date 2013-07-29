<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
jimport('joomla.application.component.model');
 
/**
 * 
 */
class sportsmanagementModelcpanel extends JModel
{

public function getVersion() 
	{
	   $mainframe =& JFactory::getApplication();
	   $this->_db->setQuery('SELECT manifest_cache FROM #__extensions WHERE name = "com_sportsmanagement"');
       $manifest_cache = json_decode( $this->_db->loadResult(), true );
	   //$mainframe->enqueueMessage(JText::_('manifest_cache<br><pre>'.print_r($manifest_cache,true).'</pre>'   ),'');
       return $manifest_cache['version'];	
	}

public function getGithubRequests()
{
$mainframe =& JFactory::getApplication();    
$paramsdata = JComponentHelper::getParams('com_sportsmanagement');
//$mainframe->enqueueMessage(JText::_('getGithubRequests paramsdata<br><pre>'.print_r($paramsdata,true).'</pre>'   ),'');


// Load the parameters
        $uname = JComponentHelper::getParams('com_sportsmanagement')->get('cfg_github_username','diddipoeler');
        $repo = JComponentHelper::getParams('com_sportsmanagement')->get('cfg_github_repository','sportsmanagement');
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
					$ISOtime = JHTML::date($date, 'Y-m-d H:i:s');

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