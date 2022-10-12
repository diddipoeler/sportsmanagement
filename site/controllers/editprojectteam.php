<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editprojectteam
 * @file       editprojectteam.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Language\Text;


/**
 * sportsmanagementControllereditprojectteam
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2019
 * @version   $Id$
 * @access    public
 */
class sportsmanagementControllereditprojectteam extends FormController
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

		/**
		 *
		 * Map the apply task to the save method.
		 */
		$this->registerTask('apply', 'save');
	}

	/**
	 * sportsmanagementControllereditprojectteam::submit()
	 *
	 * @return
	 */
	public function submit()
	{

		return true;
	}

	/**
	 * sportsmanagementControllereditprojectteam::save()
	 *
	 * @param   mixed  $key
	 * @param   mixed  $urlVar
	 *
	 * @return
	 */
	public function save($key = null, $urlVar = null)
	{
		/**
		 *
		 * Initialise variables.
		 */
		$app   = Factory::getApplication();
		$model = $this->getModel('editprojectteam');

		$data = Factory::getApplication()->input->post->getArray(array());
		$id   = Factory::getApplication()->input->getInt('id');

		/**
		 *
		 * Now update the loaded data to the database via a function in the model
		 */
		$upditem = $model->updItem($data);

		/**
		 *
		 * Set the redirect based on the task.
		 */
		switch ($this->getTask())
		{
			case 'apply':
				$message = Text::_('COM_SPORTSMANAGEMENT_EDIT_PROJECTTEAM_SAVED');
				$this->setRedirect('index.php?option=com_sportsmanagement&view=editprojectteam&tmpl=component&id=' . $id . '&ptid=' . $data['ptid'] . '&p=' . $data['p'] . '&tid=' . $data['tid'], $message);
				break;

			case 'save':
			default:
				$this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
				break;
		}

		return true;
	}

	/**
	 * sportsmanagementControllereditprojectteam::getModel()
	 *
	 * @param   string  $name
	 * @param   string  $prefix
	 * @param   mixed   $config
	 *
	 * @return
	 */
	public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, array('ignore_request' => false));
	}

	/**
	 * sportsmanagementControllereditprojectteam::cancel()
	 *
	 * @param   mixed  $key
	 *
	 * @return
	 */
	public function cancel($key = null)
	{
		$msg = 'cancel';
		$this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', $msg);

		return true;
	}
}

