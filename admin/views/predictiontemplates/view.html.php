<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage predictiontemplates
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * sportsmanagementViewPredictionTemplates
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewPredictionTemplates extends sportsmanagementView
{

	/**
	 * sportsmanagementViewPredictionTemplates::init()
	 *
	 * @return void
	 */
	public function init()
	{

			  $this->prediction_id = $this->state->get('filter.prediction_id');

		if (isset($this->prediction_id))
		{
		}
		else
		{
			$this->prediction_id = $this->jinput->post->get('filter_prediction_id', 0);
		}

			  $lists = array();

		$mdlPredictionGame = BaseDatabaseModel::getInstance('PredictionGame', 'sportsmanagementModel');
		$mdlPredictionGames = BaseDatabaseModel::getInstance('PredictionGames', 'sportsmanagementModel');

		if (isset($this->prediction_id))
		{
			$checkTemplates = $this->model->checklist($this->prediction_id);
			$predictiongame    = $mdlPredictionGame->getPredictionGame($this->prediction_id);
		}
		else
		{
			$this->prediction_id = $this->jinput->post->get('filter_prediction_id', 0);
		}

			$this->table = Table::getInstance('predictiontemplate', 'sportsmanagementTable');

			  /**
*
 * build the html select list for prediction games
*/
		$predictions = array();
		$predictions[] = HTMLHelper::_('select.option', '0', '- ' . Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PRED_GAME') . ' -', 'value', 'text');

		if ($res = $mdlPredictionGames->getPredictionGames())
		{
			$predictions = array_merge($predictions, $res);
			$this->prediction_ids    = $res;
		}

				$lists['predictions'] = HTMLHelper::_(
					'select.genericlist',
					$predictions,
					'filter_prediction_id',
					'class="inputbox" onChange="this.form.submit();" ',
					'value',
					'text',
					$this->state->get('filter.prediction_id')
				);

		$this->pred_id    = $this->prediction_id;
		$this->lists    = $lists;
		$this->predictiongame    = $predictiongame;

			 unset($res);
		unset($predictions);
		unset($lists);

	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PTMPLS');
		$this->icon = 'templates';
		parent::addToolbar();
	}

}
