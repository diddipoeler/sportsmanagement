<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage controllers
 * @file       jlextindividualsportes.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * sportsmanagementControllerjlextindividualsportes
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllerjlextindividualsportes extends JSMControllerAdmin
{

	/**
	 * Method to update checked matches
	 *
	 * @access public
	 * @return boolean    True on success
	 */
	function saveshort()
	{
		$option             = Factory::getApplication()->input->getCmd('option');
		$app                = Factory::getApplication();
		$post               = Factory::getApplication()->input->post->getArray(array());
		$post['project_id'] = $app->getUserState("$option.pid", '0');
		$post['round_id']   = $app->getUserState("$option.rid", '0');

		$model = $this->getModel();
		$model->saveshort();

		//       $link = 'index.php?option=com_sportsmanagement&view=jlextindividualsportes&tmpl=component&rid='.$post['round_id'].'&id='.$post['match_id'].'&team1='.$post['projectteam1_id'].'&team2='.$post['projectteam2_id'].'';
		//		$this->setRedirect($link,$msg);

		$msg = '';
		$this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', $msg);
	}

	/**
	 * Proxy for getModel.
	 *
	 * @since 1.6
	 */
	public function getModel($name = 'jlextindividualsport', $prefix = 'sportsmanagementModel', $config = Array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * Method to update checked matches
	 *
	 * @access public
	 * @return boolean    True on success
	 */
	function applyshort()
	{
		$option             = Factory::getApplication()->input->getCmd('option');
		$app                = Factory::getApplication();
		$post               = Factory::getApplication()->input->post->getArray(array());
		$post['project_id'] = $app->getUserState("$option.pid", '0');
		$post['round_id']   = $app->getUserState("$option.rid", '0');

		$model = $this->getModel();
		$model->saveshort();

		$link = 'index.php?option=com_sportsmanagement&view=jlextindividualsportes&tmpl=component&rid=' . $post['round_id'] . '&id=' . $post['match_id'] . '&team1=' . $post['projectteam1_id'] . '&team2=' . $post['projectteam2_id'] . '';
		$this->setRedirect($link, $msg);

	}

	/**
	 * sportsmanagementControllerjlextindividualsportes::publish()
	 *
	 * @return void
	 */
	function publish()
	{
		$option             = Factory::getApplication()->input->getCmd('option');
		$app                = Factory::getApplication();
		$pks                = Factory::getApplication()->input->getVar('cid', array(), 'post', 'array');
		$post               = Factory::getApplication()->input->post->getArray(array());
		$post['project_id'] = $app->getUserState("$option.pid", '0');
		$post['round_id']   = $app->getUserState("$option.rid", '0');

		parent::publish();
		$msg = Text::sprintf('COM_SPORTSMANAGEMENT_N_ITEMS_PUBLISHED', count($pks));;
		$link = 'index.php?option=com_sportsmanagement&view=jlextindividualsportes&tmpl=component&rid=' . $post['round_id'] . '&id=' . $post['match_id'] . '&team1=' . $post['projectteam1_id'] . '&team2=' . $post['projectteam2_id'] . '';
		$this->setRedirect($link, $msg);

	}

	/**
	 * sportsmanagementControllerjlextindividualsportes::delete()
	 *
	 * @return void
	 */
	function delete()
	{
		$option             = Factory::getApplication()->input->getCmd('option');
		$app                = Factory::getApplication();
		$pks                = Factory::getApplication()->input->getVar('cid', array(), 'post', 'array');
		$post               = Factory::getApplication()->input->post->getArray(array());
		$post['project_id'] = $app->getUserState("$option.pid", '0');
		$post['round_id']   = $app->getUserState("$option.rid", '0');

		$model = $this->getModel();
		$model->delete($pks);

		$msg  = Text::sprintf('COM_SPORTSMANAGEMENT_N_ITEMS_DELETED', count($pks));
		$link = 'index.php?option=com_sportsmanagement&view=jlextindividualsportes&tmpl=component&rid=' . $post['round_id'] . '&id=' . $post['match_id'] . '&team1=' . $post['projectteam1_id'] . '&team2=' . $post['projectteam2_id'] . '';
		$this->setRedirect($link, $msg);

	}
}
