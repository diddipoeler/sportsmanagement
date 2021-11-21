<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage predictionrules
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementViewPredictionRules
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewPredictionRules extends sportsmanagementView
{

	/**
	 * sportsmanagementViewPredictionRules::init()
	 * 
	 * @return void
	 */
	function init()
	{

//		$model    = $this->getModel();
		$option   = Factory::getApplication()->input->getCmd('option');

		$app = Factory::getApplication();

		$this->predictionGame = sportsmanagementModelPrediction::getPredictionGame();
		$this->allowedAdmin   = sportsmanagementModelPrediction::getAllowed();
		$this->headertitle    = Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_SECTION_TITLE');

		if (isset($this->predictionGame))
		{
//			$config        = sportsmanagementModelPrediction::getPredictionTemplateConfig($this->getName());
//			$overallConfig = sportsmanagementModelPrediction::getPredictionOverallConfig();

//			$this->model              = $model;
//			$this->config             = array_merge($overallConfig, $config);
			$configavatar             = sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionusers');
			$this->configavatar       = $configavatar;
			$this->predictionMember   = sportsmanagementModelPrediction::getPredictionMember($configavatar);
			$this->predictionProjectS = sportsmanagementModelPrediction::getPredictionProjectS();
			$this->actJoomlaUser      = Factory::getUser();

			// Set page title
			$pageTitle = Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_TITLE'); // 'Tippspiel Regeln'

			$this->document->setTitle($pageTitle);

		}
		else
		{
			Log::add(Text::_('COM_SPORTSMANAGEMENT_PRED_PREDICTION_NOT_EXISTING'), Log::INFO, 'jsmerror');
		}

	}

}
