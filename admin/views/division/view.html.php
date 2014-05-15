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
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );


/**
 * sportsmanagementViewDivision
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewDivision extends JView
{
	/**
	 * sportsmanagementViewDivision::display()
	 * 
	 * @param mixed $tpl
	 * @return
	 */
	function display( $tpl = null )
	{
		$mainframe	= JFactory::getApplication();
		$option = JRequest::getCmd('option');
		$db	 		= JFactory::getDBO();
		$uri		= JFactory::getURI();
		$user		= JFactory::getUser();
		$model		= $this->getModel();
        $starttime = microtime(); 
        
        //$this->assign('show_debug_info', JComponentHelper::getParams($option)->get('show_debug_info',0) );

		$lists=array();
        
        // get the Data
		$form = $this->get('Form');
		$item = $this->get('Item');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
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
        
        $this->project_id	= $mainframe->getUserState( "$option.pid", '0' );
        $mdlProject = JModel::getInstance("Project", "sportsmanagementModel");
	    $project = $mdlProject->getProject($this->project_id);
        $this->assignRef('project',$project);
        //$this->item->project_id = $this->project_id;
        
        
        
        $this->addToolbar();
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementViewDivision item<br><pre>'.print_r($this->item,true).'</pre>'),'Notice');
        		
		parent::display($tpl);
        // Set the document
		$this->setDocument();

		
	}

	/**
	 * sportsmanagementViewDivision::_displayForm()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	function _displayForm( $tpl )
	{
		$option = JRequest::getCmd('option');

		$mainframe	= JFactory::getApplication();
		$project_id = $mainframe->getUserState( 'com_joomleagueproject' );
		$db		= JFactory::getDBO();
		$uri 	= JFactory::getURI();
		$user 	= JFactory::getUser();
		$model	= $this->getModel();

		$lists = array();
		//get the division
		$division	=& $this->get( 'data' );
		$isNew		= ( $division->id < 1 );

		// fail if checked out not by 'me'
		if ( $model->isCheckedOut( $user->get( 'id' ) ) )
		{
			$msg = JText::sprintf( 'DESCBEINGEDITTED', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DIVISION_THE_DIVISION' ), $division->name );
			$mainframe->redirect( 'index.php?option=' . $option, $msg );
		}

		// Edit or Create?
		if ( !$isNew )
		{
			$model->checkout( $user->get( 'id' ) );
		}
		else
		{
			// initialise new record
			$division->order	= 0;
		}

		/* build the html select list for ordering
		$query = '	SELECT	ordering AS value,
							name AS text
					FROM #__joomleague_division
					WHERE project_id = ' . $project_id . '
					ORDER BY ordering';

		$lists['ordering'] = JHtml::_( 'list.specificordering', $division, $division->id, $query, 1 );
		*/
		$projectws =& $this->get( 'Data', 'projectws' );

		//build the html select list for parent divisions
		$parents[] = JHtml::_( 'select.option', '0', JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PROJECT' ) );
		if ( $res =& $model->getParentsDivisions() )
		{
			$parents = array_merge( $parents, $res );
		}
		$lists['parents'] = JHtml::_(	'select.genericlist', $parents, 'parent_id', 'class="inputbox" size="1"', 'value', 'text',
										$division->parent_id );
		unset( $parents );

		$this->assignRef( 'projectws',	$projectws );
		$this->assignRef( 'lists',		$lists );
		$this->assignRef( 'division',	$division );
		$this->assignRef('form',  $this->get('form'));
    $this->assign('cfg_which_media_tool', JComponentHelper::getParams($option)->get('cfg_which_media_tool',0) );		
		//$extended = $this->getExtended($projectreferee->extended, 'division');
		//$this->assignRef( 'extended', $extended );

		$this->addToolbar();		
		parent::display( $tpl );
	}
	
	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{	
		JRequest::setVar('hidemainmenu', true);
        JRequest::setVar('pid', $this->project_id);
		$user = JFactory::getUser();
		$userId = $user->id;
		$isNew = $this->item->id == 0;
        
		$canDo = sportsmanagementHelper::getActions($this->item->id);
		JToolBarHelper::title($isNew ? JText::_('COM_SPORTSMANAGEMENT_DIVISIONS_NEW') : JText::_('COM_SPORTSMANAGEMENT_DIVISIONS_EDIT'), 'helloworld');
		// Built the actions for new and existing records.
		if ($isNew) 
		{
			$this->item->project_id = $this->project_id;
            // For new records, check the create permission.
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::apply('division.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('division.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::custom('division.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}
			JToolBarHelper::cancel('division.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			if ($canDo->get('core.edit'))
			{
				// We can save the new record
				JToolBarHelper::apply('division.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('division.save', 'JTOOLBAR_SAVE');
 
				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create')) 
				{
					JToolBarHelper::custom('division.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::custom('division.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}
			JToolBarHelper::cancel('division.cancel', 'JTOOLBAR_CLOSE');
		}
    sportsmanagementHelper::ToolbarButtonOnlineHelp();
	}	
    
    /**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() 
	{
  		// Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        
		$isNew = $this->item->id == 0;
		$document = JFactory::getDocument();
		$document->setTitle($isNew ? JText::_('COM_SPORTSMANAGEMENT_DIVISIONS_NEW') : JText::_('COM_SPORTSMANAGEMENT_DIVISIONS_EDIT'),'divison');
		$document->addScript(JURI::root() . $this->script);
		$document->addScript(JURI::root() . "/administrator/components/com_sportsmanagement/views/sportsmanagement/submitbutton.js");
		JText::script('COM_HELLOWORLD_HELLOWORLD_ERROR_UNACCEPTABLE');
	}	

}
?>
