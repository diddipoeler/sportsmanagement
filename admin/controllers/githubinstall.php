<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage controllers
 * @file       githubinstall.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;



/**
 * SportsManagement Controller
 */

/**
 * sportsmanagementControllergithubinstall
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllergithubinstall extends FormController
{

	function CopyGithubLink()
	{
$model = $this->getModel();
$github_link         = ComponentHelper::getParams('com_sportsmanagement')->get('cfg_update_server_file', '');
$msg   = $model->CopyGithubLink();
$url = Route::_('index.php?option=com_sportsmanagement&view=update&task=update.save&file_name=jsm_update_github.php"');
$this->setRedirect->redirect($url, $msg);
		
	}
	
	/**
	 * Proxy for getModel.
	 *
	 * @since 1.6
	 */
	public function getModel($name = 'githubinstall', $prefix = 'sportsmanagementModel', $config = Array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	
	/**
	 * Method to store
	 *
	 * @access public
	 * @return boolean    True on success
	 */
	function store()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(\Text::_('JINVALID_TOKEN'));
		$msg = '';

		$this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', $msg);
	}

}
