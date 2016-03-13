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

defined('_JEXEC') or die();

// welche joomla version ?
if(version_compare(JVERSION,'3.0.0','ge')) 
{
JHtml::_('jquery.framework');
}

?>

<?PHP        
/**
 * sportsmanagementView
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementView extends JViewLegacy
{

	protected $icon = '';
	protected $title = '';
    protected $layout = '';
    protected $tmpl = '';
    protected $table_data_class = '';
    protected $table_data_div = '';

	/**
	 * sportsmanagementView::display()
	 * 
	 * @param mixed $tpl
	 * @return
	 */
	public function display ($tpl = null)
	{
	   $option = JRequest::getCmd('option');
		$app = JFactory::getApplication();
        
        $view = JRequest::getCmd('view', 'cpanel');
        $this->tmpl = JRequest::getCmd('tmpl', '');
        
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
        
        $this->layout = $this->getLayout();
        
/**
 * alles aufrufen was für die views benötigt wird
 */
        
        $this->document	= JFactory::getDocument();   
        $this->app = JFactory::getApplication();
		$this->jinput = $this->app->input;
		$this->option = $this->jinput->getCmd('option');
		$this->uri = JFactory::getURI();
        $this->model = $this->getModel();

/**
 * bei der einzelverarbeitung
*/        
        if ( $this->layout == 'edit')
        {
        // get the Data
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->script = $this->get('Script');    
        }
/**
 * in der listansicht
*/        
        else
        {    
        $this->state = $this->get('State'); 
        $this->sortDirection = $this->state->get('list.direction');
        $this->sortColumn = $this->state->get('list.ordering');
        $this->items = $this->get('Items');
        $this->total = $this->get('Total');
		$this->pagination = $this->get('Pagination');
        $this->user	= JFactory::getUser();
		$this->config = JFactory::getConfig();
        $this->request_url	= $this->uri->toString();
        }
        
        if(version_compare(JVERSION,'3.0.0','ge')) 
        {
            $this->setLayout($this->getLayout() . '_3');
            $this->table_data_class = 'table table-striped';
            $this->table_data_div = '</div>';
        }
        else
        {
            // wir lassen das layout so wie es ist, dann müssen
            // nicht so viele dateien umbenannt werden
            //$this->setLayout($this->getLayout() . '_25');
            $this->setLayout($this->getLayout() );
            $this->table_data_class = 'adminlist';
            $this->table_data_div = '';
        }
        
        
        $app->enqueueMessage(sprintf(JText::_('COM_SPORTSMANAGEMENT_JOOMLA_VERSION'), COM_SPORTSMANAGEMENT_JOOMLAVERSION),'');

//		if (sportsmanagementHelper::isJoomlaVersion('2.5'))
//		{
//			// wir lassen das layout so wie es ist, dann müssen
//            // nicht so viele dateien umbenannt werden
//            //$this->setLayout($this->getLayout() . '_25');
//            $this->setLayout($this->getLayout() );
//		}
//		if (sportsmanagementHelper::isJoomlaVersion('3'))
//		{
//			$this->setLayout($this->getLayout() . '_3');
//		}

		$this->init();

		$this->addToolbar();
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' joomla version -> <br><pre>'.print_r(COM_SPORTSMANAGEMENT_JOOMLAVERSION,true).'</pre>'),'');
        
        // hier wird gesteuert, welcher menüeintrag aktiv ist.
        if(version_compare(JVERSION,'3.0.0','ge')) 
        {
            switch ( $view )
            {
                case 'projects';
                case 'projectteams';
                case 'rounds';
                case 'teampersons';
                case 'templates';
                case 'projectreferees';
                case 'projectpositions';
                case 'treetos';
                case 'divisions';
                case 'githubinstall';
                sportsmanagementHelper::addSubmenu('projects');
                break;
                case 'predictions';
                case 'predictiongames';
                case 'predictiongroups';
                case 'predictionmembers';
                case 'predictiontemplates';
                sportsmanagementHelper::addSubmenu('predictions');
                break;
                default:
                sportsmanagementHelper::addSubmenu('cpanel');
                break;
            }
            
        if ( $this->layout == 'edit')
        {}
        else
        {    
        $this->sidebar = JHtmlSidebar::render();
        }
        
        }
        
		parent::display($tpl);
	}

	/**
	 * sportsmanagementView::addToolbar()
	 * 
	 * @return void
	 */
	protected function addToolbar ()
	{
	   $option = JRequest::getCmd('option');
		$app = JFactory::getApplication();
        $view = JRequest::getCmd('view', 'cpanel');
		$canDo = sportsmanagementHelper::getActions();
        
        // in der joomla 3 version kann man die filter setzen
        if(version_compare(JVERSION,'3.0.0','ge')) 
        {
        JHtmlSidebar::setAction('index.php?option=com_sportsmanagement');   
        
        switch ($view)
        {
        case 'projects':
        case 'persons':
        case 'predictiongames':
        JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_PUBLISHED'),
			'filter_state',
			JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.state'), true)
		);
        break;    
        case 'smquotes':
        JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_PUBLISHED'),
			'filter_state',
			JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.state'), true)
		);
        JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_CATEGORY'),
			'filter_category_id',
			JHtml::_('select.options', JHtml::_('category.options', 'com_sportsmanagement'), 'value', 'text', $this->state->get('filter.category_id'))
		);        
        break;
        }
        
        
        if ( isset($this->federation) )
        {
        JHtmlSidebar::addFilter(
			JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_FEDERATION'),
			'filter_federation',
			JHtml::_('select.options', $this->federation, 'value', 'text', $this->state->get('filter.federation'), true)
		);
        }
                
        if ( isset($this->search_nation) )
        {
        JHtmlSidebar::addFilter(
			JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'),
			'filter_search_nation',
			JHtml::_('select.options', $this->search_nation, 'value', 'text', $this->state->get('filter.search_nation'), true)
		);
        }
        
        if ( isset($this->userfields) )
        {
        JHtmlSidebar::addFilter(
			JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_USERFIELD_FILTER'),
			'filter_userfields',
			JHtml::_('select.options', $this->userfields, 'id', 'name', $this->state->get('filter.userfields'), true)
		);
        }
        if ( isset($this->league) )
        {
        JHtmlSidebar::addFilter(
			JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_LEAGUES_FILTER'),
			'filter_league',
			JHtml::_('select.options', $this->league, 'id', 'name', $this->state->get('filter.league'), true)
		);
        }
        if ( isset($this->sports_type) )
        {
        JHtmlSidebar::addFilter(
			JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SPORTSTYPE_FILTER'),
			'filter_sports_type',
			JHtml::_('select.options', $this->sports_type, 'id', 'name', $this->state->get('filter.sports_type'), true)
		);
        }
        if ( isset($this->season) )
        {
        JHtmlSidebar::addFilter(
			JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SEASON_FILTER'),
			'filter_season',
			JHtml::_('select.options', $this->season, 'id', 'name', $this->state->get('filter.season'), true)
		);
        }
        
        if ( isset($this->prediction_ids) )
        {
        JHtmlSidebar::addFilter(
			JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PRED_GAME'),
			'filter_prediction_id',
			JHtml::_('select.options', $this->prediction_ids, 'value', 'text', $this->state->get('filter.prediction_id'), true)
		);
        }
        
