<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage controllers
 * @file       projectpositions.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;

/**
 * sportsmanagementControllerprojectpositions
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllerprojectpositions extends JSMControllerAdmin
{

	public function __construct($config = array())
	{
		parent::__construct($config);

	}

	/**
	 * Method to store projectpositions
	 *
	 * @access public
	 * @return boolean    True on success
	 */
	function store()
	{
		$post = Factory::getApplication()->input->post->getArray(array());

		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$model = $this->getModel();
		 $msg = $model->store($post);
		$this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', $msg);
	}



	/**
	 * Proxy for getModel.
	 *
	 * @since 1.6
	 */
	public function getModel($name = 'Projectposition', $prefix = 'sportsmanagementModel', $config = Array() )
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}




}
