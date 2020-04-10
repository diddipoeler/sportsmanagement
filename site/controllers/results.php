<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage results
 * @file       results.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementControllerResults
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementControllerResults extends BaseController
{


	/**
	 * sportsmanagementControllerEditMatch::__construct()
	 *
	 * @param   mixed  $config
	 *
	 * @return void
	 */
	function __construct($config = array())
	{
		parent::__construct($config);

		// Initialise variables.
		$this->app = Factory::getApplication();

		// JInput object
		$this->jinput    = $this->app->input;
		$this->jsmoption = $this->jinput->getCmd('option');
		$this->model     = $this->getModel('results');

		// Get the input
		$this->pks  = $this->jinput->getVar('cid', null, 'post', 'array');
		$this->post = $this->jinput->post->getArray();

		if (ComponentHelper::getParams($this->jsmoption)->get('show_debug_info_frontend'))
		{
		}

	}


	/**
	 * sportsmanagementControllerResults::saveReferees()
	 *
	 * @return void
	 */
	public function saveReferees()
	{
		$this->setRedirect('index.php?option=com_sportsmanagement&view=results&cfg_which_database=' . $this->post['cfg_which_database'] . '&s=' . $this->post['s'] . '&p=' . $this->post['p'] . '&r=' . $this->post['r'] . '&division=' . $this->post['division'] . '&mode=' . $this->post['mode'] . '&order=' . $this->post['order'] . '&layout=' . $this->post['layout']);

	}


	/**
	 * sportsmanagementControllerResults::display()
	 *
	 * @param   bool  $cachable
	 * @param   bool  $urlparams
	 *
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false)
	{

	}

	/**
	 * sportsmanagementControllerResults::saveshort()
	 *
	 * @return void
	 */
	public function saveshort()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$model  = $this->getModel('results');

		// Get the input
		$pks    = $jinput->getVar('cid', null, 'post', 'array');
		$post   = $jinput->post->getArray();
		$layout = $jinput->getCmd('layout', 'form');

		if (ComponentHelper::getParams($this->jsmoption)->get('show_debug_info_frontend'))
		{
		}

		$model->saveshort();

		$this->setRedirect('index.php?option=com_sportsmanagement&view=results&cfg_which_database=' . $post['cfg_which_database'] . '&s=' . $post['s'] . '&p=' . $post['p'] . '&r=' . $post['r'] . '&division=' . $post['division'] . '&mode=' . $post['mode'] . '&order=' . $post['order'] . '&layout=' . $layout);

	}


}
