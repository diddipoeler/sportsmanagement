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

/**
 * github icons
 * https://octicons.github.com/
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla  library
//jimport('joomla.github.issues');
//jimport('joomla.github.github');
jimport('joomla.application.component.model');

//require_once(JPATH_ROOT.DS.'libraries'.DS.'joomla'.DS. 'github' . DS . 'github.php');
//require_once(JPATH_ROOT.DS.'libraries'.DS.'joomla'.DS. 'github' . DS . 'package' . DS . 'issues.php');

if( version_compare(JSM_JVERSION,'4','eq') ) 
{
    
}
else
{    
//use Joomla\Github\Github;
JLoader::import('libraries.joomla.github.github', JPATH_ADMINISTRATOR);
}


/**
 * sportsmanagementModelgithub
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class sportsmanagementModelgithub extends JModelLegacy
{
    var $client = '';
    
    
    /**
     * sportsmanagementModelgithub::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    public function __construct($config = array())
        {  
            parent::__construct($config);
        // Reference global application object
        $this->app = JFactory::getApplication();
        $this->user	= JFactory::getUser();     
        // JInput object
        $this->jinput = $this->app->input;
        $this->option = $this->jinput->getCmd('option');
        $this->pks = $this->jinput->get('cid',array(),'array');
        $this->post = $this->jinput->post->getArray(array());    
            
        }
            
    /**
     * sportsmanagementModelgithub::addissue()
     * 
     * @return void
     */
    function addissue()
    {
    /**
     * gibt es den github token
     */
    if ( empty($this->post['gh_token']) )
    {
    $this->app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_NO_TOKEN'),'Error');
    /**
     * wenn nicht kann es aber einen user mit passwort geben
     */
    if ( empty($this->post['api_username']) && empty($this->post['api_password']) ) 
 		{ 
$this->app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_NO_USER_PASSWORD'),'Error');
return false;
 		} 
        else
        {
        $this->app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_USER_PASSWORD'),'Notice');    


    /**
     * hat die nachricht einen titel ?
     */
    if ( empty($this->post['title']) )
    {
    $this->app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_NO_TITLE'),'Error');
    return false;    
    }
    else
    {
    /**
    * ist die nachricht auch ausgefüllt ?
    */    
    if ( empty($this->post['message']) )
    {
    $this->app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_NO_MESSAGE'),'Error');
    return false;    
    }
    else
    {
    $insertresult = $this->insertissue();    
    
//    [number] => 272
//    [title] => Backend-View: sportstypes Layout: default
    
    //$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' insertresult<br><pre>'.print_r($insertresult,true).'</pre>'),'');    
    }    
        
    }
            
        }
            
    return false; 
    }    
    
    
             
        
    
//    if ( empty($this->post['message']) )
//    {
//    $this->app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_NO_MESSAGE'),'Error');
//    return false;    
//    }    
//    
//    $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($this->post,true).'</pre>'),'');    
        
    }
    
    
    /**
     * sportsmanagementModelgithub::insertissue()
     * 
     * @return void
     */
    function insertissue()
    {
    $github_user = JComponentHelper::getParams($this->option)->get('cfg_github_username','');
        $github_repo = JComponentHelper::getParams($this->option)->get('cfg_github_repository','');    
    $gh_options = new JRegistry();
// If an API token is set in the params, use it for authentication 
 		if ( $this->post['gh_token'] ) 
 		{ 
 			$gh_options->set('gh.token', $this->post['gh_token'] ); 
 		} 
 		// Set the username and password if set in the params 
 		else
 		{ 
 			$gh_options->set('api.username', $this->post['api_username'] ); 
 			$gh_options->set('api.password', $this->post['api_password'] ); 
			
 		} 

$github = new JGithub($gh_options);
//$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' github <br><pre>'.print_r($github ,true).'</pre>'),'');    

// Create an issue
$labels = array($this->post['labels']);
return $github->issues->create($github_user, $github_repo, $this->post['title'], $this->post['message'], $this->post['api_username'], $this->post['milestones'], $labels);    
       
    }    
        
    
    /**
     * sportsmanagementModelgithub::getGithubList()
     * 
     * @return
     */
    function getGithubList()
    {
        $option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication();
        
        //$this->client = JApplicationHelper::getClientInfo($this->getState('filter.client_id', 0));
        //JApplicationHelper::addClientInfo($this->client);
        $this->client = JApplicationHelper::getClientInfo();
        
        $github_user = JComponentHelper::getParams($option)->get('cfg_github_username','');
        $github_repo = JComponentHelper::getParams($option)->get('cfg_github_repository','');
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' client<br><pre>'.print_r($this->client,true).'</pre>'),'');
        //JGithubIssues::$client = $this->client;
        //$GithubList = JGithubPackageIssues->getListByRepository($github_user,$github_repo);
        
        $params = \JComponentHelper::getParams($option);
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' params <br><pre>'.print_r($params ,true).'</pre>'),'');

$gh_options = new JRegistry();
// If an API token is set in the params, use it for authentication 
 		if ($params->get('gh_token', '')) 
 		{ 
 			$gh_options->set('gh.token', $params->get('gh_token', '')); 
 		} 
 		// Set the username and password if set in the params 
 		else
// 		elseif ($params->get('gh_user', '') && $params->get('gh_password')) 
 		{ 
 			$gh_options->set('api.username', $params->get('gh_user', '')); 
 			$gh_options->set('api.password', $params->get('gh_password', '')); 
			
 		} 

        
        
//$gh_options->set('gh.token', 1 );
$github = new JGithub($gh_options);

//$github = new JGithub;
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' gh_options<br><pre>'.print_r($gh_options,true).'</pre>'),'');        
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' github <br><pre>'.print_r($github ,true).'</pre>'),'');
        
// List pull requests
$state = 'open|closed';
$page = 0;
$perPage = 20;
//$pulls = $github->pulls->getList($github_user, $github_repo, $state, $page, $perPage);

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' pulls <br><pre>'.print_r($pulls ,true).'</pre>'),'');        

$page = 0;
$perPage = 30;
$commits = $github->commits->getList($github_user, $github_repo, $page, $perPage);

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' commits <br><pre>'.print_r($commits ,true).'</pre>'),'');        




// List milestones for a repository
$state = 'open|closed';
$sort = 'due_date|completeness';
$direction = 'asc|desc';
$page = 0;
$perPage = 20;
$milestones = $github->issues->milestones->getList($github_user, $github_repo);
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' milestones <br><pre>'.print_r($milestones ,true).'</pre>'),'');        

// Create an issue
$labels = array('bug');
//$github->issues->create($github_user, $github_repo, 'Found a bug', 'having a problem with this.', 'diddipoeler', '1', $labels);

// List Stargazers.
$starred = $github->activity->starring->getList($github_user, $github_repo);
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' starred <br><pre>'.print_r($starred ,true).'</pre>'),'');        

// List issues
$filter = 'assigned|created|mentioned|subscribed';
$state = 'open|closed';
$labels = ':label1,:label2';
$sort = 'created|updated|comments';
$direction = 'asc|desc';
$since = new JDate('2012-12-12');
//$since = '2012-12-12';
$page = 0;
$perPage = 20;
//$issues = $github->issues->getList($filter, $state, $labels, $sort, $direction, $since, $page, $perPage);
        
        
    return $commits;    
    }
    
}    

?>
