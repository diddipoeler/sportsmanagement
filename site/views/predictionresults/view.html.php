<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage predictionresults
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

jimport('joomla.filesystem.file');

/**
 * sportsmanagementViewPredictionResults
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewPredictionResults extends sportsmanagementView
{

	/**
	 * sportsmanagementViewPredictionResults::init()
	 *
	 * @return void
	 */
	function init()
	{
		$this->jsmstartzeit = $this->getStartzeit();
		$this->predictionGame = sportsmanagementModelPrediction::getPredictionGame();
		$this->allowedAdmin   = sportsmanagementModelPrediction::getAllowed();

		$this->limit        = $this->model->getLimit();
		$this->limitstart   = $this->model->getLimitStart();
		$this->ausgabestart = $this->limitstart + 1;
		$this->ausgabeende  = $this->limitstart + $this->limit;

		if (isset($this->predictionGame))
		{
//			$config        = sportsmanagementModelPrediction::getPredictionTemplateConfig($this->getName());
			$configavatar  = sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionusers');
			$configentry   = sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionentry');
			$this->config        = array_merge($configentry, $this->config);
//			$overallConfig = sportsmanagementModelPrediction::getPredictionOverallConfig();

			$this->roundID             = sportsmanagementModelPredictionResults::$roundID;
//			$this->config              = array_merge($overallConfig, $config);
			$this->model->config       = $this->config;
			$this->configavatar        = $configavatar;
			$this->model->configavatar = $this->configavatar;

			$this->predictionMember   = sportsmanagementModelPrediction::getPredictionMember($configavatar);
			$this->predictionProjectS = sportsmanagementModelPrediction::getPredictionProjectS();
			$this->actJoomlaUser      = Factory::getUser();

			$predictionRounds[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_PRED_SELECT_ROUNDS'), 'value', 'text');

			if ($res = sportsmanagementModelPrediction::getRoundNames($this->predictionGame->id))
			{
				$predictionRounds = array_merge($predictionRounds, $res);
			}

			$lists['predictionRounds'] = HTMLHelper::_('select.genericList', $predictionRounds, 'r', 'class="inputbox" onchange="this.form.submit(); "', 'value', 'text', sportsmanagementModelPrediction::$roundID);
			unset($res);
			unset($predictionRounds);

			$this->lists           = $lists;
			$this->show_debug_info = ComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info', 0);

			// Set page title
			$pageTitle = Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_TITLE');

			$this->memberList = $this->get('Data');
			$this->pagination = $this->get('Pagination');

			$this->headertitle = $pageTitle;

			if (!isset($this->config['table_class']))
			{
				$this->config['table_class'] = 'table';
			}

			$this->document->setTitle($pageTitle);
		}
		else
		{
			$this->app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_PRED_PREDICTION_NOT_EXISTING'), 'error');
		}
	}

}
