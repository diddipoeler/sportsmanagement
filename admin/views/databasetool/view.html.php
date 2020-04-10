<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage databasetool
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

/**
 * sportsmanagementViewDatabaseTool
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewDatabaseTool extends sportsmanagementView
{

	/**
	 * sportsmanagementViewDatabaseTool::init()
	 *
	 * @param   mixed  $tpl
	 *
	 * @return void
	 */
	public function init($tpl = null)
	{
		$db       = sportsmanagementHelper::getDBConnection();
		$uri      = Factory::getURI();
		$model    = $this->getModel();
		$option   = Factory::getApplication()->input->getCmd('option');
		$app      = Factory::getApplication();
		$document = Factory::getDocument();

		// $this->state = $this->get('State');
		$command = Factory::getApplication()->input->getCmd('task');

		$this->assign('request_url', $uri->toString());

		$this->task = $command;

		// Explode the controller.task command.
		// List ($this->controller, $this->task) = explode('.', $command);

		$this->step = $app->getUserState("$option.step", '0');

		if (!$this->step)
		{
			$this->step = 0;
		}

		$this->assign('request_url', $uri->toString());
		$this->work_table = '';
		$this->bar_value  = 0;

		switch ($this->task)
		{
			case 'truncate':
			case 'optimize':
			case 'repair':
				$jsm_tables = $model->getSportsManagementTables();

				$this->assign('totals', count(sportsmanagementModeldatabasetool::$jsmtables));

				if ($this->step < count(sportsmanagementModeldatabasetool::$jsmtables))
				{
					$successTable     = $model->setSportsManagementTableQuery(sportsmanagementModeldatabasetool::$jsmtables[$this->step], $this->task);
					$this->work_table = sportsmanagementModeldatabasetool::$jsmtables[$this->step];

					// $this->bar_value = round( ( $this->step * 100 / $this->totals ), 0);
					$this->assign('bar_value', round(($this->step * 100 / $this->totals), 0));

					// $model::$bar_value = round( ( $this->step * 100 / $this->totals ), 0) ;
				}
				else
				{
					$this->step = 0;

					// $this->bar_value = 100;
					$this->assign('bar_value', 100);
					$this->work_table = '';
				}

				if (version_compare(JVERSION, '3.0.0', 'ge'))
				{
				}
				else
				{
					$javascript = "\n";
					$javascript .= '            jQuery(function() {' . "\n";
					$javascript .= '    var progressbar = jQuery( "#progressbar" ),' . "\n";
					$javascript .= '      progressLabel = jQuery( ".progress-label" );' . "\n";
					$javascript .= '     progressbar.progressbar({' . "\n";
					$javascript .= '      value: ' . $this->bar_value . ',' . "\n";
					$javascript .= '      create: function() {' . "\n";
					$javascript .= '        progressLabel.text( "' . $this->task . ' -> ' . $this->work_table . ' ' . '" + progressbar.progressbar( "value" ) + "%" );' . "\n";
					$javascript .= '      },' . "\n";
					$javascript .= '      change: function() {' . "\n";
					$javascript .= '        progressLabel.text( progressbar.progressbar( "value" ) + "%" );' . "\n";
					$javascript .= '      },' . "\n";
					$javascript .= '      complete: function() {' . "\n";
					$javascript .= '        progressLabel.text( "Complete!" );' . "\n";
					$javascript .= '      }' . "\n";
					$javascript .= '    });' . "\n";
					$javascript .= '     function progress() {' . "\n";
					$javascript .= '      var val = progressbar.progressbar( "value" ) || 0;' . "\n";
					$javascript .= '       progressbar.progressbar( "value", ' . $this->bar_value . ' );' . "\n";
					$javascript .= '       if ( val < 99 ) {' . "\n";
					$javascript .= '        setTimeout( progress, 100 );' . "\n";
					$javascript .= '      }' . "\n";
					$javascript .= '    }' . "\n";
					$javascript .= '     setTimeout( progress, 3000 );' . "\n";
					$javascript .= '  });' . "\n";
					$document->addScriptDeclaration($javascript);
				}

				$this->step++;
				$app->setUserState("$option.step", $this->step);
				break;
		}

		// Load mooTools
		// HTMLHelper::_('behavior.framework', true);

		// Load our Javascript
		$document->addStylesheet(Uri::base() . 'components/' . $option . '/assets/css/progressbar.css');

		// $document->addScript(Uri::base().'components/'.$option.'/assets/js/progressbar.js');

		/*
		// Load our Javascript
		$document = Factory::getDocument();
		$document->addScript('../media/com_joomlaupdate/json2.js');
		$document->addScript('../media/com_joomlaupdate/encryption.js');
		$document->addScript('../media/com_joomlaupdate/update.js');
		HTMLHelper::_('script', 'system/progressbar.js', true, true);
		HTMLHelper::_('stylesheet', 'media/mediamanager.css', array(), true);
		*/
		// $this->addToolbar();
		// parent::display( $tpl );
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar()
	{
		// Get a refrence of the page instance in joomla
		$document = Factory::getDocument();

		// Set toolbar items for the page
		$stylelink = '<link rel="stylesheet" href="' . Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css' . '" type="text/css" />' . "\n";
		$document->addCustomTag($stylelink);

		// Set toolbar items for the page
		ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TITLE'), 'database');
		ToolbarHelper::back();
		ToolbarHelper::divider();
		sportsmanagementHelper::ToolbarButtonOnlineHelp();
		ToolbarHelper::preferences(Factory::getApplication()->input->getCmd('option'));
	}

}
