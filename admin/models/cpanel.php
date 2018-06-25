<?php

/** Joomla Sports Management ein Programm zur Verwaltung für alle Sportarten
 * @version 1.0.26
 * @file		components/sportsmanagement/models/cpanel.php
 * @author diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

// import Joomla modelform library
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
class sportsmanagementModelcpanel extends JSMModelLegacy {

    var $_success_text;
    var $storeFailedColor = 'red';
    var $storeSuccessColor = 'green';
    var $existingInDbColor = 'orange';

    /**
     * sportsmanagementModelcpanel::getVersion()
     * 
     * @return
     */
    public function getVersion() {
        //$app = JFactory::getApplication();
//       $option = JFactory::getApplication()->input->getCmd('option');
//       // Create a new query object.		
//		$db = sportsmanagementHelper::getDBConnection();
//		$query = $db->getQuery(true);

        $this->jsmdb->setQuery('SELECT manifest_cache FROM #__extensions WHERE name = "com_sportsmanagement"');
        $manifest_cache = json_decode($this->jsmdb->loadResult(), true);
        //$app->enqueueMessage(JText::_('manifest_cache<br><pre>'.print_r($manifest_cache,true).'</pre>'   ),'');
        return $manifest_cache['version'];
    }

    /**
     * sportsmanagementModelcpanel::getGithubRequests()
     * 
     * @return
     */
    public function getGithubRequests() {
        $app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        $paramsdata = JComponentHelper::getParams($option);
//$app->enqueueMessage(JText::_('getGithubRequests paramsdata<br><pre>'.print_r($paramsdata,true).'</pre>'   ),'');
// Load the parameters
        $uname = JComponentHelper::getParams($option)->get('cfg_github_username', 'diddipoeler');
        $repo = JComponentHelper::getParams($option)->get('cfg_github_repository', 'sportsmanagement');
        //$uname		= $paramsdata->get('cfg_github_username', '');
        //$repo		= $paramsdata->get('cfg_github_repository', '');
        // Convert the list name to a useable string for the JSON
        if ($repo) {
            $frepo = self::toAscii($repo);
        }

        // Initialize the array
        $github = array();

        $req = 'https://api.github.com/repos/' . $uname . '/' . $frepo . '/commits';

        // Fetch the decoded JSON
        $obj = self::getJSON($req);

        if (is_null($obj)) {
            $github->error = 'Error';
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
    function getInstalledPlugin($plugin) {
        //$app = JFactory::getApplication();
//  $option = JFactory::getApplication()->input->getCmd('option'); 
//  $db = sportsmanagementHelper::getDBConnection();    
//        $query = $db->getQuery(true);
        $this->jsmquery->clear();
        $this->jsmquery->select('a.extension_id');
        $this->jsmquery->from('#__extensions AS a');
        //$type = $db->Quote($type);
        $this->jsmquery->where("a.type LIKE 'plugin' ");
        $this->jsmquery->where("a.element LIKE '" . $plugin . "'");

        $this->jsmdb->setQuery($this->jsmquery);
        return $this->jsmdb->loadResult();
    }

    /**
     * sportsmanagementModelcpanel::checkcountry()
     * 
     * @return
     */
    function checkcountry() {
        //$app = JFactory::getApplication();
//        $option = JFactory::getApplication()->input->getCmd('option');
        $starttime = microtime();
        // Create a new query object.		
//		$db = sportsmanagementHelper::getDBConnection();
//		$query = $db->getQuery(true);
        //$cols = $this->_db->getTableColumns('#__'.COM_SPORTSMANAGEMENT_TABLE.'_countries');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($cols,true).'</pre>'),'');
//        $query='SELECT count(*) AS count
//		FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_countries';

        $this->jsmquery->clear();
        // Select some fields
        $this->jsmquery->select('count(*) AS count');
        // From the table
        $this->jsmquery->from('#__sportsmanagement_countries');

        $this->jsmdb->setQuery($this->jsmquery);

        if (COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO) {
            $app->enqueueMessage(JText::_(__METHOD__ . ' ' . __LINE__ . ' <br><pre>' . print_r($this->jsmquery->dump(), true) . '</pre>'), 'Notice');
            $app->enqueueMessage(JText::_(__METHOD__ . ' ' . __LINE__ . ' Ausfuehrungszeit query<br><pre>' . print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()), true) . '</pre>'), 'Notice');
        }

        return $this->jsmdb->loadResult();
    }

    /**
     * sportsmanagementModelcpanel::checksporttype()
     * 
     * @param mixed $type
     * @return
     */
    function checksporttype($type) {
        //$app = JFactory::getApplication();
        //$option = JFactory::getApplication()->input->getCmd('option');
        $starttime = microtime();
        // Create a new query object.		
        //	$db = sportsmanagementHelper::getDBConnection();
        //	$query = $db->getQuery(true);

        $type = strtoupper($type);

        // Select some fields
        $this->jsmquery->clear();
        $this->jsmquery->select('count(*) AS count');
        // From the table
        $this->jsmquery->from('#__sportsmanagement_sports_type');
        $this->jsmquery->where('name LIKE ' . $this->jsmdb->Quote('%' . $type . '%'));

        $this->jsmdb->setQuery($this->jsmquery);

        if (COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO) {
            $app->enqueueMessage(JText::_(__METHOD__ . ' ' . __LINE__ . ' <br><pre>' . print_r($this->jsmquery->dump(), true) . '</pre>'), 'Notice');
            $app->enqueueMessage(JText::_(__METHOD__ . ' ' . __LINE__ . ' Ausfuehrungszeit query<br><pre>' . print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()), true) . '</pre>'), 'Notice');
        }

        return $this->jsmdb->loadResult();
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
    static function getJSON($req) {
        // Create a new CURL resource
        $ch = curl_init($req);

        // Set options
        curl_setopt($ch, CURLOPT_HEADER, false);
        $t_vers = curl_version();
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/' . $t_vers['version']);
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
    static function processData($obj, $params) {
        $app = JFactory::getApplication();
        // Initialize
        $github = array();
        $i = 0;

        // Load the parameters
        $uname = JComponentHelper::getParams('com_sportsmanagement')->get('cfg_github_username', '');
        $repo = JComponentHelper::getParams('com_sportsmanagement')->get('cfg_github_repository', '');
        //$uname		= $params->get('cfg_github_username', '');
        //$repo		= $params->get('cfg_github_repository', '');
        $count = 15;

        // Convert the list name to a useable string for the JSON
        if ($repo) {
            $frepo = self::toAscii($repo);
        }

        // Process the feed
        foreach ($obj as $o) {
            if ($i <= $count) {

                //$app->enqueueMessage(get_class($this).' '.__FUNCTION__.' github<br><pre>'.print_r($github, true).'</pre><br>','Notice');
                // Initialize a new object
                //$github[] = '';
                $temp = new stdClass();
                $temp->commit = new stdClass;

                // The commit message linked to the commit
                $temp->commit->message = '<a href="https://github.com/' . $uname . '/' . $frepo . '/commit/' . $o['sha'] . '" target="_blank" rel="nofollow">' . substr($o['sha'], 0, 7) . '</a> - ';
                $temp->commit->message .= preg_replace("/#(\w+)/", '#<a href="https://github.com/' . $uname . '/' . $frepo . '/issues/\\1" target="_blank" rel="nofollow">\\1</a>', htmlspecialchars($o['commit']['message']));

                // Check if the committer information
                if ($o['author']['id'] != $o['committer']['id']) {
                    // The committer name formatted with link
                    $temp->commit->committer = JText::_('COM_SPORTSMANAGEMENT_GITHUB_AND_COMMITTED_BY') . '<a href="https://github.com/' . $o['committer']['login'] . '" target="_blank" rel="nofollow">' . $o['commit']['committer']['name'] . '</a>';

                    // The author wasn't the committer
                    $temp->commit->author = JText::_('COM_SPORTSMANAGEMENT_GITHUB_AUTHORED_BY');
                } else {
                    // The author is also the committer
                    $temp->commit->author = JText::_('COM_SPORTSMANAGEMENT_GITHUB_COMMITTED_BY');
                }

                // The author name formatted with link
                $temp->commit->author .= '<a href="https://github.com/' . $o['author']['login'] . '" target="_blank" rel="nofollow">' . $o['commit']['author']['name'] . '</a>';

                // The time of commit
                $date = date_create($o['commit']['committer']['date']);
                $date = date_format($date, 'r');
                if ($params->get('relativeTime', '1') == '1') {
                    $ISOtime = JHtml::date($date, 'Y-m-d H:i:s');

                    // Load the JavaScript; first ensure we have MooTools Core
                    JHtml::_('behavior.framework');
                    JHtml::script(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/assets/js/prettydate.js', false, false);
                    $temp->commit->time = ' <span class="commit-time" title="' . $ISOtime . '">' . JHtml::date($date, 'D M d H:i:s O Y') . '</span>';
                } else {
                    $temp->commit->time = ' ' . JHtml::date($date);
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
    static function toAscii($repo) {
        $clean = preg_replace("/[^a-z'A-Z0-9\/_|+ -]/", '', $repo);
        $clean = strtolower(trim($clean, '-'));
        $repo = preg_replace("/[\/_|+ -']+/", '-', $clean);

        return $repo;
    }

}
?>    
