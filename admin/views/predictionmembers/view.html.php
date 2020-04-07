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
 * @subpackage predictionmembers
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * sportsmanagementViewPredictionMembers
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewPredictionMembers extends sportsmanagementView
{

	/**
	 * sportsmanagementViewPredictionMembers::init()
	 *
	 * @return
	 */
	public function init()
	{

			 $tpl = '';

			 $this->prediction_id = $this->state->get('filter.prediction_id');

		switch ($this->getLayout())
		{
			case 'default':
			case 'default_3':
			case 'default_4':
				$this->app->setUserState("$this->option.prediction_id", $this->state->get('filter.prediction_id'));
				$this->_display($tpl);

return;
			break;
			case 'editlist':
			case 'editlist_3':
			case 'editlist_4':
				$this->_editlist($tpl);

return;
			break;
		}

	}

	/**
	 * sportsmanagementViewPredictionMembers::_editlist()
	 *
	 * @param   mixed $tpl
	 * @return void
	 */
	function _editlist( $tpl = null )
	{
		$this->prediction_id = $this->app->getUserState($this->option . '.prediction_id');

					  $prediction_name    = $this->getModel()->getPredictionProjectName($this->prediction_id);
		$this->prediction_name    = $prediction_name;

			  $res_prediction_members = $this->getModel()->getPredictionMembers($this->prediction_id);

		if ($res_prediction_members)
		{
			$lists['prediction_members'] = HTMLHelper::_(
				'select.genericlist',
				$res_prediction_members,
				'prediction_members[]',
				'class="inputbox" multiple="true" onchange="" size="15"',
				'value',
				'text'
			);
		}
		else
		{
			$lists['prediction_members'] = '<select name="prediction_members[]" id="prediction_members" style="" class="inputbox" multiple="true" size="15"></select>';
		}

		$res_joomla_members = $this->getModel()->getJLUsers($this->prediction_id);

		if ($res_joomla_members)
		{
			$lists['members'] = HTMLHelper::_(
				'select.genericlist',
				$res_joomla_members,
				'members[]',
				'class="inputbox" multiple="true" onchange="" size="15"',
				'value',
				'text'
			);
		}

																						  $this->lists    = $lists;
		$this->setlayout('editlist');

	}


	/**
	 * sportsmanagementViewPredictionMembers::_display()
	 *
	 * @param   mixed $tpl
	 * @return void
	 */
	function _display( $tpl = null )
	{
		$this->table = Table::getInstance('predictionmember', 'sportsmanagementTable');

		// Build the html select list for prediction games
		$mdlPredGames = BaseDatabaseModel::getInstance('PredictionGames', 'sportsmanagementModel');
		$predictions[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PRED_GAME'), 'value', 'text');

		if ($res = $mdlPredGames->getPredictionGames())
		{
			 $predictions = array_merge($predictions, $res);
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

			$this->lists    = $lists;

	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar()
	{

		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_TITLE');

			  ToolbarHelper::custom('predictionmembers.reminder', 'send.png', 'send_f2.png', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_SEND_REMINDER'), true);
		ToolbarHelper::divider();

		if ($this->prediction_id)
		{
			sportsmanagementHelper::ToolbarButton('editlist', 'new', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_BUTTON_ASSIGN'));
			ToolbarHelper::publishList('predictionmembers.publish', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_APPROVE'));
			ToolbarHelper::unpublishList('predictionmembers.unpublish', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_REJECT'));
			ToolbarHelper::deleteList('', 'predictionmembers.remove');
		}

			ToolbarHelper::checkin('predictionmembers.checkin');

			  parent::addToolbar();

	}

}
