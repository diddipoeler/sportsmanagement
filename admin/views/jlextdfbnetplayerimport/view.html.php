<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage jlextdfbnetplayerimport
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;

/**
 * sportsmanagementViewjlextdfbnetplayerimport
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2013
 * @access    public
 */
class sportsmanagementViewjlextdfbnetplayerimport extends sportsmanagementView
{

	/**
	 * sportsmanagementViewjlextdfbnetplayerimport::init()
	 *
	 * @return
	 */
	function init()
	{

		if ($this->getLayout() == 'default')
		{
			$this->_displayDefault($tpl);

			return;
		}

			$config = ComponentHelper::getParams('com_media');
			$post = $this->jinput->post;
			$files = $this->jinput->get('files');

			  $this->config    = $config;

			  $revisionDate = '2011-04-28 - 12:00';
			$this->revisionDate    = $revisionDate;
			/**
 *         build the html select list for seasons
 */
			$seasons[]    = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SEASON_FILTER'), 'id', 'name');
			$mdlSeasons = BaseDatabaseModel::getInstance('Seasons', 'sportsmanagementModel');

		if (ComponentHelper::getParams($this->option)->get('show_debug_info_backend'))
		{
		}

			  $allSeasons = $mdlSeasons->getSeasons();
			$seasons = array_merge($seasons, $allSeasons);
			$lists['seasons'] = HTMLHelper::_(
				'select.genericList',
				$seasons,
				'filter_season',
				'class="inputbox" style="width:220px"',
				'id',
				'name',
				0
			);

		unset($seasons);
		$this->lists        = $lists;
	}



	/**
	 * sportsmanagementViewjlextdfbnetplayerimport::_displayDefault()
	 *
	 * @param   mixed $tpl
	 * @return void
	 */
	function _displayDefault($tpl)
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

			  $db        = sportsmanagementHelper::getDBConnection();
		$uri = Factory::getURI();
		$user = Factory::getUser();

		$model = $this->getModel();
		$project = $app->getUserState($option . 'project');
		$this->project    = $project;
		$config = ComponentHelper::getParams('com_media');

			  $this->request_url    = $uri->toString();
		$this->config    = $config;
		$revisionDate = '2011-04-28 - 12:00';
		$this->revisionDate    = $revisionDate;
		$import_version = 'NEW';
		$this->import_version    = $import_version;

			  parent::display($tpl);
	}


	/**
	 * sportsmanagementViewjlextdfbnetplayerimport::_displayDefaultUpdate()
	 *
	 * @param   mixed $tpl
	 * @return void
	 */
	function _displayDefaultUpdate($tpl)
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

			  $db        = sportsmanagementHelper::getDBConnection();
		$uri = Factory::getURI();
		$user = Factory::getUser();
		$model = $this->getModel();
		$project = $app->getUserState($option . 'project');
		$this->project    = $project;
		$config = ComponentHelper::getParams('com_media');
		$uploadArray = $app->getUserState($option . 'uploadArray', array ());
		$lmoimportuseteams = $app->getUserState($option . 'lmoimportuseteams');
		$whichfile = $app->getUserState($option . 'whichfile');
		$this->uploadArray    = $uploadArray;
		$this->importData    = $model->getUpdateData();

		parent::display($tpl);
	}

	/**
	 * sportsmanagementViewjlextdfbnetplayerimport::addToolbar()
	 *
	 * @return void
	 */
	protected function addToolbar()
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

			  $document    = Factory::getDocument();
		$stylelink = '<link rel="stylesheet" href="' . Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css' . '" type="text/css" />' . "\n";
		$document->addCustomTag($stylelink);

			  ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT'), 'dfbnet');
		ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=extensions');
		ToolbarHelper::divider();
		sportsmanagementHelper::ToolbarButtonOnlineHelp();
		ToolbarHelper::preferences($option);

	}
}

