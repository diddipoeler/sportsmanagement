<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       agegroups.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage controllers
 */

defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementControlleragegroups
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControlleragegroups extends JSMControllerAdmin
{

	/**
	 * sportsmanagementControlleragegroups::saveshort()
	 *
	 * @return void
	 */
	function saveshort()
	{
		$model = $this->getModel();
		   $msg = $model->saveshort();
		   $this->setRedirect('index.php?option=com_sportsmanagement&view=agegroups', $msg);
	}


	/**
	 * sportsmanagementControlleragegroups::import()
	 *
	 * @return void
	 */
	function import()
	{
		   $model = $this->getModel();
		   $msg = $model->importAgeGroupFile();
		   $this->setRedirect('index.php?option=com_sportsmanagement&view=agegroups', $msg);

	}

	/**
	 * Proxy for getModel.
	 *
	 * @since 1.6
	 */
	public function getModel($name = 'agegroup', $prefix = 'sportsmanagementModel', $config = Array() )
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}
}
