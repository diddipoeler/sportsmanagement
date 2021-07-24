<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.00
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_new_project
 * @file       helper.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;

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
	 * modJSMNewProjectHelper::getData()
	 *
	 * @return
	 */
	public static function getData($new_project_article, $mycategory)
	{
		// Reference global application object
		$app    = Factory::getApplication();
		$date   = Factory::getDate();
		$user   = Factory::getUser();
		$db     = Factory::getDBO();
		$query  = $db->getQuery(true);
		$result = array();

		$heutestart = date("Y-m-d");
		$heuteende  = date("Y-m-d");

		$heutestart .= ' 00:00:00';
		$heuteende  .= ' 23:59:00';

		$query->select('pro.id,pro.name,pro.current_round as roundcode,CONCAT_WS(\':\',pro.id,pro.alias) AS project_slug,le.name as liganame,le.country');
		$query->select('le.picture as league_picture,pro.picture as project_picture');
		$query->from('#__sportsmanagement_project as pro');
		$query->join('INNER', '#__sportsmanagement_league as le on le.id = pro.league_id');
		$query->where('pro.modified BETWEEN ' . $db->Quote('' . $heutestart . '') . ' AND ' . $db->Quote('' . $heuteende . ''));
        $query->where('pro.published = 1');
		$query->order('pro.name ASC');

		$db->setQuery($query);
		$anzahl = $db->loadObjectList();

		foreach ($anzahl as $row)
		{
			if ($row->roundcode)
			{
				$query->clear();
				$query->select('r.name,CONCAT_WS(\':\',r.id,r.alias) AS round_slug');
				$query->from('#__sportsmanagement_round as r');
				$query->where('r.project_id = ' . $row->id);
				$query->where('r.id = ' . $row->roundcode);

				$db->setQuery($query);

				$result2        = $db->loadObject();
				$row->roundcode = $result2->round_slug;
			}

			$temp            = new stdClass;
			$temp->name      = $row->name;
			$temp->liganame  = $row->liganame;
			$temp->roundcode = $row->roundcode;

			// $temp->id = $row->id;
			$temp->id      = $row->project_slug;
			$temp->country = $row->country;
			$result[]      = $temp;
			$result        = array_merge($result);

			/** soll ein artikel erstellt werden ? */
			if ($new_project_article)
			{
				$query->clear();
				$query->select('id');
				$query->from('#__content');
				$query->where('title LIKE ' . $db->Quote('' . $row->name . ''));
				$query->where('created BETWEEN ' . $db->Quote('' . $heutestart . '') . ' AND ' . $db->Quote('' . $heuteende . ''));
				$db->setQuery($query);
				$article = $db->loadObject();

				if ($article)
				{
				}
				else
				{
					// Create and populate an object.
					$profile           = new stdClass;
					$profile->title    = $row->name;
					$profile->alias    = JFilterOutput::stringURLSafe($row->name);
					$profile->catid    = $mycategory;
					$profile->state    = 1;
					$profile->access   = 1;
					$profile->featured = 1;
					$profile->language = '*';

					$profile->created     = $date->toSql();
					$profile->created_by  = $user->get('id');
					$profile->modified    = $date->toSql();
					$profile->modified_by = $user->get('id');
/*
					$createroute = array("option"             => "com_sportsmanagement",
					                     "view"               => "resultsranking",
					                     "cfg_which_database" => 0,
					                     "s"                  => 0,
					                     "p"                  => $row->id,
					                     "r"                  => $row->roundcode);

					$query = sportsmanagementHelperRoute::buildQuery($createroute);
					$link  = Route::_('index.php?' . $query, false);
                    */
$routeparameter                       = array();
				$routeparameter['cfg_which_database'] = $app->input->getInt('cfg_which_database', ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database', 0));
				$routeparameter['s']                  = $app->input->getInt('s', 0);
				$routeparameter['p']                  = $row->id;
				$routeparameter['r']                  = $row->roundcode;
				$routeparameter['division']           = 0;
				$routeparameter['mode']               = 0;
				$routeparameter['order']              = 0;
				$routeparameter['layout']             = 0;
				$link                                 = sportsmanagementHelperRoute::getSportsmanagementRoute("resultsranking", $routeparameter);                    
                    
                    
                    
                    

					$profile->introtext = '<p><a href="' . $link . '">
<img src="' . $row->league_picture . '" alt="' . $row->liganame . '" style="float: left;" width="200" height="auto" />
' . $row->name . ' - ( ' . $row->liganame . ' )</a> neu angelegt/aktualisiert.
<img src="' . $row->project_picture . '" alt="' . $row->name . '" style="float: right;" width="200" height="auto" />
</p>';

					$profile->publish_up = $date->toSql();

					$resultinsert = Factory::getDbo()->insertObject('#__content', $profile);

					if ($resultinsert)
					{
						// Create and populate an object.
						$profile             = new stdClass;
						$profile->content_id = $db->insertid();
						$profile->ordering   = $db->insertid();
						$resultfrontpage     = Factory::getDbo()->insertObject('#__content_frontpage', $profile);
					}
				}
			}
		}

		return $result;

	}


}
