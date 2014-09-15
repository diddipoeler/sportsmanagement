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
    $model = $this->getModel('results');
    // Get the input
    $pks = JRequest::getVar('cid', null, 'post', 'array');
    $post = JRequest::get('post');
    
//    $app->enqueueMessage(__METHOD__.' '.__LINE__.' pks<br><pre>'.print_r($pks, true).'</pre><br>','Notice');
//    $app->enqueueMessage(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post, true).'</pre><br>','Notice');
    $model->saveshort();
    $this->setRedirect('index.php?option=com_sportsmanagement&view=results&p='.$post['p'].'&division='.$post['division'].'&r='.$post['r'].'&layout=form' );
               
    }   


}
?>
