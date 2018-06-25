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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');

/**
 * sportsmanagementControllerjoomleagueimports
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllerjoomleagueimports extends JControllerAdmin
{

/**
	 * Class Constructor
	 *
	 * @param	array	$config		An optional associative array of configuration settings.
	 * @return	void
	 * @since	1.5
	 */
	function __construct($config = array())
	{
		parent::__construct($config);
        //$this->jsmdb = sportsmanagementHelper::getDBConnection();
        // Reference global application object
        $this->jsmapp = JFactory::getApplication();
        // JInput object
        $this->jsmjinput = $this->jsmapp->input;
        //$this->jsmoption = $this->jsmjinput->getCmd('option');
//        $this->jsmdocument = JFactory::getDocument();
//        $this->jsmuser = JFactory::getUser();
//        $this->jsmdate = JFactory::getDate();
////        $this->option = $this->jsmjinput->getCmd('option');
//        $this->club_id = $this->jsmapp->getUserState( "$this->option.club_id", '0' );
        
//        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' config<br><pre>'.print_r($config,true).'</pre>'),'');
        
		// Map the apply task to the save method.
		//$this->registerTask('apply', 'save');
	}


/**
 * sportsmanagementControllerjoomleagueimports::joomleaguesetagegroup()
 * 
 * @return void
 */
function joomleaguesetagegroup()
{
$model = $this->getModel();    
$result = $model->joomleaguesetagegroup();   
$type = ''; 
$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_JOOMLEAGUE_IMPORT_SETAGEGROUP');    
$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.'&jl_table_import_step=0&layout=infofield', false),$msg,$type);    
}


/**
 * sportsmanagementControllerjoomleagueimports::importjoomleaguenew()
 * 
 * @return void
 */
function importjoomleaguenew()
{
        //$app = JFactory::getApplication();
        //$option = JFactory::getApplication()->input->getCmd('option');
        
        //$jl_table_import_step = $app->getUserState( "$this->option.jl_table_import_step", 0 );
        $jl_table_import_step = $this->jsmjinput->get('jl_table_import_step',0);
        $sports_type_id = $this->jsmjinput->get('filter_sports_type', 0);
        
//        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' jl_table_import_step <br><pre>'.print_r($jl_table_import_step,true).'</pre>'),'');
//        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' sports_type_id <br><pre>'.print_r($sports_type_id,true).'</pre>'),'');
        
        if ( $jl_table_import_step != 'ENDE' )
        {
        $model = $this->getModel();
        $result = $model->importjoomleaguenew($jl_table_import_step,$sports_type_id);
        $jl_table_import_step = $this->jsmjinput->get('jl_table_import_step',0);
        //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' model result <br><pre>'.print_r($result,true).'</pre>'),'');
        //$result = $model->importjoomleaguenewtest($jl_table_import_step);
        $this->jsmapp->setUserState( $this->option.".jl_table_import_success", $result );
        //sleep(3);
        //$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.'&task=joomleagueimports.importjoomleaguenew&layout=default&jl_table_import_step='.$jl_table_import_step, false));
        
        $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.'&layout=default&jl_table_import_step='.$jl_table_import_step.'&filter_sports_type='.$sports_type_id, false));
        
        }
        else
        {
//        $model::$_success = $result;
        //$this->jsmapp->setUserState( "$this->option.jl_table_import_step", 0 );
        $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.'&jl_table_import_step=0&layout=infofield', false));
        }

}


/**
 * sportsmanagementControllerjoomleagueimports::importjoomleagueagegroup()
 * 
 * @return void
 */
function importjoomleagueagegroup()
{
    
    $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.'&layout=infofield', false));
    
}
  

	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'joomleagueimports', $prefix = 'sportsmanagementModel', $config = Array() ) 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}

?>