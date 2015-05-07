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

/**
 * System plugin
 * 1) onBeforeRender()
 * 2) onAfterRender()
 * 3) onAfterRoute()
 * 4) onAfterDispatch()
 * These events are triggered in 'JAdministrator' class in file 'application.php' at location
 * 'Joomla_base\administrator\includes'.
 * 5) onAfterInitialise()
 * This event is triggered in 'JApplication' class in file 'application.php' at location
 * 'Joomla_base\libraries\joomla\application'.
 */


// No direct access
defined('_JEXEC') or die('Restricted access');

if (! defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

jimport('joomla.plugin.plugin');
jimport('joomla.html.parameter');


/**
 * PlgSystemjsm_bootstrap
 * 
 * @package 
 * @author abcde
 * @copyright 2015
 * @version $Id$
 * @access public
 */
class PlgSystemjsm_bootstrap extends JPlugin
{


/**
 * PlgSystemjsm_bootstrap::__construct()
 * 
 * @param mixed $subject
 * @param mixed $params
 * @return void
 */
public function __construct( &$subject, $params )
{
		parent::__construct( $subject, $params );
		
	$app = JFactory::getApplication();
		
		//add the classes for handling
		$classpath = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sportsmanagement'.DS.'libraries'.DS.'cbootstrap.php';
		if(file_exists($classpath))
        {
			JLoader::register('CBootstrap', $classpath);
		}

	}

	/**
	 * PlgSystemjsm_bootstrap::onBeforeRender()
	 * 
	 * @return void
	 */
	public function onBeforeRender()
	{
	        $app = JFactory::getApplication();
       
        
        
	}
    
    /**
     * PlgSystemjsm_bootstrap::onAfterRender()
     * 
     * @return void
     */
    public function onAfterRender()
	{
        $app = JFactory::getApplication();

	}
    
    /**
     * PlgSystemjsm_bootstrap::onAfterRoute()
     * 
     * @return void
     */
    public function onAfterRoute()
	{
        $app = JFactory::getApplication();

	}
    
    /**
     * PlgSystemjsm_bootstrap::onAfterDispatch()
     * 
     * @return void
     */
    public function onAfterDispatch()
	{

 $app = JFactory::getApplication();
// Get a refrence of the page instance in joomla
$document	= JFactory::getDocument();

if(version_compare(JVERSION,'3.0.0','ge')) 
{
// Joomla! 3.0 code here

// Check for component
if ( JComponentHelper::getComponent('com_k2', true)->enabled )
{
$css = 'components/com_sportsmanagement/assets/css/customk2.css';
$document->addStyleSheet($css);
}

}
elseif(version_compare(JVERSION,'2.5.0','ge')) 
{
// Joomla! 2.5 code here		
if(!$app->isAdmin())
                    {
						CBootstrap::load();
					}
    }
                    
	}
    
    /**
     * PlgSystemjsm_bootstrap::onAfterInitialise()
     * 
     * @return void
     */
    public function onAfterInitialise()
	{
		
        $app = JFactory::getApplication();

	}
    
    
  
    

    

}    

?>