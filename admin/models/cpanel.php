<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       cpanel.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage cpanel
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementModelcpanel
 *
 * @package 
 * @author
 * @copyright diddi
 * @version   2013
 * @access    public
 */
class sportsmanagementModelcpanel extends JSMModelLegacy
{

    var $_success_text;
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
        $this->jsmdb->setQuery('SELECT manifest_cache FROM #__extensions WHERE name = "com_sportsmanagement"');
        $manifest_cache = json_decode($this->jsmdb->loadResult(), true);
        return $manifest_cache['version'];
    }

    /**
     * sportsmanagementModelcpanel::getGithubRequests()
     *
     * @return
     */
    public function getGithubRequests()
    {
        $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        $paramsdata = ComponentHelper::getParams($option);
        // Load the parameters
        $uname = ComponentHelper::getParams($option)->get('cfg_github_username', 'diddipoeler');
        $repo = ComponentHelper::getParams($option)->get('cfg_github_repository', 'sportsmanagement');
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
     * @param  mixed $plugin
     * @return
     */
    function getInstalledPlugin($plugin)
    {
        //$app = Factory::getApplication();
        //  $option = Factory::getApplication()->input->getCmd('option');
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
    function checkcountry()
    {
        $starttime = microtime();

        $this->jsmquery->clear();
        // Select some fields
        $this->jsmquery->select('count(*) AS count');
        // From the table
        $this->jsmquery->from('#__sportsmanagement_countries');
        $this->jsmdb->setQuery($this->jsmquery);

        return $this->jsmdb->loadResult();
    }

    /**
     * sportsmanagementModelcpanel::checksporttype()
     *
     * @param  mixed $type
     * @return
     */
    function checksporttype($type)
    {
        $starttime = microtime();
        $type = strtoupper($type);

        // Select some fields
        $this->jsmquery->clear();
        $this->jsmquery->select('count(*) AS count');
        // From the table
        $this->jsmquery->from('#__sportsmanagement_sports_type');
        $this->jsmquery->where('name LIKE ' . $this->jsmdb->Quote('%' . $type . '%'));
        $this->jsmdb->setQuery($this->jsmquery);

        return $this->jsmdb->loadResult();
    }

    /**
     * Function to fetch a JSON feed
     *
     * @param string $req The URL of the feed to load
     *
     * @return array  The decoded JSON query
     *
     * @since 1.0
     */
    static function getJSON($req)
    {
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
     * @param array  $obj    The JSON data
     * @param object $params The module parameters
     *
     * @return array  An array of data for output
     *
     * @since 1.0
     */
    static function processData($obj, $params)
    {
        $app = Factory::getApplication();
        // Initialize
        $github = array();
        $i = 0;

        // Load the parameters
        $uname = ComponentHelper::getParams('com_sportsmanagement')->get('cfg_github_username', '');
        $repo = ComponentHelper::getParams('com_sportsmanagement')->get('cfg_github_repository', '');
        $count = 15;

        // Convert the list name to a useable string for the JSON
        if ($repo) {
            $frepo = self::toAscii($repo);
        }

        // Process the feed
        foreach ($obj as $o) {
            if ($i <= $count) {

                // Initialize a new object
                $temp = new stdClass();
                $temp->commit = new stdClass;

                // The commit message linked to the commit
                $temp->commit->message = '<a href="https://github.com/' . $uname . '/' . $frepo . '/commit/' . $o['sha'] . '" target="_blank" rel="nofollow">' . substr($o['sha'], 0, 7) . '</a> - ';
                $temp->commit->message .= preg_replace("/#(\w+)/", '#<a href="https://github.com/' . $uname . '/' . $frepo . '/issues/\\1" target="_blank" rel="nofollow">\\1</a>', htmlspecialchars($o['commit']['message']));

                // Check if the committer information
                if ($o['author']['id'] != $o['committer']['id']) {
                    // The committer name formatted with link
                    $temp->commit->committer = Text::_('COM_SPORTSMANAGEMENT_GITHUB_AND_COMMITTED_BY') . '<a href="https://github.com/' . $o['committer']['login'] . '" target="_blank" rel="nofollow">' . $o['commit']['committer']['name'] . '</a>';

                    // The author wasn't the committer
                    $temp->commit->author = Text::_('COM_SPORTSMANAGEMENT_GITHUB_AUTHORED_BY');
                } else {
                    // The author is also the committer
                    $temp->commit->author = Text::_('COM_SPORTSMANAGEMENT_GITHUB_COMMITTED_BY');
                }

                // The author name formatted with link
                $temp->commit->author .= '<a href="https://github.com/' . $o['author']['login'] . '" target="_blank" rel="nofollow">' . $o['commit']['author']['name'] . '</a>';

                // The time of commit
                $date = date_create($o['commit']['committer']['date']);
                $date = date_format($date, 'r');
                if ($params->get('relativeTime', '1') == '1') {
                    $ISOtime = HTMLHelper::date($date, 'Y-m-d H:i:s');

                    // Load the JavaScript; first ensure we have MooTools Core
                    HTMLHelper::_('behavior.framework');
                    HTMLHelper::script(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/assets/js/prettydate.js', false, false);
                    $temp->commit->time = ' <span class="commit-time" title="' . $ISOtime . '">' . HTMLHelper::date($date, 'D M d H:i:s O Y') . '</span>';
                } else {
                    $temp->commit->time = ' ' . HTMLHelper::date($date);
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
     * @param string $repo The user inputted repo name
     *
     * @return string  The repo name converted
     *
     * @since 1.0
     */
    static function toAscii($repo)
    {
        $clean = preg_replace("/[^a-z'A-Z0-9\/_|+ -]/", '', $repo);
        $clean = strtolower(trim($clean, '-'));
        $repo = preg_replace("/[\/_|+ -']+/", '-', $clean);

        return $repo;
    }

}
?>  
