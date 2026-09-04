<?php
/**
 * Legacy SportsManagement site controller compatibility base.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Controller\AdminController;

/**
 * JSMControllerAdmin
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2017
 * @version   $Id$
 * @access    public
 */
class JSMControllerAdmin extends AdminController
{
	/**
	 * Legacy input alias retained for child controllers while avoiding a
	 * PHP 8.2 dynamic-property deprecation.
	 *
	 * @var object
	 */
	public $jinput;

	/**
	 * Constructor.
	 *
	 * @param array $config An optional associative array of configuration settings.
	 *
	 * @throws Exception
	 * @since 1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->app    = $this->getApplication();
		$this->jinput = $this->app->getInput();
		$this->option = $this->jinput->getCmd('option');
	}

	/**
	 * JSMControllerAdmin::cancel()
	 *
	 * @param mixed $key
	 *
	 * @return void
	 */
	public function cancel($key = null)
	{
		$this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
	}
}
