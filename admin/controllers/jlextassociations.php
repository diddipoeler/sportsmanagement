<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage controllers
 * @file       jlextassociations.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementControllerjlextassociations
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementControllerjlextassociations extends JSMControllerAdmin
{

	/**
	 * sportsmanagementControllerjlextassociations::import()
	 *
	 * @return void
	 */
	function import()
	{
		   $databasetool = BaseDatabaseModel::getInstance("databasetool", "sportsmanagementModel");
		   $model = $this->getModel();
		   $msg = $databasetool->checkAssociations();
		   $this->setRedirect('index.php?option=com_sportsmanagement&view=jlextassociations', $msg);

	}

	/**
	 * Proxy for getModel.
	 *
	 * @since 1.6
	 */
	public function getModel($name = 'jlextassociation', $prefix = 'sportsmanagementModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

}
