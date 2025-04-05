<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage predictiongames
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;

/**
 * sportsmanagementViewPredictionGames
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewPredictionGames extends sportsmanagementView
{

	/**
	 * sportsmanagementViewPredictionGames::init()
	 *
	 * @return void
	 */
	public function init()
	{
		$lists = array();
        $this->predictionProjects = array();

		// setup of prediction id was done during populate of controller!
		$this->prediction_id = $this->state->get('filter.prediction_id');

		if ($this->prediction_id != 0)
		{
		}
		else
		{
			$this->prediction_id = $this->jinput->get('prediction_id', 0);
		}

		$table       = Table::getInstance('predictiongame', 'sportsmanagementTable');
		$this->table = $table;

		if (!$this->items)
		{
			$this->app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_NO_GAMES'), 'Error');
		}

		// get needed Models
		$mdlPredGame              = BaseDatabaseModel::getInstance('PredictionGame', 'sportsmanagementModel');
		$mdlRounds            = BaseDatabaseModel::getInstance('Rounds', 'sportsmanagementModel');
		$mdlPredRounds        = BaseDatabaseModel::getInstance('predictionrounds', 'sportsmanagementModel');
		
		// Build the html select list for prediction games
		$predictions[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PRED_GAME'), 'value', 'text');

		if ($res = $this->model->getPredictionGames())
		{
			$predictions          = array_merge($predictions, $res);
			$this->prediction_ids = $res;
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
		unset($res);

		$this->dPredictionID = $this->prediction_id;

		if ($this->prediction_id > 0)
		{
			$this->predictionProjects = $this->getModel()->getChilds($this->prediction_id);
		}
		
		$pred_project             = $mdlPredGame->getPredictionGame($this->prediction_id);

		// Attach fetched info to this 
		$this->lists         = $lists;
		$this->pred_project  = $pred_project;
		$this->modelround     = $mdlRounds;
		$this->modelpredround = $mdlPredRounds;
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar()
	{
		// Set toolbar items for the page
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_TITLE');
		$this->icon  = 'pred-cpanel';
		ToolbarHelper::publish('predictiongames.publish', 'JTOOLBAR_PUBLISH', true);
		ToolbarHelper::unpublish('predictiongames.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		ToolbarHelper::divider();
		ToolbarHelper::editList('predictiongame.edit');
		ToolbarHelper::addNew('predictiongame.add');
		ToolbarHelper::custom('predictiongame.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
		ToolbarHelper::archiveList('predictiongame.export', Text::_('JTOOLBAR_EXPORT'));
		parent::addToolbar();

	}


}
