<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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

jimport( 'joomla.application.component.controller' );

/**
 * sportsmanagementControllerResults
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementControllerResults extends JControllerLegacy
{

	
    /**
	 * sportsmanagementControllerEditMatch::__construct()
	 * 
	 * @param mixed $config
	 * @return void
	 */
	function __construct($config = array())
	{
		parent::__construct($config);
    // Initialise variables.
    $this->app = JFactory::getApplication();
    // JInput object
    $this->jinput = $this->app->input;
    $this->jsmoption = $this->jinput->getCmd('option');
    $this->model = $this->getModel('results');
    // Get the input
    $this->pks = $this->jinput->getVar('cid', null, 'post', 'array');
    $this->post = $this->jinput->post->getArray();
    
    if ( JComponentHelper::getParams($this->jsmoption)->get('show_debug_info_frontend') )
        {
    $this->app->enqueueMessage(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($this->post, true).'</pre><br>','Notice');
}

	}
    
    
    
    /**
     * sportsmanagementControllerResults::saveReferees()
     * 
     * @return void
     */
    public function saveReferees()
	{
	$this->setRedirect('index.php?option=com_sportsmanagement&view=results&cfg_which_database='.$this->post['cfg_which_database'].'&s='.$this->post['s'].'&p='.$this->post['p'].'&r='.$this->post['r'].'&division='.$this->post['division'].'&mode='.$this->post['mode'].'&order='.$this->post['order'].'&layout='.$this->post['layout'] );   
       
       
       
    }
    
       
    /**
	 * sportsmanagementControllerResults::display()
	 * 
	 * @param bool $cachable
	 * @param bool $urlparams
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false)
	{

	}
    
    /**
     * sportsmanagementControllerResults::saveshort()
     * 
     * @return void
     */
    public function saveshort()
	{
	// Initialise variables.
    $app = JFactory::getApplication();
    // JInput object
    $jinput = $app->input;
    $model = $this->getModel('results');
    // Get the input
    $pks = $jinput->getVar('cid', null, 'post', 'array');
    $post = $jinput->post->getArray();
    $layout = $jinput->getCmd('layout', 'form');
    
    if ( JComponentHelper::getParams($this->jsmoption)->get('show_debug_info_frontend') )
        {
    $app->enqueueMessage(__METHOD__.' '.__LINE__.' pks<br><pre>'.print_r($pks, true).'</pre><br>','Notice');
    $app->enqueueMessage(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post, true).'</pre><br>','Notice');
}

    $model->saveshort();
   
    $this->setRedirect('index.php?option=com_sportsmanagement&view=results&cfg_which_database='.$post['cfg_which_database'].'&s='.$post['s'].'&p='.$post['p'].'&r='.$post['r'].'&division='.$post['division'].'&mode='.$post['mode'].'&order='.$post['order'].'&layout='.$layout );
               
    }   


}
?>
