<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jlextdbbimport
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;

/**
 * sportsmanagementViewjlextdbbimport
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewjlextdbbimport extends sportsmanagementView
{

	/**
	 * sportsmanagementViewjlextdbbimport::init()
	 *
	 * @return
	 */
	public function init()
	{

		if ($this->getLayout() == 'default')
		{
			$this->_displayDefault($tpl);

			return;
		}

		$input = Factory::getApplication()->input;

		$uri               = Factory::getURI();
		$config            = ComponentHelper::getParams('com_media');
		$files             = $input->get('files');
		$post              = $input->post;
		$this->request_url = $uri->toString();
		$this->config      = $config;

		$revisionDate       = '2011-04-28 - 12:00';
		$this->revisionDate = $revisionDate;

	}


	/**
	 * sportsmanagementViewjlextdbbimport::_displayDefault()
	 *
	 * @param   mixed  $tpl
	 *
	 * @return
	 */
	function _displayDefault($tpl)
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$db     = Factory::getDBO();
		$uri    = Factory::getURI();
		$user   = Factory::getUser();

		// $model = $this->getModel('project') ;
		// $projectdata = $this->get('Data');
		// $this->assignRef( 'name', $projectdata->name);

		$model         = $this->getModel();
		$project       = $app->getUserState($option . 'project');
		$this->project = $project;
		$config        = ComponentHelper::getParams('com_media');

		$this->request_url    = $uri->toString();
		$this->config         = $config;
		$revisionDate         = '2011-04-28 - 12:00';
		$this->revisionDate   = $revisionDate;
		$import_version       = 'NEW';
		$this->import_version = $import_version;

	}


	/**
	 * sportsmanagementViewjlextdbbimport::_displayDefaultUpdate()
	 *
	 * @param   mixed  $tpl
	 *
	 * @return
	 */
	function _displayDefaultUpdate($tpl)
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		$db            = Factory::getDBO();
		$uri           = Factory::getURI();
		$user          = Factory::getUser();
		$model         = $this->getModel();
		$project       = $app->getUserState($option . 'project');
		$this->project = $project;
		$config        = ComponentHelper::getParams('com_media');

		$uploadArray       = $app->getUserState($option . 'uploadArray', array());
		$lmoimportuseteams = $app->getUserState($option . 'lmoimportuseteams');
		$whichfile         = $app->getUserState($option . 'whichfile');

		// $delimiter = $app->getUserState ( $option . 'delimiter' );

		$this->uploadArray = $uploadArray;

		$this->importData = $model->getUpdateData();

		// $this->assignRef('xml',$model->getData());

		// Parent::display ( $tpl );
	}


	/**
	 * sportsmanagementViewjlextdbbimport::addToolbar()
	 *
	 * @return
	 */
	protected function addToolbar()
	{
		// Get a refrence of the page instance in joomla
		$document = Factory::getDocument();
		$app      = Factory::getApplication();
		$jinput   = $app->input;
		$option   = $jinput->getCmd('option');

		// Set toolbar items for the page
		$stylelink = '<link rel="stylesheet" href="' . Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css' . '" type="text/css" />' . "\n";
		$document->addCustomTag($stylelink);

		// Set toolbar items for the page
		ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT'), 'dbb-cpanel');

		parent::addToolbar();

	}
}

