<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage controllers
 * @file       treetomatch.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;

/**
 * sportsmanagementControllerTreetomatch
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2018
 * @version   $Id$
 * @access    public
 */
class sportsmanagementControllerTreetomatch extends FormController
{

	/**
	 * sportsmanagementControllerTreetomatch::__construct()
	 *
	 * @param   mixed  $config
	 *
	 * @return void
	 */
	public function __construct($config = array())
	{
		//	$app = Factory::getApplication();
		//		$jinput = $app->input;
		//		$jinput->set('layout','form');

		parent::__construct($config);

		// Reference global application object
		$this->jsmapp = Factory::getApplication();

		// JInput object
		$this->jsmjinput   = $this->jsmapp->input;
		$this->jsmoption   = $this->jsmjinput->getCmd('option');
		$this->jsmdocument = Factory::getDocument();
	}


	/**
	 * sportsmanagementControllerTreetomatch::save_matcheslist()
	 *
	 * @return void
	 */
	function save_matcheslist()
	{
		$msg        = '';
		$post       = $this->jsmjinput->post->getArray();
		$cid        = $this->jsmjinput->get('cid', array(), 'array');
		$post['id'] = $this->jsmjinput->get('nid');

		$model = $this->getModel('treetomatchs');

		if ($model->store($post))
		{
			$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOMATCH_CTRL_SAVED');
		}
		else
		{
			$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOMATCH_CTRL_ERROR_SAVE') . $model->getError();
		}

		$link = 'index.php?option=com_sportsmanagement&view=treetomatchs&layout=editlist&nid=' . $this->jsmjinput->get('nid') . '&tid=' . $this->jsmjinput->get('tid') . '&pid=' . $this->jsmjinput->get('pid');
		$this->setRedirect($link, $msg);

	}


}
