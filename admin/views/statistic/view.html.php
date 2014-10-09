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
 
// import Joomla view library
jimport('joomla.application.component.view');
 

/**
 * sportsmanagementViewstatistic
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewstatistic extends sportsmanagementView
{
	
	
	/**
	 * sportsmanagementViewstatistic::init()
	 * 
	 * @return
	 */
	public function init ()
	{
		$app = JFactory::getApplication();
        // get the Data
		$form = $this->get('Form');
		$item = $this->get('Item');
		$script = $this->get('Script');
 
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		// Assign the Data
		$this->form = $form;
		$this->item = $item;
		$this->script = $script;
        
        
        $isNew = $this->item->id == 0;
        
        if ( $isNew )
        {
        $item->class = 'basic';    
        }
        
        /*
        $templatepath = JPATH_COMPONENT_ADMINISTRATOR.DS.'statistics';
        $xmlfile = $templatepath.DS.$item->class.'.xml';
        $jRegistry = new JRegistry;
		//$jRegistry->loadString($data, $format);
        //$jRegistry->loadJSON($this->item->params);
        $jRegistry->loadArray($this->item->params);
        
        $app->enqueueMessage(JText::_('sportsmanagementViewstatistic jRegistry<br><pre>'.print_r($jRegistry,true).'</pre>'),'Notice');
        
        //$this->formparams = JForm::getInstance($item->class, $xmlfile,array('control'=> 'params'));
        $this->formparams = JForm::getInstance('params', $xmlfile,array('control'=> 'params'),false, '/config');
        //$this->formparams->bind($jRegistry);
        $this->formparams->bind($this->item->params);
		*/
        
        //$app->enqueueMessage(JText::_('sportsmanagementViewstatistic params<br><pre>'.print_r($item->params,true).'</pre>'),'Notice');
        //$app->enqueueMessage(JText::_('sportsmanagementViewstatistic params<br><pre>'.print_r($item->baseparams,true).'</pre>'),'Notice');
        
        $formparams = sportsmanagementHelper::getExtendedStatistic($item->params, $item->class);
		$this->assignRef( 'formparams', $formparams );
        
        //$app->enqueueMessage(JText::_('sportsmanagementViewstatistic formparams<br><pre>'.print_r($this->formparams,true).'</pre>'),'Notice');
        
// 		$extended = sportsmanagementHelper::getExtended($item->extended, 'team');
// 		$this->assignRef( 'extended', $extended );
		//$this->assign('cfg_which_media_tool', JComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_media_tool',0) );
 
		
	}
 
	
	/**
	 * sportsmanagementViewstatistic::addToolBar()
	 * 
	 * @return void
	 */
	protected function addToolBar() 
	{
	
		JRequest::setVar('hidemainmenu', true);
        
        $isNew = $this->item->id ? $this->title = JText::_('COM_SPORTSMANAGEMENT_STATISTIC_EDIT') : $this->title = JText::_('COM_SPORTSMANAGEMENT_STATISTIC_NEW');
        $this->icon = 'statistic';
        
		parent::addToolbar();
	}

}
