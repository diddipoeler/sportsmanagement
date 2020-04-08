<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage controllers
 * @file       joomleagueimports.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

/**
 * sportsmanagementControllerjoomleagueimports
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllerjoomleagueimports extends JSMControllerAdmin
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

		// Reference global application object
		$this->jsmapp = Factory::getApplication();
		$this->jsmjinput = $this->jsmapp->input;
	}


	/**
	 * sportsmanagementControllerjoomleagueimports::joomleaguesetagegroup()
	 *
	 * @return void
	 */
	function joomleaguesetagegroup()
	{
		$model = $this->getModel();
		$result = $model->joomleaguesetagegroup();
		$type = '';
		$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_JOOMLEAGUE_IMPORT_SETAGEGROUP');
		$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . '&jl_table_import_step=0&layout=infofield', false), $msg, $type);
	}


	/**
	 * sportsmanagementControllerjoomleagueimports::importjoomleaguenew()
	 *
	 * @return void
	 */
	function importjoomleaguenew()
	{
		$jl_table_import_step = $this->jsmjinput->get('jl_table_import_step', 0);
		$sports_type_id = $this->jsmjinput->get('filter_sports_type', 0);
		$result = array();

		if ($jl_table_import_step != 'ENDE')
		{
			$model = $this->getModel();
			$result = $model->importjoomleaguenew($jl_table_import_step, $sports_type_id);
			Factory::getDocument()->addScriptOptions('success', $result);
			$jl_table_import_step = $this->jsmjinput->get('jl_table_import_step', 0);
			$this->jsmapp->setUserState($this->option . ".jl_table_import_success", $result);
			$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . '&layout=default&jl_table_import_step=' . $jl_table_import_step . '&filter_sports_type=' . $sports_type_id, false));
		}
		else
		{
			$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . '&jl_table_import_step=0&layout=infofield', false));
		}

	}


	/**
	 * sportsmanagementControllerjoomleagueimports::importjoomleagueagegroup()
	 *
	 * @return void
	 */
	function importjoomleagueagegroup()
	{

		$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . '&layout=infofield', false));

	}


	/**
	 * Proxy for getModel.
	 *
	 * @since 1.6
	 */
	public function getModel($name = 'joomleagueimports', $prefix = 'sportsmanagementModel', $config = Array() )
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}
}

