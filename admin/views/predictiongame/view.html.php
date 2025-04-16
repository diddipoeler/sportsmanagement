<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage predictiongame
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementViewPredictionGame
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewPredictionGame extends sportsmanagementView
{

	/**
	 * sportsmanagementViewPredictionGame::init()
	 *
	 * @return
	 */
	public function init()
	{

		$this->pred_admins   = sportsmanagementModelPredictionGames::getAdmins($this->item->id);
		$this->pred_projects = $this->model->getPredictionProjectIDs($this->item->id);

//        Factory::getApplication()->enqueueMessage('<pre>'.print_r($this->pred_projects,true).'</pre>', 'error');
//        Factory::getApplication()->enqueueMessage('<pre>'.print_r(sportsmanagementModelPredictionGame::$seasonid,true).'</pre>', 'error');

		$this->form->setValue('user_ids', null, $this->pred_admins);
		$this->form->setValue('project_ids', null, $this->pred_projects);
        $this->form->setValue('s', null, sportsmanagementModelPredictionGame::$seasonid);

	}


	/**
	 * sportsmanagementViewPredictionGame::addToolBar()
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		$jinput = Factory::getApplication()->input;
		$jinput->set('hidemainmenu', true);
		$isNew      = $this->item->id ? $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAME_EDIT') : $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAME_NEW');
		$this->icon = 'pgame';
		parent::addToolbar();
	}

}
