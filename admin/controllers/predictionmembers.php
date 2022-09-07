<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage controllers
 * @file       predictionmembers.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementControllerpredictionmembers
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllerpredictionmembers extends JSMControllerAdmin
{

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see   BaseController
	 * @since 1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Reference global application object
		$this->jsmapp = Factory::getApplication();

		// JInput object
		$this->jsmjinput = $this->jsmapp->input;

	}

	/**
	 * sportsmanagementControllerpredictionmembers::save_memberlist()
	 *
	 * @return void
	 */
	function save_memberlist()
	{

		// Check for request forgeries
		Session::checkToken() or jexit(\Text::_('JINVALID_TOKEN'));

		$model = $this->getModel();
		$msg   = $model->save_memberlist();
		if (version_compare(JVERSION, '4.0.0', 'ge'))
		{
		//$this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', $msg);
		}
		else
		{
		$this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', $msg);	
		}

	}

	/**
	 * Proxy for getModel.
	 *
	 * @since 1.6
	 */
	public function getModel($name = 'predictionmember', $prefix = 'sportsmanagementModel', $config = Array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * sportsmanagementControllerpredictionmembers::editlist()
	 *
	 * @return void
	 */
	function editlist()
	{
		$msg  = '';
		$link = 'index.php?option=com_sportsmanagement&view=predictionmembers&layout=editlist';

		// Echo $msg;
		$this->setRedirect($link, $msg);

	}

	/**
	 * sportsmanagementControllerpredictionmembers::sendReminder()
	 *
	 * @return void
	 */
	function reminder()
	{
		/**
		 * This will send an email to all members of the prediction game with reminder option enabled. Are you sure?
		 */
		$post  = $this->jsmjinput->post->getArray();
		$cid   = $this->jsmjinput->getVar('cid', null, 'post', 'array');
		$pgmid = Factory::getApplication()->input->getVar('prediction_id', 0, 'post', 'INT');

		if ($pgmid == 0)
		{
			Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_CTRL_SELECT_ERROR'), Log::WARNING, 'jsmerror');
		}

		$msg = '';
		$d   = ' - ';

		$model = $this->getModel('predictionmember');
		$model->sendEmailtoMembers($cid, $pgmid);

		$link = 'index.php?option=com_sportsmanagement&view=predictionmembers';

		// Echo $msg;
		$this->setRedirect($link, $msg);
	}

	/**
	 * sportsmanagementControllerpredictionmembers::publish()
	 *
	 * @return void
	 */
	function publish()
	{
		$cids = Factory::getApplication()->input->getVar('cid', array(), 'post', 'array');
		ArrayHelper::toInteger($cids);
		$predictionGameID = Factory::getApplication()->input->getVar('prediction_id', '', 'post', 'int');

		if (count($cids) < 1)
		{
			Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_CTRL_SEL_MEMBER_APPR'), Log::ERROR, 'jsmerror');
		}

		$model = $this->getModel('predictionmember');

		if (!$model->publishpredmembers($cids, 1, $predictionGameID))
		{
			echo "<script> alert( '" . $model->getError(true) . "' ); window.history.go(-1); </script>\n";
		}

		$this->setRedirect('index.php?option=com_sportsmanagement&view=predictionmembers');
	}

	/**
	 * sportsmanagementControllerpredictionmembers::unpublish()
	 *
	 * @return void
	 */
	function unpublish()
	{
		$cids = Factory::getApplication()->input->getVar('cid', array(), 'post', 'array');
		ArrayHelper::toInteger($cids);
		$predictionGameID = Factory::getApplication()->input->getVar('prediction_id', '', 'post', 'int');

		if (count($cids) < 1)
		{
			Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_CTRL_SEL_MEMBER_REJECT'), Log::ERROR, 'jsmerror');
		}

		$model = $this->getModel('predictionmember');

		if (!$model->publishpredmembers($cids, 0, $predictionGameID))
		{
			echo "<script> alert( '" . $model->getError(true) . "' ); window.history.go(-1); </script>\n";
		}

		$this->setRedirect('index.php?option=com_sportsmanagement&view=predictionmembers');
	}

	/**
	 * sportsmanagementControllerpredictionmembers::remove()
	 *
	 * @return void
	 */
	function remove()
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		$d   = ' - ';
		$msg = '';
		$cid = Factory::getApplication()->input->getVar('cid', array(), 'post', 'array');
		ArrayHelper::toInteger($cid);
		$prediction_id = Factory::getApplication()->input->getInt('prediction_id', (-1), 'post');

		if (count($cid) < 1)
		{
			Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_CTRL_DEL_ITEM'), Log::ERROR, 'jsmerror');
		}

		$model = $this->getModel('predictionmember');

		if (!$model->deletePredictionResults($cid, $prediction_id))
		{
			$msg .= $d . Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_CTRL_DEL_MSG');
		}

		$msg .= $d . Text::_('COM_SPORTSMANAGEMENTADMIN_PMEMBER_CTRL_DEL_PRESULTS');

		if (!$model->deletePredictionMembers($cid))
		{
			$msg .= Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_CTRL_DEL_PMEMBERS_MSG');
		}

		$msg .= $d . Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_CTRL_DEL_PMEMBERS');

		$link = 'index.php?option=com_sportsmanagement&view=predictionmembers';

		// Echo $msg;
		$this->setRedirect($link, $msg);
	}
}
