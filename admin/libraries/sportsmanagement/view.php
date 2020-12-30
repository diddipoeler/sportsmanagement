<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage libraries
 * @file       view.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/** welche joomla version ? */
if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
	/** Include the component HTML helpers. */
	HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
	HTMLHelper::_('behavior.formvalidator');
	HTMLHelper::_('behavior.keepalive');
	HTMLHelper::_('jquery.framework');
}
elseif (version_compare(substr(JVERSION, 0, 3), '3.0', 'ge'))
{
	HTMLHelper::_('jquery.framework');
	HTMLHelper::_('behavior.framework', true);
	HTMLHelper::_('behavior.modal');
	HTMLHelper::_('behavior.tooltip');
	HTMLHelper::_('behavior.formvalidation');
}
elseif (version_compare(substr(JVERSION, 0, 3), '2.0', 'ge'))
{
	HTMLHelper::_('behavior.mootools');
}

?>

<?PHP

/**
 * sportsmanagementView
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementView extends BaseHtmlView
{
	protected $icon = '';
	protected $title = '';
	protected $layout = '';
	protected $tmpl = '';
	protected $table_data_class = '';
	protected $table_data_div = '';
    public $itemname;
	
	public $bootstrap_fileinput_version = '5.1.2';
	public $bootstrap_fileinput_bootstrapversion = '4.3.1';
	public $bootstrap_fileinput_popperversion = '1.14.7';
	public $leaflet_version = '1.7.1';
	public $leaflet_css_integrity = 'sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==';
	public $leaflet_js_integrity = 'sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==';

	/**
	 * sportsmanagementView::display()
	 *
	 * @param   mixed  $tpl
	 *
	 * @return
	 */
	public function display($tpl = null)
	{
		/**
		 * alle fehlermeldungen online ausgeben
		 * mit der kategorie: jsmerror
		 */
		Log::addLogger(array('logger' => 'messagequeue'), Log::ALL, array('jsmerror'));
		/**
		 * fehlermeldungen datenbankabfragen
		 */
		Log::addLogger(array('logger' => 'database', 'db_table' => '#__sportsmanagement_log_entries'), Log::ALL, array('dblog'));
		/**
		 * laufzeit datenbankabfragen
		 */
		Log::addLogger(array('logger' => 'database', 'db_table' => '#__sportsmanagement_log_entries'), Log::ALL, array('dbperformance'));

		$this->app       = Factory::getApplication();
		$this->starttime = microtime();
		/**
		 * Check for errors.
		 */
		/*
        if (count($this->errors = $this->get('Errors')))
        {
         $this->app->enqueueMessage(implode("\n",$this->errors));
         return false;
        }
        */
		$this->layout = $this->getLayout();

		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			$this->uri = Uri::getInstance();
		}
		else
		{
			$this->uri = Factory::getURI();
		}

		/** alles aufrufen was für die views benötigt wird */
		$this->document = Factory::getDocument();
		//$this->document->addStyleSheet(Uri::root() . 'components/com_sportsmanagement/assets/css/flex.css', 'text/css');
        if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
        {
        $this->document->addScript(Uri::root() . 'administrator/components/com_sportsmanagement/assets/js/joomla4functions.js');
        //$this->document->addScript(Uri::root() . 'media/system/js/searchtools.js');
        }
		
