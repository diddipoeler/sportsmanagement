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
class sportsmanagementViewstatistic extends JViewLegacy
{
	
	/**
	 * sportsmanagementViewstatistic::display()
	 * 
	 * @param mixed $tpl
	 * @return
	 */
	public function display($tpl = null) 
	{
		$mainframe = JFactory::getApplication();
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
        //$jRegistry->loadString($this->item->params);
        $jRegistry->loadArray($this->item->params);
        
        $mainframe->enqueueMessage(JText::_('sportsmanagementViewstatistic jRegistry<br><pre>'.print_r($jRegistry,true).'</pre>'),'Notice');
        
        //$this->formparams = JForm::getInstance($item->class, $xmlfile,array('control'=> 'params'));
        $this->formparams = JForm::getInstance('params', $xmlfile,array('control'=> 'params'),false, '/config');
        //$this->formparams->bind($jRegistry);
        $this->formparams->bind($this->item->params);
		*/
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementViewstatistic params<br><pre>'.print_r($item->params,true).'</pre>'),'Notice');
        //$mainframe->enqueueMessage(JText::_('sportsmanagementViewstatistic params<br><pre>'.print_r($item->baseparams,true).'</pre>'),'Notice');
        
        $formparams = sportsmanagementHelper::getExtendedStatistic($item->params, $item->class);
		$this->assignRef( 'formparams', $formparams );
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementViewstatistic formparams<br><pre>'.print_r($this->formparams,true).'</pre>'),'Notice');
        
// 		$extended = sportsmanagementHelper::getExtended($item->extended, 'team');
// 		$this->assignRef( 'extended', $extended );
		//$this->assign('cfg_which_media_tool', JComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_media_tool',0) );
 
		// Set the toolbar
		$this->addToolBar();
 
		// Display the template
		parent::display($tpl);
 
		// Set the document
		$this->setDocument();
	}
 
	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() 
	{
	// Get a refrence of the page instance in joomla
        $document = JFactory::getDocument();
        $option = JRequest::getCmd('option');
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JUri::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
		JRequest::setVar('hidemainmenu', true);
		$user = JFactory::getUser();
		$userId = $user->id;
		$isNew = $this->item->id == 0;
		$canDo = sportsmanagementHelper::getActions($this->item->id);
		JToolBarHelper::title($isNew ? JText::_('COM_SPORTSMANAGEMENT_STATISTIC_NEW') : JText::_('COM_SPORTSMANAGEMENT_STATISTIC_EDIT'), 'statistic');
		// Built the actions for new and existing records.
		if ($isNew) 
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::apply('statistic.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('statistic.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::custom('statistic.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}
			JToolBarHelper::cancel('statistic.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			if ($canDo->get('core.edit'))
			{
				// We can save the new record
				JToolBarHelper::apply('statistic.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('statistic.save', 'JTOOLBAR_SAVE');
 
				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create')) 
				{
					JToolBarHelper::custom('statistic.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::custom('team.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}
			JToolBarHelper::cancel('statistic.cancel', 'JTOOLBAR_CLOSE');
		}
        
        JToolBarHelper::divider();
        sportsmanagementHelper::ToolbarButtonOnlineHelp();
		JToolBarHelper::preferences($option);
	}
	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() 
	{
		$isNew = $this->item->id == 0;
		$document = JFactory::getDocument();
		$document->setTitle($isNew ? JText::_('COM_SPORTSMANAGEMENT_STATISTIC_NEW') : JText::_('COM_SPORTSMANAGEMENT_STATISTIC_EDIT'));
		$document->addScript(JUri::root() . $this->script);
		$document->addScript(JUri::root() . "/administrator/components/com_sportsmanagement/views/sportsmanagement/submitbutton.js");
		JText::script('COM_HELLOWORLD_HELLOWORLD_ERROR_UNACCEPTABLE');
	}
}
