<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editclubs
 * @file       editclub.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Session\Session;

/**
 * sportsmanagementControllerEditClub
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementControllerEditClub extends FormController
{

	/**
	 * Class Constructor
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @return void
	 * @since  1.5
	 */
	function __construct($config = array())
	{
		parent::__construct($config);

		/** Map the apply task to the save method. */
		$this->registerTask('apply', 'save');
	}

	/**
	 * sportsmanagementControllerEditClub::cancel()
	 *
	 * @param   mixed  $key
	 *
	 * @return
	 */
	public function cancel($key = null)
	{
		$msg = 'cancel';
		$this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', $msg);
		return true;
	}

	/**
	 * sportsmanagementControllerEditClub::load()
	 *
	 * @return void
	 */
	function load()
	{
		$cid = Factory::getApplication()->input->getInt('cid', 0);
		$club = Table::getInstance('Club', 'sportsmanagementTable');
		$club->load($cid);
		$club->checkout($user->id);
		$this->display();
	}

	/**
	 * sportsmanagementControllerEditClub::display()
	 *
	 * @param   bool   $cachable
	 * @param   mixed  $urlparams
	 *
	 * @return void
	 */
	function display($cachable = false, $urlparams = Array())
	{

	}

	/**
	 * sportsmanagementControllerEditClub::save()
	 *
	 * @param   mixed  $key
	 * @param   mixed  $urlVar
	 *
	 * @return void
	 */
	function save($key = null, $urlVar = null)
	{
		$app = Factory::getApplication();

		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$msg           = '';
		$address_parts = array();
		$post          = Factory::getApplication()->input->post->getArray(array());

		$model = $this->getModel('editclub');

		if (isset($post['merge_teams']))
		{
			if (count($post['merge_teams']) > 0)
			{
				$temp = implode(",", $post['merge_teams']);
			}
			else
			{
				$temp = '';
			}

			$post['merge_teams'] = $temp;
		}
		else
		{
			$post['merge_teams'] = '';
		}

		/** Now update the loaded data to the database via a function in the model */
        $updateresult = $model->updItem($post);

		/** Set the redirect based on the task. */
        if ($this->getTask() == 'save')
		{
			$this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
		}
		else
		{
			$this->setRedirect('index.php?option=com_sportsmanagement&tmpl=component&view=editclub&cid=' . $post['id'] . '&id=' . $post['id'] . '&p=' . $post['p'], $msg, $type);
		}
	}

	/**
	 * sportsmanagementControllerEditClub::getModel()
	 *
	 * @param   string  $name
	 * @param   string  $prefix
	 * @param   mixed   $config
	 *
	 * @return
	 */
	public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, array('ignore_request' => false));
	}

}

