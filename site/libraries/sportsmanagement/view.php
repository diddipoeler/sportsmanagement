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
 *
 * fehlerbehandlung
 * https://docs.joomla.org/Using_JLog
 * https://hotexamples.com/examples/-/JLog/addLogger/php-jlog-addlogger-method-examples.html
 * http://eddify.me/posts/logging-in-joomla-with-jlog.html
 * https://github.com/joomla-framework/log/blob/master/src/Logger/Database.php
 */
defined('_JEXEC') or die();
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\HTML\HTMLHelper;


if ( ComponentHelper::getParams($this->jinput->getCmd('option'))->get('show_jsm_errors', 0) )
{
ini_set('display_errors', ComponentHelper::getParams($this->jinput->getCmd('option'))->get('show_jsm_errors_front', 0));
ini_set('display_startup_errors', ComponentHelper::getParams($this->jinput->getCmd('option'))->get('show_jsm_errors_front', 0));    
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);    

if ( ComponentHelper::getParams($this->jinput->getCmd('option'))->get('show_jsm_errors_file', 0) )
{
ini_set('error_log', "jsm-errors.log");    
}

    
}


/**
 *
 * welche joomla version ?
 */
if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
	/**
	 * Include the component HTML helpers.
	 */
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

$document = Factory::getDocument();

$params_com     = ComponentHelper::getParams('com_sportsmanagement');
$jsmgrid        = $params_com->get('use_jsmgrid');
$jsmflex        = $params_com->get('use_jsmflex');
$cssflags       = $params_com->get('cfg_flags_css');
$usefontawesome = $params_com->get('use_fontawesome');
$addfontawesome = $params_com->get('add_fontawesome');

/** Welche joomla version ? */
if (version_compare(JVERSION, '3.0.0', 'ge'))
{
	if ($cssflags)
	{
		$stylelink = Uri::root() . 'components/com_sportsmanagement/libraries/flag-icon/css/flag-icon.css';
		$document->addStyleSheet($stylelink);
	}

	if ($jsmflex)
	{
		$stylelink = Uri::root() . 'components/com_sportsmanagement/assets/css/flex.css';
		$document->addStyleSheet($stylelink);
	}

	if ($jsmgrid)
	{
		$stylelink = Uri::root() . 'components/com_sportsmanagement/assets/css/grid.css';
		$document->addStyleSheet($stylelink);
	}

	if ($usefontawesome)
	{
		$stylelink = Uri::root() . 'components/com_sportsmanagement/assets/css/fontawesome_extend.css';
		$document->addStyleSheet($stylelink);
	}

	if ($addfontawesome)
	{
		$stylelink = Uri::root() . 'components/com_sportsmanagement/libraries/fontawesome/css/font-awesome.min.css';
		$document->addStyleSheet($stylelink);
	}
}
elseif (version_compare(JVERSION, '2.5.0', 'ge'))
{
	// Joomla! 2.5 code here
}
elseif (version_compare(JVERSION, '1.7.0', 'ge'))
{
	// Joomla! 1.7 code here
}
elseif (version_compare(JVERSION, '1.6.0', 'ge'))
{
	// Joomla! 1.6 code here
}
else
{
	// Joomla! 1.5 code here
}

