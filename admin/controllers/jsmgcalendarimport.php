<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jsmgcalendar
 * @file       jsmgcalendarimport.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * sportsmanagementControllerjsmgcalendarImport
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2017
 * @version   $Id$
 * @access    public
 */
class sportsmanagementControllerjsmgcalendarImport extends BaseController
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
		/**
		 *
		 * Initialise variables.
		 */
		$app = Factory::getApplication();
		parent::__construct($config);

	}

	/**
	 * sportsmanagementControllerjsmgcalendarImport::import()
	 *
	 * @return void
	 */
	public function import()
	{

		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();
		$model  = $this->getModel('jsmgcalendarImport');
		$result = $model->import();

		if ($result)
		{
			$msg = Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_GCALENDARS_IMPORT_YES');
		}
		else
		{
			$msg = Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_GCALENDARS_IMPORT_NO');
		}

		$link = 'index.php?option=com_sportsmanagement&view=jsmgcalendars';

		$this->setRedirect($link, $msg);

	}

}
