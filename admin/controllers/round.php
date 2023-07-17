<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage controllers
 * @file       round.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * sportsmanagementControllerround
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllerround extends JSMControllerForm
{

	/**
	 * Class Constructor
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @return void
	 * @since  1.5
	 */
	function __construct($config = array())
	{
		parent::__construct($config);
	}


/**
 * sportsmanagementControllerround::save2()
 * 
 * @param mixed $key
 * @param mixed $urlVar
 * @return void
 */
function save2($key = NULL, $urlVar = NULL)
{
Factory::getApplication()->enqueueMessage(Text::_(print_r($this->getTask(),true)), 'Notice');    
    
}


	/**
	 * sportsmanagementControllerround::startpopulate()
	 *
	 * @return void
	 */
	function startpopulate()
	{
		$msgType    = 'message';
		$msg        = '';
		$model      = $this->getModel('rounds');
		$post       = Factory::getApplication()->input->post->getArray(array());
		$project_id = $post['project_id'];
		$scheduling = $post['scheduling'];
		$time       = $post['time'];
		$interval   = $post['interval'];
		$start      = $post['start'];
		$roundname  = $post['roundname'];
		$teamsorder = $post['teamsorder'];

		if (!$teamsorder)
		{
			$msg     = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_NO_CLUB');
			$msgType = 'error';
		}
		else
		{
			$res = $model->populate($project_id, $scheduling, $time, $interval, $start, $roundname, $teamsorder);
		}

		$this->setRedirect('index.php?option=com_sportsmanagement&view=rounds', $msg, $msgType);
	}


}
