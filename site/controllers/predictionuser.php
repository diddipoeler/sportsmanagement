<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage prediction
 * @file       predictionuser.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Session\Session;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * sportsmanagementControllerPredictionUsers
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllerPredictionUsers extends FormController
{

	/**
	 * sportsmanagementControllerPredictionUsers::display()
	 *
	 * @return void
	 */
	function display()
	{
		$this->showprojectheading();
		$this->showbackbutton();
		$this->showfooter();
	}

	/**
	 * sportsmanagementControllerPredictionUsers::cancel()
	 *
	 * @return void
	 */
	function cancel()
	{
		
		/*$uri = JUri::getInstance(); 
		$current_uri = $uri->toString();
		Factory::getApplication()->redirect(str_ireplace('&layout=edit', '', $current_uri)); */
	
		//Factory::getApplication()->redirect(str_ireplace('&layout=edit', '', Factory::getURI()->toString()));
	}

	/**
	 * sportsmanagementControllerPredictionUsers::select()
	 *
	 * @return void
	 */
	function select()
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$pID = Factory::getApplication()->input->getVar('prediction_id', '', 'post', 'int');
		$uID = Factory::getApplication()->input->getVar('uid', null, 'post', 'int');

		if (empty($uID))
		{
			$uID = null;
		}

		$link = JSMPredictionHelperRoute::getPredictionMemberRoute($pID, $uID);

		// Echo '<br />' . $link . '<br />';
		$this->setRedirect($link);
	}

	/**
	 * sportsmanagementControllerPredictionUsers::savememberdata()
	 *
	 * @return void
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

		$model        = $this->getModel('predictionusers');
		$user         =& Factory::getUser();
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
				if (!$model->savememberdata())
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

		$this->setRedirect($link, $msg);
	}

	/**
	 * sportsmanagementControllerPredictionUsers::selectprojectround()
	 *
	 * @return void
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

		$this->setRedirect($link);
	}

}
