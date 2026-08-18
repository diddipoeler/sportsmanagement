<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.00
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_new_project
 * @file       helper.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;

JLoader::import('components.com_sportsmanagement.helpers.countries', JPATH_SITE);
JLoader::import('components.com_sportsmanagement.helpers.route', JPATH_SITE);

/**
 * modJSMNewProjectHelper
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2015
 * @version   $Id$
 * @access    public
 */
class modJSMNewProjectHelper
{
	/**
	 * Returns the projects which should be displayed by the module.
	 *
	 * This method is intentionally read-only. Content generation must be
	 * triggered explicitly through createArticles().
	 *
	 * @return array
	 */
	public static function getData()
	{
		$result = array();

		foreach (self::getChangedProjects() as $row)
		{
			$temp            = new stdClass;
			$temp->name      = $row->name;
			$temp->liganame  = $row->liganame;
			$temp->roundcode = $row->roundcode;
			$temp->id        = $row->project_slug;
			$temp->country   = $row->country;
			$result[]        = $temp;
		}

		return $result;
	}

	/**
	 * Explicitly creates Joomla articles for today's new/updated projects.
	 *
	 * This method is deliberately not called from the module render path.
	 * Callers which want content generation must invoke it explicitly.
	 *
	 * @param int $mycategory Joomla content category id
	 *
	 * @return int Number of articles created
	 */
	public static function createArticles($mycategory)
	{
		$app   = Factory::getApplication();
		$date  = Factory::getDate();
		$user  = Factory::getUser();
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		list($heutestart, $heuteende) = self::getTodayRange();

		$created = 0;
		$autotweetEnabled = ComponentHelper::getComponent('com_autotweet', true)->enabled;

		if ($autotweetEnabled)
		{
			include_once JPATH_ADMINISTRATOR . '/components/com_autotweet/helpers/autotweetbase.php';
		}

		foreach (self::getChangedProjects() as $row)
		{
			$query->clear();
			$query->select('id');
			$query->from('#__content');
			$query->where('xreference = ' . (int) $row->id);
			$query->where('created BETWEEN ' . $db->quote($heutestart) . ' AND ' . $db->quote($heuteende));
			$db->setQuery($query);

			if ($db->loadObject())
			{
				continue;
			}

			$profile              = new stdClass;
			$profile->title       = $row->name;
			$profile->alias       = OutputFilter::stringURLSafe($row->name);
			$profile->catid       = (int) $mycategory;
			$profile->xreference  = $row->id;
			$profile->state       = 1;
			$profile->access      = 1;
			$profile->featured    = 1;
			$profile->language    = '*';
			$profile->created     = $date->toSql();
			$profile->created_by  = 62;
			$profile->modified    = $date->toSql();
			$profile->modified_by = $user->get('id');

			$routeparameter                       = array();
			$routeparameter['cfg_which_database'] = $app->input->getInt('cfg_which_database', ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database', 0));
			$routeparameter['s']                  = $app->input->getInt('s', 0);
			$routeparameter['p']                  = $row->id;
			$routeparameter['r']                  = $row->roundcode;
			$routeparameter['division']           = 0;
			$routeparameter['mode']               = 0;
			$routeparameter['order']              = 0;
			$routeparameter['layout']             = 0;
			$link                                 = sportsmanagementHelperRoute::getSportsmanagementRoute('resultsranking', $routeparameter);

			if (!$row->project_picture)
			{
				$row->project_picture = ComponentHelper::getParams('com_sportsmanagement')->get('ph_project', '');
			}

			if (!$row->league_picture)
			{
				$row->league_picture = ComponentHelper::getParams('com_sportsmanagement')->get('ph_project', '');
			}

			$profile->introtext = '<p><a href="' . $link . '">
<img src="' . $row->league_picture . '" alt="' . $row->liganame . '" style="float: left;" width="100" height="auto" />
' . $row->name . ' - ( ' . $row->liganame . ' )</a> neu angelegt/aktualisiert.
<img src="' . $row->project_picture . '" alt="' . $row->name . '" style="float: right;" width="100" height="auto" />
</p>';
			$profile->publish_up = $date->toSql();

			if (!$db->insertObject('#__content', $profile))
			{
				continue;
			}

			$articleId = $db->insertid();

			$frontpage             = new stdClass;
			$frontpage->content_id = $articleId;
			$frontpage->ordering   = $articleId;
			$db->insertObject('#__content_frontpage', $frontpage);

			if ($autotweetEnabled)
			{
				$query->clear();
				$query->select('*');
				$query->from('#__content');
				$query->where('id = ' . (int) $articleId);
				$db->setQuery($query);
				$article = $db->loadObject();

				if ($article)
				{
					Factory::getApplication()->triggerEvent('onContentAfterSave', array('com_content.article', $article, 1));
				}
			}

			$created++;
		}

		return $created;
	}

	/**
	 * Loads today's published projects which were modified today.
	 *
	 * @return array
	 */
	private static function getChangedProjects()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		list($heutestart, $heuteende) = self::getTodayRange();

		$query->select("pro.id, pro.name, pro.current_round AS roundcode, CONCAT_WS(':', pro.id, pro.alias) AS project_slug, le.name AS liganame, le.country");
		$query->select('le.picture AS league_picture, pro.picture AS project_picture');
		$query->from('#__sportsmanagement_project AS pro');
		$query->join('INNER', '#__sportsmanagement_league AS le ON le.id = pro.league_id');
		$query->where('pro.modified BETWEEN ' . $db->quote($heutestart) . ' AND ' . $db->quote($heuteende));
		$query->where('pro.published = 1');
		$query->order('pro.name ASC');

		$db->setQuery($query);
		$projects = $db->loadObjectList();

		if (!$projects)
		{
			return array();
		}

		foreach ($projects as $row)
		{
			if (!$row->roundcode)
			{
				continue;
			}

			$query->clear();
			$query->select("r.name, CONCAT_WS(':', r.id, r.alias) AS round_slug");
			$query->from('#__sportsmanagement_round AS r');
			$query->where('r.project_id = ' . (int) $row->id);
			$query->where('r.id = ' . (int) $row->roundcode);
			$db->setQuery($query);
			$round = $db->loadObject();

			if ($round)
			{
				$row->roundcode = $round->round_slug;
			}
		}

		return $projects;
	}

	/**
	 * Returns today's SQL range in the same format as the previous implementation.
	 *
	 * @return array
	 */
	private static function getTodayRange()
	{
		$today = date('Y-m-d');

		return array($today . ' 00:00:00', $today . ' 23:59:00');
	}
}
