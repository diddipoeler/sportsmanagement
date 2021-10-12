<?php

/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage plugins
 * @file       jsmprofile.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\Utilities\ArrayHelper;

/**
 * plgUserjsmprofile
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class plgUserjsmprofile extends JPlugin
{
	/**
	 * plgUserjsmprofile::onContentPrepareData()
	 *
	 * @param  mixed $context
	 * @param  mixed $data
	 * @return
	 */
	function onContentPrepareData($context, $data)
	{
		$app = Factory::getApplication();
		/**
		 *
		 * Check we are manipulating a valid form.
		 */
		if (!in_array($context, array('com_users.user', 'com_users.profile', 'com_admin.profile')))
		{
			return true;
		}

		$userId = isset($data->id) ? $data->id : 0;

		/**
		 *
		 * Load the profile data from the database.
		 */
		$db = Factory::getDbo();
		$db->setQuery(
			'SELECT profile_key, profile_value FROM #__user_profiles' .
				' WHERE user_id = ' . (int) $userId .
				' AND profile_key LIKE \'jsmprofile.%\'' .
				' ORDER BY ordering'
		);

		try
		{
			$results = $db->loadRowList();
		}
		catch (JException $e)
		{
			$this->_subject->setError($e->getMessage());
			return false;
		}

		/**
		 *
		 * Merge the profile data.
		 */
		$data->jsmprofile = array();
		foreach ($results as $v)
		{
			$k = str_replace('jsmprofile.', '', $v[0]);
			$data->jsmprofile[$k] = $v[1];
		}

		return true;
	}

	/**
	 * plgUserjsmprofile::onContentPrepareForm()
	 *
	 * @param  mixed $form
	 * @param  mixed $data
	 * @return
	 */
	function onContentPrepareForm($form, $data)
	{
		$app = Factory::getApplication();
		/**
		 *
		 * Load user_profile plugin language
		 */
		$lang = Factory::getLanguage();
		$lang->load('plg_user_jsmprofile', JPATH_ADMINISTRATOR);

		if (!($form instanceof Form))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');
			return false;
		}
		/**
		 *
		 * Check we are manipulating a valid form.
		 */
		if (!in_array($form->getName(), array('com_users.user', 'com_admin.profile')))
		{
			return true;
		}

		/**
		 *
		 * Add the profile fields to the form.
		 */
		Form::addFormPath(dirname(__FILE__) . '/profiles');
		$form->loadFile('profile', false);
	}

	/**
	 * plgUserjsmprofile::onUserAfterSave()
	 *
	 * @param  mixed $data
	 * @param  mixed $isNew
	 * @param  mixed $result
	 * @param  mixed $error
	 * @return
	 */
	function onUserAfterSave($data, $isNew, $result, $error)
	{
		$app = Factory::getApplication();
		$userId = ArrayHelper::getValue($data, 'id', 0, 'int');

		if ($userId && $result && isset($data['jsmprofile']) && (count($data['jsmprofile'])))
		{
			try
			{
				$db = Factory::getDbo();
				$db->setQuery('DELETE FROM #__user_profiles WHERE user_id = ' . $userId . ' AND profile_key LIKE \'jsmprofile.%\'');

				if (!$db->execute())
				{
					throw new Exception($db->getErrorMsg());
				}

				$tuples = array();
				$order    = 1;

				foreach ($data['jsmprofile'] as $k => $v)
				{
					$tuples[] = '(' . $userId . ', ' . $db->quote('jsmprofile.' . $k) . ', ' . $db->quote($v) . ', ' . $order++ . ')';
				}

				$db->setQuery('INSERT INTO #__user_profiles VALUES ' . implode(', ', $tuples));

				if (!$db->execute())
				{
					throw new Exception($db->getErrorMsg());
				}
			}
			catch (JException $e)
			{
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}

		return true;
	}

	/**
	 * plgUserjsmprofile::onUserAfterDelete()
	 *
	 * @param  mixed $user
	 * @param  mixed $success
	 * @param  mixed $msg
	 * @return
	 */
	function onUserAfterDelete($user, $success, $msg)
	{
		$app = Factory::getApplication();

		if (!$success)
		{
			return false;
		}

		$userId = ArrayHelper::getValue($user, 'id', 0, 'int');

		if ($userId)
		{
			try
			{
				$db = Factory::getDbo();
				$db->setQuery(
					'DELETE FROM #__user_profiles WHERE user_id = ' . $userId .
						" AND profile_key LIKE 'jsmprofile.%'"
				);

				if (!$db->execute())
				{
					throw new Exception($db->getErrorMsg());
				}
			}
			catch (JException $e)
			{
				$this->_subject->setError($e->getMessage());

				return false;
			}
		}

		return true;
	}
}
