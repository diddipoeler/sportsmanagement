<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       editclub.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage editclubs
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Session\Session;

/**
 * sportsmanagementControllerEditClub
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementControllerEditClub extends FormController
{

	/**
	 * Class Constructor
	 *
	 * @param   array $config An optional associative array of configuration settings.
	 * @return void
	 * @since  1.5
	 */
	function __construct($config = array())
	{
		parent::__construct($config);

		/**
*
 * Map the apply task to the save method.
*/
		$this->registerTask('apply', 'save');
	}

	/**
	 * sportsmanagementControllerEditClub::getModel()
	 *
	 * @param   string $name
	 * @param   string $prefix
	 * @param   mixed  $config
	 * @return
	 */
	public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, array('ignore_request' => false));
	}

		 /**
		  * sportsmanagementControllerEditClub::cancel()
		  *
		  * @param   mixed $key
		  * @return
		  */
	public function cancel($key = null)
	{
		$msg = 'cancel';
		$this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', $msg);

		return true;
	}


	/**
	 * sportsmanagementControllerEditClub::load()
	 *
	 * @return void
	 */
	function load()
	{
		$cid = Factory::getApplication()->input->getInt('cid', 0);

		$club = Table::getInstance('Club', 'sportsmanagementTable');
		$club->load($cid);
		$club->checkout($user->id);

		$this->display();
	}


	/**
	 * sportsmanagementControllerEditClub::display()
	 *
	 * @param   bool  $cachable
	 * @param   mixed $urlparams
	 * @return void
	 */
	function display($cachable = false, $urlparams = Array() )
	{

	}


	/**
	 * sportsmanagementControllerEditClub::save()
	 *
	 * @param   mixed $key
	 * @param   mixed $urlVar
	 * @return void
	 */
	function save($key = null, $urlVar = null)
	{
		$app = Factory::getApplication();

		// Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$msg = '';
		$address_parts = array();
		$post = Factory::getApplication()->input->post->getArray(array());

		// $cid = Factory::getApplication()->input->getVar('cid', array(0), 'post', 'array');
		// $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' post '.'<pre>'.print_r($post,true).'</pre>'  ), '');
		// $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' cid '.'<pre>'.print_r($cid,true).'</pre>'  ), '');
		// $this->setRedirect('index.php?option=com_sportsmanagement&tmpl=component&view=editclub&cid='.$post['id'].'&id='.$post['id'].'&p='.$post['p'], $msg, $type);
		// $post['id'] = (int) $cid[0];
		$model = $this->getModel('editclub');

		if (!empty($post['address']))
		{
			$address_parts[] = $post['address'];
		}

		if (!empty($post['state']))
		{
			$address_parts[] = $post['state'];
		}

		if (!empty($post['location']))
		{
			if (!empty($post['zipcode']))
			{
				$address_parts[] = $post['zipcode'] . ' ' . $post['location'];
			}
			else
			{
				$address_parts[] = $post['location'];
			}
		}

		if (!empty($post['country']))
		{
			$address_parts[] = JSMCountries::getShortCountryName($post['country']);
		}

		$address = implode(', ', $address_parts);
		$coords = sportsmanagementHelper::resolveLocation($address);

		foreach ($coords as $key => $value)
		{
			$post['extended'][$key] = $value;
		}

		$post['latitude'] = $coords['latitude'];
		$post['longitude'] = $coords['longitude'];

		if (isset($post['merge_teams']))
		{
			if (count($post['merge_teams']) > 0)
			{
				$temp = implode(",", $post['merge_teams']);
			}
			else
			{
				$temp = '';
			}

			$post['merge_teams'] = $temp;
		}
		else
		{
			$post['merge_teams'] = '';
		}

			  $updateresult = $model->updItem($post);

		//        if ($model->updItem($post)) {
		//            $msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_CTRL_SAVED');
		//            $createTeam = Factory::getApplication()->input->getVar('createTeam');
		//            if ($createTeam) {
		//                $team_name = Factory::getApplication()->input->getVar('name');
		//                $team_short_name = strtoupper(substr(preg_replace('/[^a-zA-Z]/','', $team_name), 0, 3));
		//                $teammodel = $this->getModel('team');
		//                $tpost['id'] = "0";
		//                $tpost['name'] = $team_name;
		//                $tpost['short_name'] = $team_short_name;
		//                $tpost['club_id'] = $teammodel->getDbo()->insertid();
		//                $teammodel->save($tpost);
		//            }
		//            $type = 'message';
		//        } else {
		//            $msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_CTRL_ERROR_SAVE') . $model->getError();
		//            $type = 'error';
		//        }

		if ($this->getTask() == 'save')
		{
			$this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
		}
		else
		{
			$this->setRedirect('index.php?option=com_sportsmanagement&tmpl=component&view=editclub&cid=' . $post['id'] . '&id=' . $post['id'] . '&p=' . $post['p'], $msg, $type);
		}
	}

}

