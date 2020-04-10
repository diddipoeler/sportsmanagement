<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage controllers
 * @file       template.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementControllertemplate
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllertemplate extends FormController
{

	/**
	 * sportsmanagementControllertemplate::__construct()
	 *
	 * @return void
	 */
	function __construct()
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');
		parent::__construct();

		// Register Extra tasks
		$this->registerTask('reset', 'remove');
		$this->registerTask('update', 'update');
	}

	/**
	 * sportsmanagementControllertemplate::remove()
	 *
	 * @return
	 */
	function remove()
	{
		$cid = Factory::getApplication()->input->getVar('cid', array(0), 'post', 'array');
		ArrayHelper::toInteger($cid);
		$isMaster = Factory::getApplication()->input->getVar('isMaster', array(), 'post', 'array');
		ArrayHelper::toInteger($isMaster);

		if (count($cid) < 1)
		{
			Log::add(Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TO_DELETE'), Log::ERROR, 'jsmerror');
		}

		foreach ($cid AS $id)
		{
			if ($isMaster[$id])
			{
				echo "<script> alert('" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_CTRL_DELETE_WARNING') . "'); window.history.go(-1); </script>\n";

				return;
			}
		}

		$model = $this->getModel('template');

		if (!$model->delete($cid))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$msg = Text::_("COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_RESET_SUCCESS");
		$this->setRedirect('index.php?option=com_sportsmanagement&view=templates&pid=' . Factory::getApplication()->input->getInt("pid", 0), $msg);
	}

	/**
	 * sportsmanagementControllertemplate::update()
	 *
	 * adds all "new" config keys and their values to selected template
	 * exsisting confing keys stay untouched
	 *
	 * @return
	 */
	function update()
	{
		$cid = Factory::getApplication()->input->getVar('cid', array(0), 'post', 'array');
		ArrayHelper::toInteger($cid);
		$isMaster = Factory::getApplication()->input->getVar('isMaster', array(), 'post', 'array');
		ArrayHelper::toInteger($isMaster);

		if (count($cid) < 1)
		{
			Log::add(Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TO_DELETE'), Log::ERROR, 'jsmerror');
		}

		foreach ($cid AS $id)
		{
			if ($isMaster[$id])
			{
				echo "<script> alert('" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_CTRL_DELETE_WARNING') . "'); window.history.go(-1); </script>\n";

				return;
			}
		}

		$model = $this->getModel('template');

		if (!$model->update($cid))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$msg = Text::_("COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_UPDATE_SUCCESS");
		$this->setRedirect('index.php?option=com_sportsmanagement&view=templates&pid=' . Factory::getApplication()->input->getInt("pid", 0), $msg);
	}

	/**
	 * sportsmanagementControllertemplate::masterimport()
	 *
	 * @return void
	 */
	function masterimport()
	{
		$templateid = Factory::getApplication()->input->getVar('templateid', 0, 'post', 'int');
		$projectid  = Factory::getApplication()->input->getVar('pid', 0, 'post', 'int');
		$model      = $this->getModel('template');

		if ($templateid)
		{
			if ($model->import($templateid, $projectid))
			{
				$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_CTRL_IMPORTED_TEMPLATE');
			}
			else
			{
				$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_CTRL_ERROR_IMPORT_TEMPLATE') . $model->getError();
			}
		}

		$this->setRedirect('index.php?option=com_sportsmanagement&view=templates&pid=' . $projectid, $msg);
	}

}