// css parameter of formbehavior2::select2
// for details http://ivaynberg.github.io/select2/		
$this->document->addStyleDeclaration(
			'
img.sportsmanagement-img-preview {
  width: auto;
  height: 50px;
}			
			
img.item {
    padding-right: 10px;
    vertical-align: middle;
}
img.car {
    height: 25px;
}'
		);		
		
	$this->document->addScript(Uri::root() . '/components/com_sportsmanagement/assets/js/sm_functions.js');
	$this->jinput         = $this->app->input;
	$this->option         = $this->jinput->getCmd('option');
	$this->format         = $this->jinput->getCmd('format');
	$this->view           = $this->jinput->getCmd('view', 'cpanel');
	$this->tmpl           = $this->jinput->getCmd('tmpl', '');
	$this->modalheight    = ComponentHelper::getParams($this->jinput->getCmd('option'))->get('modal_popup_height', 600);
	$this->modalwidth     = ComponentHelper::getParams($this->jinput->getCmd('option'))->get('modal_popup_width', 900);
	$this->project_id     = $this->jinput->get('pid');
	$this->jsmmessage     = '';
	$this->jsmmessagetype = 'notice';
		
		
		switch ($this->view)
		{
			case 'smquotetxt':
				break;
			default:
				$this->state          = $this->get('State');
				break;
		}
	
    $this->dragable_group = '';
		$this->sortColumn = '';
		$this->sortDirection = '';
        $this->ordering = true;
        if ( $this->state )
        {
        try{
        $this->sortColumn = $this->escape($this->state->get('list.ordering'));
        $this->sortDirection  = $this->escape($this->state->get('list.direction'));
		 }
catch (Exception $e)
{
//Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), Log::ERROR, 'jsmerror');
//Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), Log::ERROR, 'jsmerror');
}
}

/** soll der link zur bewertung der komponente angezeigt werden ? */		
if (ComponentHelper::getParams($this->option)->get('show_jed_link'))
{
Log::add(Text::_('COM_SPORTSMANAGEMENT_SETTINGS_SHOW_JED_LINK_TEXT'), Log::NOTICE, 'jsmerror');
$this->app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_SETTINGS_SHOW_JED_LINK_TEXT'), 'Notice');	
}		
		
		
		
if (preg_match("/ordering/i", $this->sortColumn)) {
   $this->saveOrderButton = false;
} else {
   $this->saveOrderButton = true;
}
        //$this->saveOrder = $this->sortColumn == 'a.ordering';
        
