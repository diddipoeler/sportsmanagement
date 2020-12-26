<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage controllers
 * @file       projectteams.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

/**
 * sportsmanagementControllerprojectteams
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllerprojectteams extends JSMControllerAdmin
{

	/**
	 * sportsmanagementControllermatches::__construct()
	 *
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * sportsmanagementControllerprojectteams::set_playground_match()
	 *
	 * @return void
	 */
	function set_playground_match()
	{
		$model = $this->getModel();
		$post  = Factory::getApplication()->input->post->getArray(array());
		$msg   = $model->set_playground_match($post);
		$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . '&pid=' . $post['pid'], false));
	}

	/**
	 * Proxy for getModel.
	 *
	 * @since 1.6
	 */
	public function getModel($name = 'Projectteam', $prefix = 'sportsmanagementModel', $config = Array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * sportsmanagementControllerprojectteams::set_playground()
	 *
	 * @return void
	 */
	function set_playground()
	{
		$model = $this->getModel();
		$post  = Factory::getApplication()->input->post->getArray(array());
		$msg   = $model->set_playground($post);
		$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . '&pid=' . $post['pid'], false));
	}

	/**
	 * Method to assign persons or teams
	 *
	 * @access public
	 * @return boolean    True on success
	 */
	function assign()
	{
		$post  = Factory::getApplication()->input->post->getArray(array());
		$model = $this->getModel();
		$msg   = $model->storeAssign($post);
		$this->setRedirect('index.php?option=' . $this->option . '&view=close&tmpl=component', $msg);
	}

	/**
	 * sportsmanagementControllerprojectteams::matchgroups()
	 *
	 * @return void
	 */
	function matchgroups()
	{
		$model = $this->getModel();
		$post  = Factory::getApplication()->input->post->getArray(array());
		$model->matchgroups();
		$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . '&pid=' . $post['pid'], false));
	}

	/**
	 * sportsmanagementControllerprojectteams::setseasonid()
	 *
	 * @return void
	 */
	function setseasonid()
	{
		$model = $this->getModel();
		$post  = Factory::getApplication()->input->post->getArray(array());
		$model->setseasonid();
		$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . '&pid=' . $post['pid'], false));
	}

	/**
	 * sportsmanagementControllerprojectteams::use_table_yes()
	 *
	 * @return void
	 */
	function use_table_yes()
	{
		$model = $this->getModel();
		$post  = Factory::getApplication()->input->post->getArray(array());
		$model->setusetable(1);
		$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . '&pid=' . $post['pid'], false));
	}

	/**
	 * sportsmanagementControllerprojectteams::use_table_no()
	 *
	 * @return void
	 */
	function use_table_no()
	{
		$model = $this->getModel();
		$post  = Factory::getApplication()->input->post->getArray(array());
		$model->setusetable(0);
		$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . '&pid=' . $post['pid'], false));
	}

	/**
	 * sportsmanagementControllerprojectteams::use_table_points_yes()
	 *
	 * @return void
	 */
	function use_table_points_yes()
	{
		$model = $this->getModel();
		$post  = Factory::getApplication()->input->post->getArray(array());
		$model->setusetablepoints(1);
		$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . '&pid=' . $post['pid'], false));
	}

	/**
	 * sportsmanagementControllerprojectteams::use_table_points_no()
	 *
	 * @return void
	 */
	function use_table_points_no()
	{
		$model = $this->getModel();
		$post  = Factory::getApplication()->input->post->getArray(array());
		$model->setusetablepoints(0);
		$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . '&pid=' . $post['pid'], false));
	}

	/**
	 * Method to update checked projectteams
	 *
	 * @access public
	 * @return boolean    True on success
	 */
	function saveshort()
	{
		$model = $this->getModel();
		$post  = Factory::getApplication()->input->post->getArray(array());
		$model->saveshort();
		$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . '&pid=' . $post['pid'], false));
	}

}
