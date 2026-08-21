<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage ical
 * @file       view.raw.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\EventModel;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\Database\DatabaseInterface;

class sportsmanagementViewIcal extends HtmlView
{
	public function display($tpl = null)
	{
		if (!class_exists(EventModel::class))
		{
			require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
			require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/EventModel.php';
		}

		$model = new EventModel();
		$model->setDatabase(Factory::getContainer()->get(DatabaseInterface::class));
		$this->setModel($model, true);

		$this->event = $model->getGCalendar();

		parent::display($tpl);
	}
}