/*
		if (isset($this->state))
		{
			$this->sortDirection = $this->state->get('list.direction');
			$this->sortColumn    = $this->state->get('list.ordering');
            //$this->saveOrder = $this->sortColumn == 'ordering';
            $this->saveOrder = true;
            //$ordering   = ($this->sortColumn == 'ordering');
            $this->ordering = true;
		}
*/
		if (ComponentHelper::getParams($this->option)->get('cfg_which_database'))
		{
			$this->jsmmessage = 'Sie haben Zugriff auf die externe Datenbank';
		}

		if (!$this->project_id)
		{
			$this->project_id = $this->app->getUserState("$this->option.pid", '0');
		}

		$this->user        = Factory::getUser();
		$this->request_url = $this->uri->toString();

		switch ($this->view)
		{
			case 'predictions';
			case 'extensions';

				// Case 'github';
				break;
			default:
				$this->model = $this->getModel();
				break;
		}

		/** bei der einzelverarbeitung */
		if ($this->layout == 'edit'
			|| $this->layout == 'edit_3'
			|| $this->layout == 'edit_4'
		)
		{
			switch ($this->view)
			{
				case 'match';
				case 'predictionproject';

					// Case 'jsmgcalendar':
					break;
				default:
					$this->addTemplatePath(JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . $this->option . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'fieldsets' . DIRECTORY_SEPARATOR . 'tmpl');

					/** Get the Data */
					$this->form   = $this->get('Form');
					$this->item   = $this->get('Item');
					$this->script = $this->get('Script');

					$this->document->addScriptDeclaration(
						"
	Joomla.submitbutton = function(task)
	{
		if (task == '" . $this->view . ".cancel' || document.formvalidator.isValid(document.getElementById('adminForm')))
		{
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
	};

"
					);

					break;
			}
			
/** hier wird der name für den button des bilderuploads gesetzt */			
switch ($this->view)
{
case 'club';
case 'playground';
case 'league';
case 'person';
case 'position';
case 'agegroup';
case 'sportstype';		
case 'eventtype';
$this->app->setUserState('com_sportsmanagement.itemname', Text::_($this->item->name) );
break;
case 'teamplayer';
case 'projectreferee';
$mdlPerson = BaseDatabaseModel::getInstance("player", "sportsmanagementModel");
$project_person = $mdlPerson->getPerson($this->item->person_id);
$this->app->setUserState('com_sportsmanagement.itemname', $project_person->lastname . ' - ' . $project_person->firstname);
break;
case 'player';
$this->app->setUserState('com_sportsmanagement.itemname', $this->item->lastname.' '.$this->item->firstname);
break;
case 'smquote';
$this->app->setUserState('com_sportsmanagement.itemname', $this->item->author);		
break;

case 'projectteam';
$team_id = $this->item->team_id;
$season_team = Table::getInstance('seasonteam', 'sportsmanagementTable');
$season_team->load($team_id);
$mdlTeam = BaseDatabaseModel::getInstance('Team', 'sportsmanagementModel');
$this->project_team = $mdlTeam->getTeam($season_team->team_id, 0);
$this->app->setUserState('com_sportsmanagement.itemname', $this->project_team->name);

break;		
}
            
            
            
            
		}

		/** in der listansicht */
		else
		{
			if ($this->format != 'json')
			{
				/** dadurch werden die spaltenbreiten optimiert */
				$this->document->addStyleSheet(Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/form_control.css', 'text/css');
			}

			switch ($this->view)
			{
				case 'predictions';
				case 'extensions';
				case 'jlxmlexports';
				case 'treeto';
				case 'jlextdfbkeyimport';
				case 'transifex';
                case 'imagelist';
				break;
				default:
				$this->items      = $this->get('Items');
				$this->total      = $this->get('Total');
				$this->pagination = $this->get('Pagination');
				break;
			}

			$this->user        = Factory::getUser();
			$this->config      = Factory::getConfig();
			$this->request_url = $this->uri->toString();
		}

		if (ComponentHelper::getParams($this->option)->get('show_debug_info_backend'))
		{
		}

		if (version_compare(JSM_JVERSION, '4', 'eq'))
		{
			$this->setLayout($this->getLayout() . '_4');
			$this->table_data_class = 'table table-striped';
			$this->table_data_div   = '</div>';
		}
		elseif (version_compare(JSM_JVERSION, '3', 'eq'))
		{
			$this->setLayout($this->getLayout() . '_3');
			$this->table_data_class = 'table table-striped';
			$this->table_data_div   = '</div>';
		}
		else
		{
			// Wir lassen das layout so wie es ist, dann müssen
			// nicht so viele dateien umbenannt werden
			$this->setLayout($this->getLayout());
			$this->table_data_class = 'adminlist';
			$this->table_data_div   = '';
		}

		$this->init();
		$this->addToolbar();

		// Hier wird gesteuert, welcher menüeintrag aktiv ist.
		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			switch ($this->view)
			{
				case 'projects';
				case 'projectteams';
				case 'rounds';
				case 'teamplayers';
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
				case 'predictionrounds';
					sportsmanagementHelper::addSubmenu('predictions');
					break;
				default:
					sportsmanagementHelper::addSubmenu('cpanel');
					break;
			}

			if ($this->layout == 'edit'
				|| $this->layout == 'edit_3'
				|| $this->layout == 'edit_4'
			)
			{
			}
			else
			{
				$this->sidebar = JHtmlSidebar::render();
			}
		}
	/*	
switch ($this->view)
{
case 'clubs';
case 'playgrounds':
//$this->filterForm    = $this->get('FilterForm');
//$this->activeFilters = $this->get('ActiveFilters');	
break;
}
*/		

		parent::display($tpl);
	}

	/**
	 * sportsmanagementView::init()
	 *
	 * @return void
	 */
	protected function init()
	{
	}

	/**
	 * sportsmanagementView::addToolbar()
	 *
	 * @return void
	 */
	protected function addToolbar()
	{
		$canDo = sportsmanagementHelper::getActions();
        $myoptions = array();

		// In der joomla 3 version kann man die filter setzen
		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			JHtmlSidebar::setAction('index.php?option=com_sportsmanagement');

			switch ($this->view)
			{
				//case 'projects':
				//case 'players':
				case 'predictiongames':
				case 'jlextfederations':
				case 'jlextassociations':
				//case 'jlextcountries':
				//case 'agegroups':
				//case 'eventtypes':
				//case 'leagues':
				//case 'seasons':
				case 'sportstypes':
				//case 'positions':
				case 'clubnames':
				//case 'clubs':
				//case 'teams':
				//case 'playgrounds':
				//case 'rounds':
				case 'divisions':
				case 'extrafields':
				//case 'teamplayers':
					JHtmlSidebar::addFilter(
						Text::_('JOPTION_SELECT_PUBLISHED'),
						'filter_state',
						HTMLHelper::_('select.options', HTMLHelper::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.state'), true)
					);
					break;
				case 'clubs':
					/*
					JHtmlSidebar::addFilter(
						Text::_('JOPTION_SELECT_PUBLISHED'),
						'filter_state',
						HTMLHelper::_('select.options', HTMLHelper::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.state'), true)
					);
*/
//					$myoptions[] = HTMLHelper::_('select.option', '1', Text::_('JNO'));
//					$myoptions[] = HTMLHelper::_('select.option', '2', Text::_('JYES'));
//					JHtmlSidebar::addFilter(
//						Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_GEO_DATEN'),
//						'filter_geo_daten',
//						HTMLHelper::_('select.options', $myoptions, 'value', 'text', $this->state->get('filter.geo_daten'), true)
//					);
                    
//                    unset($myoptions);
//                    $myoptions[] = HTMLHelper::_('select.option', '0', Text::_('JNO'));
//					$myoptions[] = HTMLHelper::_('select.option', '1', Text::_('JYES'));
//					JHtmlSidebar::addFilter(
//						Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_STANDARD_PICTURE'),
//						'filter_standard_picture',
//						HTMLHelper::_('select.options', $myoptions, 'value', 'text', $this->state->get('filter.standard_picture'), true)
//					);

//					if (isset($this->search_nation) && is_array($this->association))
//					{
//						JHtmlSidebar::addFilter(
//							Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_ASSOCIATION'),
//							'filter_association',
//							HTMLHelper::_('select.options', $this->association, 'value', 'text', $this->state->get('filter.association'), true)
//						);
//					}

					break;
				case 'smquotes':
					/*
					JHtmlSidebar::addFilter(
						Text::_('JOPTION_SELECT_PUBLISHED'),
						'filter_state',
						HTMLHelper::_('select.options', HTMLHelper::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.state'), true)
					);
					*/
					/*
					JHtmlSidebar::addFilter(
						Text::_('JOPTION_SELECT_CATEGORY'),
						'filter_category_id',
						HTMLHelper::_('select.options', HTMLHelper::_('category.options', 'com_sportsmanagement'), 'value', 'text', $this->state->get('filter.category_id'))
					);
					*/
					break;
			}
/*
			if (isset($this->search_nation))
			{
				JHtmlSidebar::addFilter(
					Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'),
					'filter_search_nation',
					HTMLHelper::_('select.options', $this->search_nation, 'value', 'text', $this->state->get('filter.search_nation'), true)
				);
				
			}
            */
            switch ($this->view)
			{
			 case 'clubs':
					if (isset($this->search_nation) && is_array($this->association))
					{
						JHtmlSidebar::addFilter(
							Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_ASSOCIATION'),
							'filter_association',
							HTMLHelper::_('select.options', $this->association, 'value', 'text', $this->state->get('filter.association'), true)
						);
					}             
             break;
             }

			if (isset($this->federation))
			{
				JHtmlSidebar::addFilter(
					Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_FEDERATION'),
					'filter_federation',
					HTMLHelper::_('select.options', $this->federation, 'value', 'text', $this->state->get('filter.federation'), true)
				);
			}

			if (isset($this->unique_id))
			{
				JHtmlSidebar::addFilter(
					Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_UNIQUE_ID'),
					'filter_unique_id',
					HTMLHelper::_('select.options', $this->unique_id, 'value', 'text', $this->state->get('filter.unique_id'), true)
				);
			}

			if (isset($this->userfields))
			{
				JHtmlSidebar::addFilter(
					Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_USERFIELD_FILTER'),
					'filter_userfields',
					HTMLHelper::_('select.options', $this->userfields, 'id', 'name', $this->state->get('filter.userfields'), true)
				);
			}

			if (isset($this->league))
			{
				JHtmlSidebar::addFilter(
					Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_LEAGUES_FILTER'),
					'filter_league',
					HTMLHelper::_('select.options', $this->league, 'id', 'name', $this->state->get('filter.league'), true)
				);
			}
/*
			if (isset($this->sports_type))
			{
				JHtmlSidebar::addFilter(
					Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SPORTSTYPE_FILTER'),
					'filter_sports_type',
					HTMLHelper::_('select.options', $this->sports_type, 'id', 'name', $this->state->get('filter.sports_type'), true)
				);
			}
*/
			if (isset($this->season))
			{
			 /*
             
             $append = '';
             $opt = sportsmanagementHelper::formatselect2output($this->season,'season','season' );
             HTMLHelper::_('formbehavior2.select2', '.season', $opt);
             echo HTMLHelper::_(
				'select.genericlist', $this->season, 'filter_season',
				'style="width:225px;" class="season" size="1"' . $append, 'id', 'name', $this->state->get('filter.season') 
			);
            */
            
//				JHtmlSidebar::addFilter(
//					Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SEASON_FILTER'),
//					'filter_season',
//					HTMLHelper::_('select.options', $this->season, 'id', 'name', $this->state->get('filter.season'), true)
//				);
                
             /*   
 $this->document->addScriptDeclaration(
						'
//var element = document.getElementById("filter_season");
//element.classList.add("filter_season");
jQuery(document).ready(function($) {
document.getElementById("filter_season").classList.add("filter_season");
});

             ');                
                */
                
			}

			if (isset($this->prediction_ids))
			{
				JHtmlSidebar::addFilter(
					Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PRED_GAME'),
					'filter_prediction_id',
					HTMLHelper::_('select.options', $this->prediction_ids, 'value', 'text', $this->state->get('filter.prediction_id'), true)
				);
			}

			if (isset($this->project_position_id))
			{
				JHtmlSidebar::addFilter(
					Text::_('COM_SPORTSMANAGEMENT_D_MENU_POSITIONS'),
					'filter_project_position_id',
					HTMLHelper::_('select.options', $this->project_position_id, 'value', 'text', $this->state->get('filter.project_position_id'), true)
				);
			}
/*
			if (isset($this->search_agegroup))
			{
				JHtmlSidebar::addFilter(
					Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP_FILTER'),
					'filter_search_agegroup',
					HTMLHelper::_('select.options', $this->search_agegroup, 'value', 'text', $this->state->get('filter.search_agegroup'), true)
				);
			}
			*/
		}

		if ($this->layout == 'edit'
			|| $this->layout == 'edit_3'
			|| $this->layout == 'edit_4'
		)
		{
			$isNew = $this->item->id == 0;
			$canDo = sportsmanagementHelper::getActions($this->item->id);

			if (empty($this->title))
			{
				if ($isNew)
				{
					$this->title = 'COM_SPORTSMANAGEMENT_ADMIN_' . strtoupper($this->getName()) . '_NEW';
				}
				else
				{
					$this->title = 'COM_SPORTSMANAGEMENT_ADMIN_' . strtoupper($this->getName()) . '_EDIT';
				}
			}

			// Built the actions for new and existing records.
			// Projectteam
			$search_tmpl_array = array('projectteam' => null, 'treetonode' => null);

			if ($isNew)
			{
				// For new records, check the create permission.
				if ($canDo->get('core.create'))
				{
					if (version_compare(JSM_JVERSION, '3', 'eq'))
					{
						ToolbarHelper::apply($this->view . '.apply', 'JTOOLBAR_APPLY');
						ToolbarHelper::save($this->view . '.save', 'JTOOLBAR_SAVE');

						if (!array_key_exists($this->view, $search_tmpl_array))
						{
							ToolbarHelper::custom($this->view . '.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
						}
					}
					elseif (version_compare(JSM_JVERSION, '4', 'eq'))
					{
						$toolbarButtons[] = array('apply', $this->view . '.apply');
						$toolbarButtons[] = array('save', $this->view . '.save');

						if (!array_key_exists($this->view, $search_tmpl_array))
						{
							$toolbarButtons[] = array('save2new', $this->view . '.save2new');
						}
					}
				}

				ToolbarHelper::cancel($this->view . '.cancel', 'JTOOLBAR_CANCEL');
			}
			else
			{
				if ($canDo->get('core.edit'))
				{
					if (version_compare(JSM_JVERSION, '3', 'eq'))
					{
						// We can save the new record
						ToolbarHelper::apply($this->view . '.apply', 'JTOOLBAR_APPLY');
						ToolbarHelper::save($this->view . '.save', 'JTOOLBAR_SAVE');
					}
					elseif (version_compare(JSM_JVERSION, '4', 'eq'))
					{
						$toolbarButtons[] = array('apply', $this->view . '.apply');
						$toolbarButtons[] = array('save', $this->view . '.save');
					}

					// We can save this record, but check the create permission to see if we can return to make a new one.
					if ($canDo->get('core.create') && !array_key_exists($this->view, $search_tmpl_array))
					{
						if (version_compare(JSM_JVERSION, '3', 'eq'))
						{
							ToolbarHelper::custom($this->view . '.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
						}
						elseif (version_compare(JSM_JVERSION, '4', 'eq'))
						{
							$toolbarButtons[] = array('save2new', $this->view . '.save2new');
						}
					}
				}

				if ($canDo->get('core.create') && !array_key_exists($this->view, $search_tmpl_array))
				{
					if (version_compare(JSM_JVERSION, '3', 'eq'))
					{
						ToolbarHelper::custom($this->view . '.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
					}
					elseif (version_compare(JSM_JVERSION, '4', 'eq'))
					{
						$toolbarButtons[] = array('save2copy', $this->view . '.save2copy');
					}
				}

				ToolbarHelper::cancel($this->view . '.cancel', 'JTOOLBAR_CLOSE');
			}

			if (version_compare(JSM_JVERSION, '4', 'eq'))
			{
				ToolbarHelper::saveGroup(
					$toolbarButtons,
					'btn-success'
				);
			}
		}
		else
		{
			if (empty($this->title))
			{
				$this->title = 'COM_SPORTSMANAGEMENT_ADMIN_' . strtoupper($this->getName());
			}
		}

		if (empty($this->icon))
		{
			$this->icon = strtolower($this->getName());
		}

		$document = Factory::getDocument();

		if (version_compare(JVERSION, '4.0.0-dev', 'ge'))
		{
			$stylelink = '<link rel="stylesheet" href="' . Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/jlextusericons4.css' . '" type="text/css" />' . "\n";
			$document->addCustomTag($stylelink);
			$stylelink = '<link rel="stylesheet" href="' . Uri::root() . 'components/com_sportsmanagement/libraries/flag-icon/css/flag-icon.css' . '" type="text/css" />' . "\n";
			$document->addCustomTag($stylelink);
		}
		elseif (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			$document->addScript(Uri::root() . "administrator/components/com_sportsmanagement/views/sportsmanagement/submitbutton.js");
			$stylelink = '<link rel="stylesheet" href="' . Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/layout.css' . '" type="text/css" />' . "\n";
			$document->addCustomTag($stylelink);
			$stylelink = '<link rel="stylesheet" href="' . Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css' . '" type="text/css" />' . "\n";
			$document->addCustomTag($stylelink);
			$stylelink = '<link rel="stylesheet" href="' . Uri::root() . 'components/com_sportsmanagement/libraries/flag-icon/css/flag-icon.css' . '" type="text/css" />' . "\n";
			$document->addCustomTag($stylelink);
		}
		else
		{
			$stylelink = '<link rel="stylesheet" href="' . Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css' . '" type="text/css" />' . "\n";
			$document->addCustomTag($stylelink);
		}

		if ($this->layout == 'edit'
			|| $this->layout == 'edit_3'
			|| $this->layout == 'edit_4'
		)
		{
			if ($isNew)
			{
				ToolbarHelper::title(Text::_($this->title), $this->icon);
			}
			else
			{
				ToolbarHelper::title(sprintf(Text::_($this->title), $this->item->name), $this->icon);
			}
		}
		else
		{
			if (version_compare(JVERSION, '3.0.0', 'ge'))
			{
				ToolbarHelper::title(Text::_($this->title));
			}
			else
			{
				ToolbarHelper::title(Text::_($this->title), $this->icon);
			}

			/**
			 * zwischen den views unterscheiden
			 */
			switch ($this->view)
			{
				case 'joomleagueimports':
				case 'githubinstall':
				case 'extensions':
				case 'projectteams':
				case 'cpanel':
				case 'jlxmlimports':
				case 'jlextlmoimports':
				case 'projectpositions':
				case 'predictionmembers':
				case 'templates':
				case 'predictiongroups':
				case 'predictionrounds':
				case 'jlextdfbkeyimport':
				case 'transifex';
				case 'predictions';
				case 'specialextensions';
				case 'jlxmlexports';
				case 'treetonodes';
				case 'treetos';
				case 'treetomatchs';
				case 'smextxmleditors';
				case 'smextxmleditor';
				case 'jsmopenligadb';
                case 'smimageimports';
				case 'smquotestxt';
				break;
				default:
					/**
					 * es gibt nur noch die ablage in den papierkorb
					 * dadurch sind wir in der lage, fehlerhaft gelöschte einträge
					 * wieder herzustellen um eine fehlerursache besser zu finden
					 */
					if ($canDo->get('core.delete'))
					{
						ToolbarHelper::deleteList('', $this->view . '.delete');
						ToolbarHelper::trash($this->view . '.trash');
					}

					ToolbarHelper::checkin($this->view . '.checkin');
					break;
			}
		}

		$cfg_help_server = ComponentHelper::getParams($this->option)->get('cfg_help_server', '');
		$layout          = $this->jinput->get('layout');
		$view            = ucfirst(strtolower($this->view));
		$layout          = ucfirst(strtolower($layout));

		if ($canDo->get('core.admin'))
		{
			ToolbarHelper::preferences('com_sportsmanagement');
			ToolbarHelper::help('JHELP_COMPONENTS_SPORTSMANAGEMENT_CPANEL', false, $cfg_help_server . 'SM-Backend:' . $view);
			ToolbarHelper::divider();
		}
        
        switch ($this->view)
		{
		case 'rosterpositions';
        $title  = Text::_('JTOOLBAR_BATCH');
		$layout = new JLayoutFile('rosterpositions', JPATH_ROOT . '/components/com_sportsmanagement/layouts');
		$html   = $layout->render();
		Toolbar::getInstance('toolbar')->appendButton('Custom', $html, 'batch');
        $modal_params           = array();
        $modal_params['title']  = Text::_('JLIB_HTML_BEHAVIOR_UPLOADER_CURRENT_TITLE');
		$modal_params['url']    = 'index.php?option=com_sportsmanagement&view=imagelist&author=&fieldid=&tmpl=component&imagelist=1&asset=com_sportsmanagement&folder=rosterground&type=rosterground';
		$modal_params['height'] = $this->modalheight;
		$modal_params['width']  = $this->modalwidth;
		echo HTMLHelper::_('bootstrap.renderModal', 'rosterpositions', $modal_params);
		break;
        }

		/** test */
		$title  = Text::_('JTOOLBAR_BATCH');
		$layout = new JLayoutFile('newissue', JPATH_ROOT . '/components/com_sportsmanagement/layouts');
		$html   = $layout->render();
		Toolbar::getInstance('toolbar')->appendButton('Custom', $html, 'batch');

		$modal_params           = array();
        $modal_params['title']  = Text::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_ADD_ISSUE');
		$modal_params['url']    = 'index.php?option=com_sportsmanagement&view=github&layout=addissue&tmpl=component&issuelayout=' . $this->layout . '&issueview=' . $this->view;
		$modal_params['height'] = $this->modalheight;
		$modal_params['width']  = $this->modalwidth;
		echo HTMLHelper::_('bootstrap.renderModal', 'newissue', $modal_params);

	}
}
