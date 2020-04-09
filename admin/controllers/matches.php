<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage matches
 * @file       matches.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

/**
 * sportsmanagementControllermatches
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllermatches extends JSMControllerAdmin
{


	/**
	 * sportsmanagementControllermatches::__construct()
	 *
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();

	}

	/**
	 * Returns a reference to the global {@link JoomlaTuneAjaxResponse} object,
	 * only creating it if it doesn't already exist.
	 *
	 * @return JoomlaTuneAjaxResponse
	 */
	public static function getAjaxResponse()
	{
		static $instance = null;

		if (!is_object($instance))
		{
			$instance = new JoomlaTuneAjaxResponse('utf-8');
		}

		return $instance;
	}


	/**
	 * sportsmanagementControllermatches::removeEvent()
	 *
	 * @return void
	 */
	function removeEvent()
	{
		$event_id = Factory::getApplication()->input->getInt('event_id');
		$model    = $this->getModel();

		if (!$result = $model->deleteevent($event_id))
		{
			$result = "0" . "&" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_DELETE_EVENTS') . ': ' . $model->getError();
		}
		else
		{
			$result = "1" . "&" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_DELETE_EVENTS') . '&' . $event_id;
		}

		echo json_encode($result);
		Factory::getApplication()->close();
	}

	/**
	 * Proxy for getModel.
	 *
	 * @since 1.6
	 */
	public function getModel($name = 'match', $prefix = 'sportsmanagementModel', $config = Array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * sportsmanagementControllermatches::removeCommentary()
	 *
	 * @return void
	 */
	public function removeCommentary()
	{
		$event_id = Factory::getApplication()->input->getInt('event_id');
		$model    = $this->getModel();

		if (!$result = $model->deletecommentary($event_id))
		{
			$result = '0' . '&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_DELETE_COMMENTARY') . ': ' . $model->getError();
		}
		else
		{
			$result = '1' . '&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_DELETE_COMMENTARY') . '&' . $event_id;
		}

		echo json_encode($result);
		Factory::getApplication()->close();
	}

	/**
	 * sportsmanagementControllermatches::removeSubst()
	 *
	 * @return void
	 */
	function removeSubst()
	{
		$substid = Factory::getApplication()->input->getInt('substid', 0);
		$model   = $this->getModel();

		if (!$result = $model->removeSubstitution($substid))
		{
			$result = "0" . "&" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_REMOVE_SUBST') . ': ' . $model->getError();
		}
		else
		{
			$result = "1" . "&" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_REMOVE_SUBST') . '&' . $substid;
		}

		echo json_encode($result);
		Factory::getApplication()->close();
	}

	/**
	 * sportsmanagementControllermatches::saveevent()
	 *
	 * @return void
	 */
	function saveevent()
	{
		$option = Factory::getApplication()->input->getCmd('option');

		$data                   = array();
		$data['teamplayer_id']  = Factory::getApplication()->input->getInt('teamplayer_id');
		$data['projectteam_id'] = Factory::getApplication()->input->getInt('projectteam_id');
		$data['event_type_id']  = Factory::getApplication()->input->getInt('event_type_id');
		$data['event_time']     = Factory::getApplication()->input->getVar('event_time', '');
		$data['match_id']       = Factory::getApplication()->input->getInt('match_id');
		$data['event_sum']      = Factory::getApplication()->input->getVar('event_sum', '');
		$data['notice']         = Factory::getApplication()->input->getVar('notice', '');
		$data['notes']          = Factory::getApplication()->input->getVar('notes', '');

		// Diddipoeler
		$data['projecttime']  = Factory::getApplication()->input->getVar('projecttime', '');
		$data['useeventtime'] = Factory::getApplication()->input->getVar('useeventtime', '');
		$model                = $this->getModel();

		if (!$result = $model->saveevent($data))
		{
			$result = "0" . "&" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_EVENT') . ': ' . $model->getError();
		}
		else
		{
			$result = $result . "&" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_EVENT');
		}

		echo json_encode($result);
		Factory::getApplication()->close();
	}

	/**
	 * sportsmanagementControllermatches::savecomment()
	 *
	 * @return void
	 */
	function savecomment()
	{
		$data               = array();
		$data['event_time'] = Factory::getApplication()->input->getVar('event_time', '');
		$data['match_id']   = Factory::getApplication()->input->getInt('matchid');
		$data['type']       = Factory::getApplication()->input->getVar('type', '');
		$data['notes']      = Factory::getApplication()->input->getVar('notes', '');

		// Diddipoeler
		$data['projecttime'] = Factory::getApplication()->input->getVar('projecttime', '');

		$model = $this->getModel();

		if (!$result = $model->savecomment($data))
		{
			$result = '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_COMMENT') . ': ' . $model->getError();
		}
		else
		{
			$result = $result . '&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_COMMENT');
		}

		echo json_encode($result);
		Factory::getApplication()->close();
	}

	/**
	 * sportsmanagementControllermatches::savesubst()
	 *
	 * @return void
	 */
	function savesubst()
	{
		$data                        = array();
		$data['in']                  = Factory::getApplication()->input->getInt('in');
		$data['out']                 = Factory::getApplication()->input->getInt('out');
		$data['matchid']             = Factory::getApplication()->input->getInt('matchid');
		$data['in_out_time']         = Factory::getApplication()->input->getVar('in_out_time', '');
		$data['project_position_id'] = Factory::getApplication()->input->getInt('project_position_id');

		// Diddipoeler
		$data['projecttime'] = Factory::getApplication()->input->getVar('projecttime', '');
		$model               = $this->getModel();

		if (!$result = $model->savesubstitution($data))
		{
			$result = "0" . "&" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_SUBST') . ': ' . $model->getError();
		}
		else
		{
			$result = $model->getDbo()->insertid() . "&" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_SUBST');
		}

		echo json_encode($result);
		Factory::getApplication()->close();
	}

	/**
	 * sportsmanagementControllermatches::savestats()
	 *
	 * @return void
	 */
	function savestats()
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$post   = Factory::getApplication()->input->post->getArray(array());
		$model  = $this->getModel();

		if ($model->savestats($post))
		{
			$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_UPDATE_STATS');
		}
		else
		{
			$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_UPDATE_STATS');
		}

		if (Factory::getApplication()->input->getString('close', 0) == 1)
		{
			$link = 'index.php?option=' . $option . '&view=close&tmpl=component';
		}
		else
		{
			$link = 'index.php?option=' . $option . '&close=' . Factory::getApplication()->input->getString('close', 0) . '&tmpl=component&view=match&layout=editstats&id=' . $post['id'] . '&team=' . $post['team'];
		}

		$this->setRedirect($link, $msg);
	}

	/**
	 * sportsmanagementControllermatches::saveroster()
	 *
	 * @return void
	 */
	function saveroster()
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$post   = Factory::getApplication()->input->post->getArray(array());
		$model  = $this->getModel();

		$positions              = $model->getProjectPositionsOptions(0, 1, $post['project_id']);
		$staffpositions         = $model->getProjectPositionsOptions(0, 2, $post['project_id']);
		$post['positions']      = $positions;
		$post['staffpositions'] = $staffpositions;

		if ($model->updateRoster($post))
		{
			$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_MR_PLAYER') . '<br />';
			$msg .= Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_MR_PLAYER_TRIKOT') . '<br />';
		}
		else
		{
			$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVE_MR_PLAYER') . '<br />';
			$msg .= Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVE_MR_PLAYER_TRIKOT') . '<br />';
		}

		if ($model->updateStaff($post))
		{
			$msg .= Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_MR_STAFF') . '<br />';
		}
		else
		{
			$msg .= Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVE_MR_STAFF') . '<br />';
		}

		if (Factory::getApplication()->input->getString('close', 0) == 1)
		{
			$link = 'index.php?option=' . $option . '&view=close&tmpl=component';
		}
		else
		{
			$link = 'index.php?option=' . $option . '&close=' . Factory::getApplication()->input->getString('close', 0) . '&tmpl=component&view=match&layout=editlineup&id=' . $post['id'] . '&team=' . $post['team'];
		}

		$this->setRedirect($link, $msg);
	}

	/**
	 * sportsmanagementControllermatches::saveReferees()
	 *
	 * @return void
	 */
	function saveReferees()
	{
		$option            = Factory::getApplication()->input->getCmd('option');
		$post              = Factory::getApplication()->input->post->getArray(array());
		$model             = $this->getModel();
		$positions         = $model->getProjectPositionsOptions(0, 3, $post['project_id']);
		$post['positions'] = $positions;

		if ($model->updateReferees($post))
		{
			$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_MR_REFEREES');
		}
		else
		{
			$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_MR_REFEREES_ERROR') . '<br />';
		}

		if (Factory::getApplication()->input->getString('close', 0) == 1)
		{
			$link = 'index.php?option=' . $option . '&view=close&tmpl=component';
		}
		else
		{
			$link = 'index.php?option=' . $option . '&close=' . Factory::getApplication()->input->getString('close', 0) . '&tmpl=component&view=match&layout=editreferees&id=' . $post['id'];
		}

		$this->setRedirect($link, $msg);
	}

	/**
	 * sportsmanagementControllermatches::count_result_yes()
	 *
	 * @return void
	 */
	function count_result_yes()
	{
		$model = $this->getModel();
		$model->count_result(1);
		$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
	}

	/**
	 * sportsmanagementControllermatches::count_result_no()
	 *
	 * @return void
	 */
	function count_result_no()
	{
		$model = $this->getModel();
		$model->count_result(0);
		$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
	}

	/**
	 * Method to update checked matches
	 *
	 * @access public
	 * @return boolean    True on success
	 */
	function saveshort()
	{
		$model = $this->getModel();
		$model->saveshort();
		$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
	}
}
