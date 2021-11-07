<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage prediction
 * @file       predictionusers.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Session\Session;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * sportsmanagementControllerPredictionUsers
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllerPredictionUsers extends BaseController
{


	/**
	 * sportsmanagementControllerPredictionUsers::display()
	 *
	 * @param   bool  $cachable
	 * @param   bool  $urlparams
	 *
	 * @return void
	 */
	function display($cachable = false, $urlparams = false)
	{

		parent::display($cachable, $urlparams = false);
	}

	/**
	 * sportsmanagementControllerPredictionUsers::cancel()
	 *
	 * @return
	 */
	function cancel()
	{
		Factory::getApplication()->redirect(str_ireplace('&layout=edit', '', Factory::getURI()->toString()));
	}

	/**
	 * sportsmanagementControllerPredictionUsers::select()
	 *
	 * @return
	 */
	function select()
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$post   = $jinput->post->getArray(array());
		$link   = JSMPredictionHelperRoute::getPredictionMemberRoute($post['prediction_id'], $post['uid'], $post['task'], $post['pj'], $post['pggroup'], $post['r']);
		$this->setRedirect($link);
	}

	/**
	 * sportsmanagementControllerPredictionUsers::savememberdata()
	 *
	 * @return
	 */
	function savememberdata()
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$option     = Factory::getApplication()->input->getCmd('option');
		$optiontext = strtoupper(Factory::getApplication()->input->getCmd('option') . '_');
		$app        = Factory::getApplication();
		$document   = Factory::getDocument();

		$msg  = '';
		$link = '';

		$post             = Factory::getApplication()->input->post->getArray(array());
		$predictionGameID = Factory::getApplication()->input->getVar('prediction_id', '', 'post', 'int');
		$joomlaUserID     = Factory::getApplication()->input->getVar('user_id', '', 'post', 'int');

		// $model            = $this->getModel('predictionusers');
		$modelusers   = BaseDatabaseModel::getInstance("predictionusers", "sportsmanagementModel");
		$model        = BaseDatabaseModel::getInstance("prediction", "sportsmanagementModel");
		$user         = Factory::getUser();
		$isMember     = $model->checkPredictionMembership();
		$allowedAdmin = $model->getAllowed();

		if ((($user->id != $joomlaUserID)) && (!$allowedAdmin))
		{
			$msg  .= Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_CONTROLLER_ERROR_1');
			$link = Factory::getURI()->toString();
		}
		else
		{
			if ((!$isMember) && (!$allowedAdmin))
			{
				$msg  .= Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_CONTROLLER_ERROR_2');
				$link = Factory::getURI()->toString();
			}
			else
			{
				if (!$modelusers->savememberdata())
				{
					$msg  .= Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_CONTROLLER_ERROR_3');
					$link = Factory::getURI()->toString();
				}
				else
				{
					$msg  .= Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_CONTROLLER_MSG_1');
					$link = Factory::getURI()->toString();
				}
			}
		}

		// Echo '<br />';
		// echo '' . $link . '<br />';
		// echo '' . $msg . '<br />';
		$this->setRedirect($link, $msg);
	}

	/**
	 * sportsmanagementControllerPredictionUsers::selectprojectround()
	 *
	 * @return
	 */
	function selectprojectround()
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput      = $app->input;
		$pID         = $jinput->getVar('prediction_id', '0');
		$pggroup     = $jinput->getVar('pggroup', '0');
		$pggrouprank = $jinput->getVar('pggrouprank', '0');
		$pjID        = $jinput->getVar('pj', '0');
		$rID         = $jinput->getVar('r', '0');
		$set_pj      = $jinput->getVar('set_pj', '0');
		$set_r       = $jinput->getVar('set_r', '0');

		$link = JSMPredictionHelperRoute::getPredictionMemberRoute($pID, $uID, null, $pjID, $pggroup, $rID);

		// Echo '<br />' . $link . '<br />';
		$this->setRedirect($link);
	}

}