/**
 * sportsmanagementView
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementView extends HtmlView
{
	protected $icon = '';
	protected $title = '';
	protected $layout = '';
	protected $tmpl = '';
	protected $table_data_class = '';
	protected $table_data_div = '';
	
	public $bootstrap_fileinput_version = '5.1.2';
	public $bootstrap_fileinput_bootstrapversion = '4.3.1';
	public $bootstrap_fileinput_popperversion = '1.14.7';
	public $leaflet_version = '1.7.1';
	public $leaflet_css_integrity = 'sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==';
	public $leaflet_js_integrity = 'sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==';
	
	public $leaflet_locatecontrol = '0.72.0';
	public $leaflet_routing_machine = '3.2.12';

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
		 * JLog::INFO, JLog::WARNING, JLog::ERROR, JLog::ALL, JLog::EMERGENCY or JLog::CRITICAL
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

		// Reference global application object
		$this->app = Factory::getApplication();

		// JInput object
		$this->jinput = $this->app->input;

		$this->modalheight = ComponentHelper::getParams($this->jinput->getCmd('option'))->get('modal_popup_height', 600);
		$this->modalwidth  = ComponentHelper::getParams($this->jinput->getCmd('option'))->get('modal_popup_width', 900);

		if (version_compare(JSM_JVERSION, '4', 'eq'))
		{
			$this->uri = Uri::getInstance();
		}
		else
		{
			$this->uri = Factory::getURI();
		}

		$this->action = $this->uri->toString();
		$this->params = $this->app->getParams();

		// Get a refrence of the page instance in joomla
		$this->document           = Factory::getDocument();
		$this->option             = $this->jinput->getCmd('option');
		$this->user               = Factory::getUser();
		$this->view               = $this->jinput->getVar("view");
		$this->cfg_which_database = $this->jinput->getVar('cfg_which_database', '0');

		if (isset($_SERVER['HTTP_REFERER']))
		{
			$this->backbuttonreferer = $_SERVER['HTTP_REFERER'];
		}
		else
		{
			$this->backbuttonreferer = getenv('HTTP_REFERER');
		}

		$this->model = $this->getModel();
		$headData    = $this->document->getHeadData();
		$scripts     = $headData['scripts'];
		$this->document->addStyleSheet(Uri::base() . 'components/' . $this->option . '/assets/css/modalwithoutjs.css');

		$this->document->addStyleSheet(Uri::base() . 'components/' . $this->option . '/assets/css/jcemediabox.css');
		$this->document->addScript(Uri::root(true) . '/components/' . $this->option . '/assets/js/jcemediabox.js');

		$headData['scripts'] = $scripts;
		$this->document->setHeadData($headData);

		switch ($this->view)
		{
			case 'jltournamenttree':
				$this->project       = sportsmanagementModelProject::getProject(sportsmanagementModelProject::$cfg_which_database);
				$this->overallconfig = sportsmanagementModelProject::getOverallConfig(sportsmanagementModelProject::$cfg_which_database);
				$this->config        = sportsmanagementModelProject::getTemplateConfig('treetonode', sportsmanagementModelProject::$cfg_which_database);
				$this->config        = array_merge($this->overallconfig, $this->config);
				break;
			case 'ical':
				$this->project = sportsmanagementModelProject::getProject(sportsmanagementModelProject::$cfg_which_database);
				break;
			case 'scoresheet':
				$this->project = sportsmanagementModelProject::getProject(sportsmanagementModelProject::$cfg_which_database);
				break;
			case 'resultsranking':
			case 'resultsmatrix':
				$this->project       = sportsmanagementModelProject::getProject(sportsmanagementModelProject::$cfg_which_database);
				$this->overallconfig = sportsmanagementModelProject::getOverallConfig(sportsmanagementModelProject::$cfg_which_database);
				break;
			case 'curve':
			case 'stats':
			case 'teamstats':
				$this->project       = sportsmanagementModelProject::getProject(sportsmanagementModelProject::$cfg_which_database);
				$this->overallconfig = sportsmanagementModelProject::getOverallConfig(sportsmanagementModelProject::$cfg_which_database);
				$this->config        = sportsmanagementModelProject::getTemplateConfig($this->getName(), sportsmanagementModelProject::$cfg_which_database);
				$this->flashconfig   = sportsmanagementModelProject::getTemplateConfig('flash', sportsmanagementModelProject::$cfg_which_database);
				$this->config        = array_merge($this->overallconfig, $this->config);
				break;
			default:
				$this->project       = sportsmanagementModelProject::getProject(sportsmanagementModelProject::$cfg_which_database);
				$this->overallconfig = sportsmanagementModelProject::getOverallConfig(sportsmanagementModelProject::$cfg_which_database);
				$this->config        = sportsmanagementModelProject::getTemplateConfig($this->getName(), sportsmanagementModelProject::$cfg_which_database);
				$this->config        = array_merge($this->overallconfig, $this->config);
				break;
		}

		/**
		 * flexible einstellung der div klassen im frontend
		 * da man nicht alle templates mit unterschiedlich bootstrap versionen
		 * abfangen kann. hier muss der anwender bei den templates hand anlegen
		 */
		$this->divclasscontainer = isset($this->config['divclasscontainer']) ? $this->config['divclasscontainer'] : 'container-fluid';
		$this->divclassrow       = isset($this->config['divclassrow']) ? $this->config['divclassrow'] : 'row-fluid';

		$this->init();

		$this->addToolbar();

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

	protected function addToolbar()
	{

	}

}
