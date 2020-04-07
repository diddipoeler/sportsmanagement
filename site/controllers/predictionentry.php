<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       predictionentry.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage prediction
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;

/**
 * sportsmanagementControllerPredictionEntry
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllerPredictionEntry extends BaseController
{

	/**
	 * sportsmanagementControllerPredictionEntry::__construct()
	 *
	 * @return void
	 */
	function __construct()
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();
		$document = Factory::getDocument();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		parent::__construct();
	}

	/**
	 * sportsmanagementControllerPredictionEntry::display()
	 *
	 * @param   bool $cachable
	 * @param   bool $urlparams
	 * @return void
	 */
	function display($cachable = false, $urlparams = false)
	{

		parent::display($cachable, $urlparams = false);
	}

	/**
	 * sportsmanagementControllerPredictionEntry::register()
	 *
	 * @return void
	 */
	function register()
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();
		$document = Factory::getDocument();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		JSession::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$msg = '';
		$link = '';
		$post = Factory::getApplication()->input->post->getArray(array());

		$predictionGameID = Factory::getApplication()->input->getVar('prediction_id', '', 'post', 'int');
		$joomlaUserID = Factory::getApplication()->input->getVar('user_id', '', 'post', 'int');
		$approved = Factory::getApplication()->input->getVar('approved', 0, '', 'int');

		$model = $this->getModel('Prediction');
		$mdlPredictionEntry = BaseDatabaseModel::getInstance("PredictionEntry", "sportsmanagementModel");
		$user = Factory::getUser();
		$isMember = $model->checkPredictionMembership();

		if (( $user->id != $joomlaUserID))
		{
			$msg .= Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_ERROR_1');
			$link = Uri::getInstance()->toString();
		}
		else
		{
			if ($isMember)
			{
				$msg .= Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_ERROR_4');
				$link = Uri::getInstance()->toString();
			}
			else
			{
				$post['registerDate'] = HTMLHelper::date($input = 'now', 'Y-m-d h:i:s', false);

				if (!$mdlPredictionEntry->store($post))
				{
					$msg .= Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_ERROR_5');
					$link = Uri::getInstance()->toString();
				}
				else
				{
					$cids = array();
					$cids[] = $mdlPredictionEntry->getDbo()->insertid();
					ArrayHelper::toInteger($cids);

					$msg .= Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_MSG_2');

					if ($model->sendMembershipConfirmation($cids))
					{
						$msg .= ' - ';
						$msg .= Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_MSG_3');
					}
					else
					{
						$msg .= ' - ';
						$msg .= Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_ERROR_6');
					}

					$params = array('option' => 'com_sportsmanagement',
						'view' => 'predictionentry',
						'prediction_id' => $predictionGameID,
						's' => '1');

						$query = sportsmanagementHelperRoute::buildQuery($params);
						$link = Route::_('index.php?' . $query, false);
				}
			}
		}

		echo '<br /><br />';
		echo '#' . $msg . '#<br />';
		$this->setRedirect($link, $msg);
	}

	/**
	 * sportsmanagementControllerPredictionEntry::select()
	 *
	 * @return void
	 */
	function select()
	{
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;

		JSession::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$pID = Factory::getApplication()->input->getVar('prediction_id', '', 'post', 'int');
		$uID = Factory::getApplication()->input->getVar('uid', null, 'post', 'int');

		if (empty($uID))
		{
			$uID = null;
		}

		$link = JSMPredictionHelperRoute::getPredictionTippEntryRoute($pID, $uID);

		// Echo '<br />' . $link . '<br />';
		$this->setRedirect($link);
	}

	/**
	 * sportsmanagementControllerPredictionEntry::selectprojectround()
	 *
	 * @return void
	 */
	function selectprojectround()
	{
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;

		JSession::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$pID = $jinput->get('prediction_id', 0, '');
		$groupID = $jinput->get('pggroup', 0, '');
		$pjID = $jinput->get('pj', 0, '');
		$rID = $jinput->get('r', 0, '');
		$uID = $jinput->get('uid', 0, '');
		$link = JSMPredictionHelperRoute::getPredictionTippEntryRoute($pID, $uID, $rID, $pjID, $groupID);
		$this->setRedirect($link);
	}

	/**
	 * Proxy for getModel
	 *
	 * @param   string $name   The model name. Optional.
	 * @param   string $prefix The class prefix. Optional.
	 *
	 * @return object    The model.
	 * @since  1.6
	 */
	function getModel($name = 'predictionentry', $prefix = 'sportsmanagementModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * sportsmanagementControllerPredictionEntry::addtipp()
	 *
	 * @return void
	 */
	function addtipp()
	{
		JSession::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app = Factory::getApplication();
		$document = Factory::getDocument();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$post = $jinput->post->getArray();

		$msg = '';
		$link = '';

		$predictionGameID = $jinput->getVar('prediction_id', '', 'post', 'int');
		$joomlaUserID = $jinput->getVar('user_id', '', 'post', 'int');
		$memberID = $jinput->getVar('memberID', '', 'post', 'int');
		$round_id = $jinput->getVar('round_id', '', 'post', 'int');
		$pjID = $jinput->getVar('pjID', '', 'post', 'int');
		$set_r = $jinput->getVar('set_r', '', 'post', 'int');
		$set_pj = $jinput->getVar('set_pj', '', 'post', 'int');

		$model = $this->getModel('Prediction');
		$user = Factory::getUser();
		$isMember = $model->checkPredictionMembership();
		$allowedAdmin = $model->getAllowed();

		if (( ( $user->id != $joomlaUserID ) ) && (!$allowedAdmin ))
		{
			$msg .= Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_ERROR_1');
			$link = Uri::getInstance()->toString();
		}
		else
		{
			if ((!$isMember ) && (!$allowedAdmin ))
			{
				$msg .= Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_ERROR_2');
				$link = Uri::getInstance()->toString();
			}
			else
			{
				if ($pjID != $set_pj)
				{
					$params = array('option' => 'com_sportsmanagement',
						'view' => 'predictionentry',
						'prediction_id' => $predictionGameID,
						'pj' => $set_pj
							);

							$query = sportsmanagementHelperRoute::buildQuery($params);
							$link = Route::_('index.php?' . $query, false);
							$this->setRedirect($link);
				}

				if ($round_id != $set_r)
				{
					$params = array('option' => 'com_sportsmanagement',
					'view' => 'predictionentry',
					'prediction_id' => $predictionGameID,
					'r' => $set_r,
					'pj' => $pjID
					);

					$query = sportsmanagementHelperRoute::buildQuery($params);
					$link = Route::_('index.php?' . $query, false);
					$this->setRedirect($link);
				}

				$model = $this->getModel('PredictionEntry');

				if (!$model->savePredictions($allowedAdmin))
				{
					$msg .= Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_ERROR_3');
					$link = Factory::getURI()->toString();
				}
				else
				{
					$msg .= Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_MSG_1');
					$link = Uri::getInstance()->toString();
				}
			}
		}

		$this->setRedirect($link, $msg);
	}

}