//        if ( isset($this->prediction_id_select) )
//        {
//        JHtmlSidebar::addFilter(
//			JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PRED_GAME'),
//			'filter_prediction_id_select',
//			JHtml::_('select.options', $this->prediction_id_select, 'value', 'text', $this->state->get('filter.prediction_id_select'), true)
//		);
//        }
        
        if ( isset($this->project_position_id) )
        {
        JHtmlSidebar::addFilter(
			JText::_('COM_SPORTSMANAGEMENT_D_MENU_POSITIONS'),
			'filter_project_position_id',
			JHtml::_('select.options', $this->project_position_id, 'value', 'text', $this->state->get('filter.project_position_id'), true)
		);
        }
         
        }    
        
        
        if ( $this->layout == 'edit')
        {
        $isNew = $this->item->id == 0;
        $canDo = sportsmanagementHelper::getActions($this->item->id);
        $view = JRequest::getCmd('view', 'edit');
            if (empty($this->title))
		    {
            if ( $isNew )
            {
            $this->title = 'COM_SPORTSMANAGEMENT_' . strtoupper($this->getName()).'_NEW';    
            }
            else
            {
            $this->title = 'COM_SPORTSMANAGEMENT_' . strtoupper($this->getName()).'_EDIT';    
            }
            }
        // Built the actions for new and existing records.
		if ($isNew) 
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::apply($view.'.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save($view.'.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::custom($view.'.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}
			JToolBarHelper::cancel($view.'.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			if ($canDo->get('core.edit'))
			{
				// We can save the new record
				JToolBarHelper::apply($view.'.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save($view.'.save', 'JTOOLBAR_SAVE');
 
				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create')) 
				{
					JToolBarHelper::custom($view.'.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::custom($view.'.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}
			JToolBarHelper::cancel($view.'.cancel', 'JTOOLBAR_CLOSE');
		}    
            
            
            
            
        }
        else
        {

		if (empty($this->title))
		{
			$this->title = 'COM_SPORTSMANAGEMENT_' . strtoupper($this->getName());
		}
        
        }
		
        
        
        if (empty($this->icon))
		{
			$this->icon = strtolower($this->getName());
		}
		
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' icon -> '.$this->icon.''),'Notice');
        
        //JToolBarHelper::title(JText::_($this->title), $this->icon);
		$document = JFactory::getDocument();
        $document->addScript(JURI::root() . "administrator/components/com_sportsmanagement/views/sportsmanagement/submitbutton.js");
        if(version_compare(JVERSION,'3.0.0','ge')) 
        {
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/layout.css'.'" type="text/css" />' ."\n";    
        $document->addCustomTag($stylelink);
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        }
        else
        {    
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        }
        
        
//		$document->addStyleDeclaration(
//				'.icon-48-' . $this->icon . ' {background-image: url('.JURI::root().'administrator/components/com_sportsmanagement/assets/images/' . $this->icon .
//						 '.png);background-repeat: no-repeat;}');

		if ( $this->layout == 'edit')
        {
        if ($isNew) 
		{
        JToolBarHelper::title(JText::_($this->title), $this->icon);
        }
        else
        {
        JToolBarHelper::title( sprintf(JText::_($this->title),$this->item->name), $this->icon);    
        }
        }
        else
        {
            if(version_compare(JVERSION,'3.0.0','ge')) 
            {
            JToolBarHelper::title(JText::_($this->title) );    
            }
            else
            {
            JToolBarHelper::title(JText::_($this->title), $this->icon);
            }    
        }
        
        if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_sportsmanagement');
			JToolBarHelper::divider();
		}
        sportsmanagementHelper::ToolbarButtonOnlineHelp();
        
        
	}

	/**
	 * sportsmanagementView::init()
	 * 
	 * @return void
	 */
	protected function init ()
	{
	}
}
