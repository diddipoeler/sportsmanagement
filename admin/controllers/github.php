<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage controllers
 * @file       github.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\Controller\FormController;


/**
 * sportsmanagementControllergithub
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2016
 * @version   $Id$
 * @access    public
 */
class sportsmanagementControllergithub extends FormController
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
		$this->model  = $this->getModel();
		$this->post   = $this->jinput->post->getArray(array());

		// $this->registerTask('saveshort',  'saveshort');
	}

	/**
	 * Proxy for getModel.
	 *
	 * @since 1.6
	 */
	public function getModel($name = 'github', $prefix = 'sportsmanagementModel', $config = Array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * sportsmanagementControllergithub::cancel()
	 *
	 * @param   mixed  $key
	 *
	 * @return void
	 */
	function cancel($key = null)
	{
		$msg = Text::_('JLIB_HTML_BEHAVIOR_CLOSE');

		$link = 'index.php?option=' . $this->option . '&view=close&tmpl=component';

		// Echo $link.'<br />';
		$this->setRedirect($link, $msg);
	}

	/**
	 * sportsmanagementControllergithub::addissue()
	 *
	 * @return void
	 */
	function addissue()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(\Text::_('JINVALID_TOKEN'));

		$msg = $this->model->addissue();
		$this->setRedirect('index.php?option=com_sportsmanagement&view=github&tmpl=component&layout=github_result', $msg);

	}

}

