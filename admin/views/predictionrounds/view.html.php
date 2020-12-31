<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    3.8.20
 * @package    Sportsmanagement
 * @subpackage predictionrounds
 * @file       view.html.php
 * @author     jst, diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2020 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementViewPredictionRounds
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2020
 * @access    public
 */
class sportsmanagementViewPredictionRounds extends sportsmanagementView
{

	/**
	 * sportsmanagementViewpredictionrounds::init()
	 *
	 * @return void
	 */
	public function init()
	{
		$this->populate = 0;
		$tpl            = null;

		$this->prediction_id = $this->state->get('filter.prediction_id');
		if ($this->prediction_id == 0)
		{
			$this->prediction_id = $this->jinput->request->get('prediction_id', 0);
			$this->state->set('filter.prediction_id', $this->prediction_id);
		}
		
		switch ($this->getLayout())
		{
			case 'default':
			case 'default_3':
			case 'default_4':
				$this->_displayDefault($tpl);
				return;
            case 'populate':
			case 'populate_3':
			case 'populate_4':
				$this->_displayPopulate($tpl);
				return;
		}
	}

	/**
	 * sportsmanagementViewRounds::_displayDefault()
	 *
	 * @param   mixed  $tpl
	 *
	 * @return void
	 */
	function _displayDefault($tpl)
	{
		$this->table = Table::getInstance('predictionround', 'sportsmanagementTable');
		
		if (!$this->items)
		{
			$this->app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_NO_PREDICTION_TIPPROUNDS'), 'Error');
		}

		// get needed Models
		$mdlPredGames             = BaseDatabaseModel::getInstance('PredictionGames', 'sportsmanagementModel');
		$mdlPredGame              = BaseDatabaseModel::getInstance('PredictionGame', 'sportsmanagementModel');
		
		// Build the html select list for prediction games
		$predictions[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PRED_GAME'), 'value', 'text');

		if ($res = $mdlPredGames->getPredictionGames())
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

		$pred_project             = $mdlPredGame->getPredictionGame($this->prediction_id);

		$myoptions                = array();
		$myoptions[]              = HTMLHelper::_('select.option', 'FIRSTMATCH_OF_TIPPGAME', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_RIEN_NE_VA_PLUS_FIRSTMATCH_OF_TIPPGAME'));
		$myoptions[]              = HTMLHelper::_('select.option', 'FIRSTMATCH_OF_TIPPROUND', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_RIEN_NE_VA_PLUS_FIRSTMATCH_OF_TIPPROUND'));
		$myoptions[]              = HTMLHelper::_('select.option', 'BEGIN_OF_MATCH', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_RIEN_NE_VA_PLUS_BEGIN_OF_MATCH'));
		$lists['rien_ne_va_plus'] = $myoptions;

		// Attach fetched info to this 
		$this->lists         = $lists;
		$this->pred_project  = $pred_project;
	}

	/**
	 * sportsmanagementViewRounds::_displayPopulate()
	 *
	 * @param   mixed  $tpl
	 *
	 * @return void
	 */
	function _displayPopulate($tpl)
	{

		$this->document->setTitle(Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_TITLE'));
		$lists = array();

		$options             = array(HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_TYPE_SINGLE_ROUND_ROBIN')),
			HTMLHelper::_('select.option', 1, Text::_('COM_SPORTSMANAGEMENTADMIN_ROUNDS_POPULATE_TYPE_DOUBLE_ROUND_ROBIN')),
			HTMLHelper::_('select.option', 2, Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_TYPE_TOURNAMENT_ROUND_ROBIN'))
		);
		$lists['scheduling'] = HTMLHelper::_('select.genericlist', $options, 'scheduling', '', 'value', 'text');

		$this->project_id = $this->app->getUserState("$this->option.pid", '0');
		$mdlProject       = BaseDatabaseModel::getInstance("Project", "sportsmanagementModel");
		$teams            = $mdlProject->getProjectTeamsOptions($this->project_id);
		$projectws        = $mdlProject->getProject($this->project_id);

		$options = array();

		foreach ($teams as $t)
		{
			$options[] = HTMLHelper::_('select.option', $t->value, $t->text);
		}

		$lists['teamsorder'] = HTMLHelper::_('select.genericlist', $options, 'teamsorder[]', 'multiple="multiple" size="20"');

		$this->projectws = $projectws;
		$this->lists     = $lists;
		$this->populate  = 1;
		$this->setLayout('populate');
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar()
	{
		// Set toolbar items for the page
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_TITLE');
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=predictiongames&prediction_id='.$this->prediction_id);

		if ($this->populate)
		{
			$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_TITLE');
			ToolbarHelper::apply('predictionround.startpopulate');
			ToolbarHelper::back();
			parent::addToolbar();
		}
		else
		{
			ToolbarHelper::editList('predictionround.edit');
			ToolbarHelper::publishList('rounds.publish');
			ToolbarHelper::unpublishList('rounds.unpublish');
			ToolbarHelper::divider();
		
			sportsmanagementHelper::ToolbarButton('predictionround.populate', 'new', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_IMPORT_BUTTON'));
		    ToolbarHelper::divider();
			ToolbarHelper::archiveList('predictionround.export', Text::_('JTOOLBAR_EXPORT'));
			ToolbarHelper::divider();
			ToolbarHelper::deleteList('', 'predictionrounds.delete', 'JTOOLBAR_DELETE');
			parent::addToolbar();
		}

	}
}
