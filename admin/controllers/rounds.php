<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage controllers
 * @file       rounds.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;

/**
 * sportsmanagementControllerrounds
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllerrounds extends JSMControllerAdmin
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
	 * sportsmanagementControllerrounds::cancel()
	 * 
	 * @return void
	 */
	function cancel()
	{
		$msg = '';
		$this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', $msg);
	}

	/**
	 * Method to add mass rounds
	 *
	 * @access public
	 * @return boolean    True on success
	 */
	function massadd()
	{
		Session::checkToken() or jexit(\Text::_('JINVALID_TOKEN'));
		$model = $this->getModel();
		$msg   = $model->massadd();
		$this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', $msg);
	}

	/**
	 * Proxy for getModel.
	 *
	 * @since 1.6
	 */
	public function getModel($name = 'Round', $prefix = 'sportsmanagementModel', $config = Array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	/**
	 * Method to delete matches in round
	 *
	 * @access public
	 * @return boolean    True on success
	 */

	function deleteRoundMatches()
	{
		$model = $this->getModel();
		$pks   = Factory::getApplication()->input->getVar('cid', null, 'post', 'array');
		$msg   = $model->deleteRoundMatches($pks);
		$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
	}

	/**
	 * Method to update checked rounds
	 *
	 * @access public
	 * @return boolean    True on success
	 */
	function saveshort()
	{
		$this->project_id = $this->app->getUserState("$this->option.pid", '0');
		$model            = $this->getModel();
		$msg              = $model->saveshort();
		$this->setRedirect('index.php?option=com_sportsmanagement&view=rounds&pid=' . $this->project_id, $msg);
	}

	/**
	 * display the populate form
	 */
	public function populate()
	{
		$app         = Factory::getApplication();
		$jinput      = $app->input;
		$division_id = $jinput->getInt('division_id', 0);
		$this->setRedirect('index.php?option=' . $this->option . '&view=rounds&layout=populate&division_id=' . $division_id);
	}


}
