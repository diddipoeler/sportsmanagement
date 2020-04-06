<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       update.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage controllers
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Filesystem\File;

/**
 * sportsmanagementControllerUpdate
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllerUpdate extends BaseController
{

	/**
	 * sportsmanagementControllerUpdate::__construct()
	 *
	 * @return void
	 */
	function __construct()
	{
		// Register Extra tasks
		parent::__construct();
	}


	/**
	 * sportsmanagementControllerUpdate::display()
	 *
	 * @param   bool $cachable
	 * @param   bool $urlparams
	 * @return void
	 */
	function display($cachable = false, $urlparams = false)
	{
		//		$document = Factory::getDocument();
		//		$model=$this->getModel('updates');
		$viewType = $document->getType();
		$view = $this->getView('updates', $viewType);

		//      $view->setModel($model,true);
		$view->setLayout('updates');

		parent::display();
	}


	/**
	 * sportsmanagementControllerUpdate::save()
	 *
	 * @return void
	 */
	function save()
	{
		$post = Factory::getApplication()->input->post->getArray(array());
		$file_name = Factory::getApplication()->input->getVar('file_name');
		$path = explode('/', $file_name);

		if (count($path) > 1)
		{
			$filepath = JPATH_COMPONENT_SITE . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . $path[0] . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . $path[1];
		}
		else
		{
			$filepath = JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'updates' . DIRECTORY_SEPARATOR . $path[0];
		}

		$model = $this->getModel('updates');
		echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_FROM_FILE', '<b>' . $filepath . '</b>');

		if (File::exists($filepath))
		{
			$model->loadUpdateFile($filepath, $file_name);
		}
		else
		{
			echo Text::_('Update file not found!');
		}
	}
}
