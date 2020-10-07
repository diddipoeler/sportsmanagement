<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage controllers
 * @file       seasons.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * sportsmanagementControllerseasons
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllerseasons extends JSMControllerAdmin
{

	/**
	 * sportsmanagementControllerseasons::cancel()
	 * 
	 * @param mixed $key
	 * @return void
	 */
	function cancel($key = null)
	{
		$msg  = Text::_('JLIB_HTML_BEHAVIOR_CLOSE');
		$link = 'index.php?option=com_sportsmanagement&view=close&tmpl=component';
		$this->setRedirect($link, $msg);
	}

	/**
	 * sportsmanagementControllerseasons::applypersons()
	 *
	 * @return void
	 */
	function applypersons()
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();
		$post   = Factory::getApplication()->input->post->getArray(array());
		$model  = $this->getModel();
		$msg = $model->saveshortpersons();
		$this->setRedirect('index.php?option=com_sportsmanagement&tmpl=component&view=players&layout=assignpersons&season_id=' . $post['season_id'] . '&team_id=' . $post['team_id'] . '&persontype=' . $post['persontype'], $msg);
	}

	/**
	 * Proxy for getModel.
	 *
	 * @since 1.6
	 */
	public function getModel($name = 'Season', $prefix = 'sportsmanagementModel', $config = Array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	/**
	 * sportsmanagementControllerseasons::applyteams()
	 *
	 * @return void
	 */
	function applyteams()
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();
		$post   = Factory::getApplication()->input->post->getArray(array());
		$model  = $this->getModel();
		$model->saveshortteams();
		$msg = '';
		$this->setRedirect('index.php?option=com_sportsmanagement&tmpl=component&view=teams&layout=assignteams&season_id=' . $post['season_id'], $msg);
	}

}
