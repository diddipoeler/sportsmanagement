<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    3.8.20
 * @package    Sportsmanagement
 * @subpackage controllers
 * @file       predictionrounds.php
 * @author     jst, diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2020Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;

/**
 * sportsmanagementControllerPredictionRounds
 *
 * @package
 * @author
 * @copyright jst
 * @version   2020
 * @access    public
 */
class sportsmanagementControllerPredictionRounds extends JSMControllerAdmin
{

	/**
	 * Constructor.
	 *
	 * @param   array An optional associative array of configuration settings.
	 *
	 * @see   JController
	 * @since 1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->app    = Factory::getApplication();
		$this->jinput = $this->app->input;
		$this->option = $this->jinput->getCmd('option');
	}

	/**
	 * Proxy for getModel.
	 *
	 * @since 1.6
	 */
	public function getModel($name = 'predictionround', $prefix = 'sportsmanagementModel', $config = Array())
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
	 * Method to update checked predictionrounds
	 *
	 * @access public
	 * @return boolean    True on success
	 */
	function saveshort()
	{
		$msgType    = 'message';
		$msg        = '';
		
		//  Get the input
		$pks = $this->jinput->get('cid', array(), 'array');
		if (!$pks)
		{
			$msg  	=  Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_SAVE_NO_SELECT');
			$msgType = 'error';
		}
		else 
		{
			$model = $this->getModel();
			// provide input and post data to model to update according items
            $msg   = $model->saveshort($pks, $this->jinput->post->getArray());
        }
		$this->setRedirect('index.php?option=com_sportsmanagement&view=predictionrounds', $msg, $msgType);
	}

	/**
	 * populateFromProjectRounds()
	 * adds (inactive) Turnament TippRounds to selected tippgame
	 * 
	 * @return void
	 */
	public function populateFromProjectRounds()
	{
		$msgType       = 'message';
		$msg           = '';
		$mdlPredRound  = $this->getModel();
		$mdlPredRounds = $this->getModel('predictionrounds');
		$mdlProjRounds = $this->getModel('rounds');
		$mdlPredGame   = $this->getModel('predictiongame');
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();

		$prediction_id   = $app->getUserState("$option.prediction_id", '0');
		if ($prediction_id == 0)
		{
			$msg  	= Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_NO_PREDICTION_ID',
						    Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PRED_GAME'));
			$msgType = 'error';
		}
		$project_ids = $mdlPredGame->getPredictionProjectIDs($prediction_id);
		if (count($project_ids) < 1)
		{
			$msg  	 = "INTERNAL ERROR: Unable to find out Project ID!!!";
			$msgType = 'error';
		}
		else
		{
			// get existing Predrounds in tippgame
			$existingPredRoundsIds = $mdlPredRounds->getPredGamesPredictionRoundsIds($prediction_id);
			// get existing Predrounds in depending JSM Project
			$existingProjRoundsIds = $mdlProjRounds->getRoundsIds($project_ids[0]);

			$projRoundsIdsToAdd = array_diff($existingProjRoundsIds, $existingPredRoundsIds);

			if (count($projRoundsIdsToAdd) > 0) 
			{
				$msg   = $mdlPredRound->addPredRoundIds($projRoundsIdsToAdd, $prediction_id, $project_ids[0]);
			}		
			else
			{
				$msg   = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_ALL_AVAILABLE');
				$msgType = 'warning';
			}
		}
		$this->setRedirect('index.php?option=com_sportsmanagement&view=predictionrounds', $msg, $msgType);
	}
}
