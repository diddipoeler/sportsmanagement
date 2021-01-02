<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage controllers
 * @file       divisions.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;

/**
 * sportsmanagementControllerdivisions
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllerdivisions extends JSMControllerAdmin
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
     * sportsmanagementControllerdivisions::cancel()
     * 
     * @return void
     */
    function cancel()
	{
		$msg = '';
		$this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', $msg);
	}
    
    /**
     * sportsmanagementControllerdivisions::massadd()
     * 
     * @return void
     */
    function massadd()
	{
		Session::checkToken() or jexit(\Text::_('JINVALID_TOKEN'));
		$model = $this->getModel();
		$msg   = $model->massadd();
		$this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', $msg);
	}

	/**
	 * sportsmanagementControllerdivisions::divisiontoproject()
	 * 
	 * @return void
	 */
	function divisiontoproject()
	{
		$model = $this->getModel();
		$msg   = $model->divisiontoproject();
		$this->setRedirect('index.php?option=com_sportsmanagement&view=divisions&pid=' . $this->project_id, $msg);
	}

	/**
	 * Proxy for getModel.
	 *
	 * @since 1.6
	 */
	public function getModel($name = 'Division', $prefix = 'sportsmanagementModel', $config = Array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * sportsmanagementControllerdivisions::saveOrder()
	 *
	 * @return void
	 */
	public function saveOrder()
	{
		$this->project_id = $this->app->getUserState("$this->option.pid", '0');
		$model = $this->getModel();
		$pks   = $this->jinput->get('cid', array(), 'array');
		$order = $this->jinput->get('order', array(), 'array');
		$msg = $model->saveorder($pks, $order);
		$this->setRedirect('index.php?option=com_sportsmanagement&view=divisions&pid=' . $this->project_id, $msg);
	}

	/**
	 * sportsmanagementControllerdivisions::saveshort()
	 *
	 * @return void
	 */
	function saveshort()
	{
		$this->project_id = $this->app->getUserState("$this->option.pid", '0');
		$model            = $this->getModel();
		$msg              = $model->saveshort();
		$this->setRedirect('index.php?option=com_sportsmanagement&view=divisions&pid=' . $this->project_id, $msg);
	}
}
