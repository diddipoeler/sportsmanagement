<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage controllers
 * @file       jlextdfbkeyimport.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementControllerjlextdfbkeyimport
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementControllerjlextdfbkeyimport extends BaseController
{

	/**
	 * sportsmanagementControllerjlextdfbkeyimport::__construct()
	 *
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();
		$this->registerTask('save', 'Save');
		$this->registerTask('apply', 'Apply');
		$this->registerTask('insert', 'insert');
	}


	/**
	 * sportsmanagementControllerjlextdfbkeyimport::display()
	 *
	 * @param   bool  $cachable
	 * @param   bool  $urlparams
	 *
	 * @return void
	 */
	function display($cachable = false, $urlparams = false)
	{

		parent::display();

	}

	/**
	 * sportsmanagementControllerjlextdfbkeyimport::getdivisionfirst()
	 *
	 * @return void
	 */
	function getdivisionfirst()
	{
		$post   = Factory::getApplication()->input->post->getArray(array());
		$option = Factory::getApplication()->input->getCmd('option');
		$msg    = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_INFO_20');
		$link   = 'index.php?option=' . $option . '&view=jlextdfbkeyimport&layout=default&divisionid=' . $post['divisionid'];
		$this->setRedirect($link, $msg);
	}

	/**
	 * sportsmanagementControllerjlextdfbkeyimport::apply()
	 *
	 * @return void
	 */
	function apply()
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$post   = Factory::getApplication()->input->post->getArray(array());

		// Store the variable that we would like to keep for next time
		// function syntax is setUserState( $key, $value );
		Factory::getApplication()->setUserState("$option.first_post", $post);

		$msg  = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_INFO_12');
		$link = 'index.php?option=' . $option . '&view=jlextdfbkeyimport&layout=default_savematchdays';
		$this->setRedirect($link, $msg);

	}


	/**
	 * sportsmanagementControllerjlextdfbkeyimport::save()
	 *
	 * @return void
	 */
	function save()
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();
		$post   = Factory::getApplication()->input->post->getArray(array());

		for ($i = 0; $i < count($post[roundcode]); $i++)
		{
			$mdl = BaseDatabaseModel::getInstance("round", "sportsmanagementModel");
			$row = $mdl->getTable();

			$row->roundcode  = $post[roundcode][$i];
			$row->project_id = $post[projectid];
			$row->name       = $post[name][$i];

			// Convert dates back to mysql date format
			if (isset($post[round_date_first][$i]))
			{
				$post[round_date_first][$i] = strtotime($post[round_date_first][$i]) ? strftime('%Y-%m-%d', strtotime($post[round_date_first][$i])) : null;
			}
			else
			{
				$post[round_date_first][$i] = '0000-00-00';
			}

			if (isset($post[round_date_last][$i]))
			{
				$post[round_date_last][$i] = strtotime($post[round_date_last][$i]) ? strftime('%Y-%m-%d', strtotime($post[round_date_last][$i])) : null;
			}
			else
			{
				$post[round_date_last][$i] = '0000-00-00';
			}

			$row->round_date_first = $post[round_date_first][$i];
			$row->round_date_last  = $post[round_date_last][$i];

			try
			{
				$row->store();
			}
			catch (Exception $e)
			{
				$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
        $this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
			}
		}

		$msg  = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_INFO_2');
		$link = 'index.php?option=' . $option . '&view=jlextdfbkeyimport&layout=default_firstmatchday';
		$this->setRedirect($link, $msg);
	}


	/**
	 * sportsmanagementControllerjlextdfbkeyimport::insert()
	 *
	 * @return void
	 */
	function insert()
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();
		$post   = Factory::getApplication()->input->post->getArray(array());

		for ($i = 0; $i < count($post[roundcode]); $i++)
		{
			$mdl = BaseDatabaseModel::getInstance("match", "sportsmanagementModel");
			$row = $mdl->getTable();

			$row->round_id        = $post[round_id][$i];
			$row->match_number    = $post[match_number][$i];
			$row->projectteam1_id = $post[projectteam1_id][$i];
			$row->projectteam2_id = $post[projectteam2_id][$i];
			$row->published       = 1;

			// Convert dates back to mysql date format
			if (isset($post[match_date][$i]))
			{
				$post[match_date][$i] = strtotime($post[match_date][$i]) ? strftime('%Y-%m-%d', strtotime($post[match_date][$i])) : null;
			}
			else
			{
				$post[match_date][$i] = null;
			}

			$row->match_date = $post[match_date][$i];

			try
			{
				$row->store();
			}
			catch (Exception $e)
			{
				Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), Log::ERROR, 'jsmerror');
				Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), Log::ERROR, 'jsmerror');
			}
		}

		$msg  = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_INFO_1');
		$link = 'index.php?option=' . $option . '&view=rounds';
		$this->setRedirect($link, $msg);

	}

}


