<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage smquotestxt
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

/**
 * sportsmanagementViewsmquotestxt
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewsmquotestxt extends sportsmanagementView
{
	/**
	 * sportsmanagementViewsmquotestxt::init()
	 *
	 * @return void
	 */
	public function init()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$model  = $this->getModel();
		$this->files = $model->getTXTFiles();
		$this->option = $option;
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar()
	{
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TXT_EDITORS');
		ToolbarHelper::back(Text::_('JPREV'), Route::_('index.php?option=com_sportsmanagement&view=smquotes'));
		parent::addToolbar();
	}


}
