<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_calendar
 * @file       helper.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'calendarClass.php';


/**
 * modJSMCalendarHelper
 *
 * @package
 * @author    diddi
 * @copyright 2015
 * @version   $Id$
 * @access    public
 */
class modJSMCalendarHelper
{

	/**
	 * modJSMCalendarHelper::showCal()
	 *
	 * @param   mixed    $params
	 * @param   mixed    $year
	 * @param   mixed    $month
	 * @param   integer  $ajax
	 * @param   mixed    $modid
	 *
	 * @return
	 */
	function showCal(&$params, $year, $month, $ajax = 0, $modid) // This function returns the html of the calendar for a given month
	{
		// Global $mainframe;
		// Reference global application object
		$app = Factory::getApplication();

		$language = Factory::getLanguage(); // Get the current language
		$language->load('mod_sportsmanagement_calendar'); // load the language ini file of the module
		$article  = $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_VALUEMATCH');
		$articles = $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_VALUEMATCHES'); // This strings are used for the titles of the links
		$article2 = $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_MATCHTHISDAY');

		$cal       = new JSMCalendar; // This object creates the html for the calendar
		$dayNamLen = $params->get('cal_length_days');

		$cal->dayNames = array(
			substr(Text::_('SUN'), 0, $dayNamLen),
			substr(Text::_('MON'), 0, $dayNamLen),
			substr(Text::_('TUE'), 0, $dayNamLen),
			substr(Text::_('WED'), 0, $dayNamLen),
			substr(Text::_('THU'), 0, $dayNamLen),
			substr(Text::_('FRI'), 0, $dayNamLen),
			substr(Text::_('SAT'), 0, $dayNamLen)
		);

		$cal->monthNames = array(
			Text::_('JANUARY'),
			Text::_('FEBRUARY'),
			Text::_('MARCH'),
			Text::_('APRIL'),
			Text::_('MAY'),
			Text::_('JUNE'),
			Text::_('JULY'),
			Text::_('AUGUST'),
			Text::_('SEPTEMBER'),
			Text::_('OCTOBER'),
			Text::_('NOVEMBER'),
			Text::_('DECEMBER')
		);

		$cal->startDay = $params->get('cal_start_day');

		// Set the startday (this is the day that appears in the first column). Sunday = 0
		// it is loaded from the language ini because it may vary from one country to another, in Spain
		// for example, the startday is Monday (1)
		$cal->lightbox             = $params->get('lightbox');
		$cal->lightbox_on_pageload = $params->get('lightbox_on_pageload');
		$cal::$prefix              = $params->get('custom_prefix');
		$cal->usedteams            = $params->get('usedteams');
		$cal->usedclubs            = $params->get('usedclubs');
		$cal::$params              = $params;

		// Set the link for the month, this will be the link for the calendar header (ex. December 2007)
		$cal->monthLink = Route::_(
			'index.php?option=com_joomleague_calendar' . '&year=' . $year .
			'&month=' . $month . '&modid=' . $modid
		);
		$cal->modid     = $modid;
		$cal->ajax      = $ajax;
		$cal::getMatches($month, $year);
		$counter = Array();
		jimport('joomla.utilities.date');

		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			$config     = Factory::getConfig();
			$offset     = $config->get('offset');
			$dateformat = 'Format';
		}
		else
		{
			$config     = Factory::getConfig();
			$offset     = $config->get('offset');
			$dateformat = 'toFormat';
		}

		foreach ($cal::$matches as $row)
		{
			$created = new JDate($row['date'], $offset);

			if (version_compare(JVERSION, '3.0.0', 'ge'))
			{
				$createdYear  = $created->$dateformat('Y');
				$createdMonth = $created->$dateformat('m');
				$createdDay   = $created->$dateformat('d'); // Have to use %d because %e doesn't works on windows
			}
			else
			{
				$createdYear  = $created->$dateformat('%Y');
				$createdMonth = $created->$dateformat('%m');
				$createdDay   = $created->$dateformat('%d'); // Have to use %d because %e doesn't works on windows
			}

			$createdDate                           = $createdYear . $createdMonth . $createdDay; // This makes an unique variable for every day
			$counter[$createdDate]['createdYear']  = $createdYear;
			$counter[$createdDate]['createdMonth'] = $createdMonth;
			$counter[$createdDate]['createdDay']   = $createdDay;

			if (version_compare(JVERSION, '3.0.0', 'ge'))
			{
				$counter[$createdDate]['tiptitle'] = $created->$dateformat('l, d.m.Y');
			}
			else
			{
				$counter[$createdDate]['tiptitle'] = $created->$dateformat('%A, %d.%m.%Y');
			}

			if (!isset($counter[$createdDate]['count']))
			{
				$counter[$createdDate]['count'] = 1;
			}
			else
			{
				$counter[$createdDate]['count'] += 1; // $counter[$date] counts the number of articles in each day, to display it as a title in the link of the day
			}
		}

		foreach ($counter AS $createdDate => $val)
		{
			$title                                 = $counter[$createdDate]['tiptitle'] . ' :: ' . $counter[$createdDate]['count'] . ' ';
			$title                                 .= ($counter[$createdDate]['count'] > 1) ? $articles : $article;
			$title                                 .= ' ' . $article2;
			$inject                                = $params->get('inject', 0);
			$update_module                         = $params->get('update_module', 0);
			$cal::$linklist[$createdDate]['click'] = 'jlCalmod_showhide(\'jlCalList-' . $modid . '\', \'jlcal_'
				. $counter[$createdDate]['createdYear'] . '-' . $counter[$createdDate]['createdMonth'] . '-' . $counter[$createdDate]['createdDay'] . '-' . $modid
				. '\', \'' . str_replace(' :: ', ': ', $title) . '\', ' . $inject . ', ' . $modid . ');';
			$cal::$linklist[$createdDate]['link']  = 'javascript:void(0)';
			$cal::$linklist[$createdDate]['link']  .= "\" title=\"";

			// The calendar class sets the links this way: <a href=" . THE LINK STRING . ">
			// so, the easiest way to add a title to that link is by setting THE LINK STRING = the link" title="the title
			// the result link would be <a href="the link" title="the title">
			$cal::$linklist[$createdDate]['link'] .= $title;

			// The above 3 lines output something like: 3 articles on this day. Or: 1 article on this day
		}

		return $cal->getMonthView($month, $year);

	}

	/**
	 * modJSMCalendarHelper::getDate_byId()
	 *
	 * @param   mixed  $id
	 *
	 * @return
	 */
	function getDate_byId($id)
	{
		// Global $mainframe;
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$db     = sportsmanagementHelper::getDBConnection();
		$query  = $db->getQuery(true);

		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			$config     = Factory::getConfig();
			$offset     = $config->get('offset');
			$dateformat = 'Format';
		}
		else
		{
			$offset     = 0;
			$dateformat = 'toFormat';
		}

		$query->select("match_date");
		$query->from('#__sportsmanagement_matches');
		$query->where('match_id=\'' . $id . '\'');
		$db->setQuery($query);
		$row = $db->loadObjectList();

		jimport('joomla.utilities.date');
		$created = new JDate($row[0]->match_date, $offset);

		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			$createdYear  = $created->$dateformat('Y');
			$createdMonth = $created->$dateformat('m');
			$createdDay   = $created->$dateformat('d'); // Have to use %d because %e doesn't works on windows
		}
		else
		{
			$createdYear  = $created->$dateformat('%Y');
			$createdMonth = $created->$dateformat('%m');
			$createdDay   = $created->$dateformat('%d');
		}

		$createdDate = Array($createdYear, $createdMonth, $createdDay);

		return $createdDate;
	}

	/**
	 * modJSMCalendarHelper::showDropDown()
	 *
	 * @param   mixed    $params
	 * @param   mixed    $year
	 * @param   mixed    $month
	 * @param   mixed    $day
	 * @param   integer  $ajax
	 *
	 * @return
	 */
	function showDropDown($params, $year, $month, $day, $ajax = 0)
	{
		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			$dateformat = 'Format';
		}
		else
		{
			$dateformat = 'toFormat';
		}

		$results = $this->setTheQuery($params, $year, $month, $day, $ajax, 1);

		foreach ($results as $key => $result)
		{
			$created = new JDate($results[$key]->match_date);

			if (version_compare(JVERSION, '3.0.0', 'ge'))
			{
				$createdYear  = $created->$dateformat('Y');
				$createdMonth = $created->$dateformat('m');
			}
			else
			{
				$createdYear  = $created->$dateformat('%Y');
				$createdMonth = $created->$dateformat('%m');
			}

			$results[$key]->year  = $createdYear;
			$results[$key]->month = $createdMonth;

			$createdYear == $year ? $articleCounter[$createdYear]['now'] = true : '';
			$createdMonth == $month ? $articleCounter[$createdYear][$createdMonth]['now'] = true : '';

			if (!isset($articleCounter[$createdYear][$createdMonth]['total']))
			{
				$articleCounter[$createdYear][$createdMonth]['total'] = 0;
			}

			if (!isset($articleCounter[$createdYear]['total']))
			{
				$articleCounter[$createdYear]['total'] = 0;
			}

			$articleCounter[$createdYear][$createdMonth]['total']++;
			$articleCounter[$createdYear]['total']++;
		}

		return array($results, $articleCounter);
	}

}


/**
 * JSMCalendar
 *
 * @package
 * @author    diddi
 * @copyright 2015
 * @version   $Id$
 * @access    public
 */
class JSMCalendar extends PHPCalendar
{
	static $linklist; // This variable will be an array that contains all the links of the month

	static $prefix;

	static $params;

	static $matches = array();

	static $teams = array();

	static $teamslist = array();

	/**
	 * JSMCalendar::addTeam()
	 *
	 * @param   mixed   $id
	 * @param   string  $name
	 * @param   string  $pic
	 *
	 * @return
	 */
	static function addTeam($id, $name = '', $pic = '')
	{
		if (!array_key_exists($id, self::$teams) && $id > 0)
		{
			self::$teams[$id]          = new stdclass;
			self::$teams[$id]->value   = $id;
			self::$teams[$id]->name    = $name;
			self::$teams[$id]->picture = $pic;
			self::$teamslist[]         = self::$teams[$id];
		}
	}

	/**
	 * JSMCalendar::jl_utf8_convert()
	 *
	 * @param   mixed   $text
	 * @param   string  $fromenc
	 * @param   string  $toenc
	 *
	 * @return
	 */
	static function jl_utf8_convert($text, $fromenc = 'iso-8859-1', $toenc = 'UTF-8')
	{
		if (strtolower($fromenc) == strtolower($toenc) || SportsmanagementConnector::$params->get('convert', 0) == 0)
		{
			return $text;
		}
		elseif (function_exists('iconv'))
		{
			return iconv($fromenc, $toenc, $text);
		}
		elseif (strtolower($fromenc) == 'iso-8859-1' && strtolower($toenc) == 'utf-8')
		{
			return utf8_encode($text);
		}
		elseif (strtolower($fromenc) == 'utf-8' && strtolower($toenc) == 'iso-8859-1')
		{
			return utf8_decode($text);
		}
		else
		{
			return $text;
		}
	}

	/**
	 * JSMCalendar::asc()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	static function asc($a, $b)
	{
		if ($a < $b)
		{
			return -1;
		}

		if ($a == $b)
		{
			return 0;
		}

		return 1;
	}


	// Return the URL to link to in order to display a calendar for a given month/year.
	// This function is called to get the links of the two arrows in the header.

	/**
	 * JSMCalendar::desc()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	static function desc($a, $b)
	{
		if ($a > $b)
		{
			return -1;
		}

		if ($a == $b)
		{
			return 0;
		}

		return 1;
	}

	/**
	 * JSMCalendar::getMatches()
	 *
	 * @param   mixed  $month
	 * @param   mixed  $year
	 *
	 * @return
	 */
	static function getMatches($month, $year)
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput                     = $app->input;
		$livescore                  = self::$params->get('livescore', '');
		$caldates                   = array();
		$caldates['start']          = "$year-$month-01 00:00:00";
		$caldates['end']            = "$year-$month-31 23:59:59";
		$caldates['starttimestamp'] = sportsmanagementHelper::getTimestamp($caldates['start']);
		$caldates['endtimestamp']   = sportsmanagementHelper::getTimestamp($caldates['end']);
		$caldates['roundstart']     = "$year-$month-01";
		$caldates['roundend']       = "$year-$month-31";

		$jlrows     = array();
		$lsrows     = array();
		$usejevents = self::$params->get('jevents', 0);

		if ($usejevents == 1)
		{
			$day = 0;
			include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'connectors' . DIRECTORY_SEPARATOR . 'jevents.php';
			JEventsConnector::getEntries($caldates, self::$params, self::$matches);
		}

		include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'connectors' . DIRECTORY_SEPARATOR . 'sportsmanagement.php';
		self::$params->prefix = self::$prefix;
		sportsmanagementConnector::getEntries($caldates, self::$params, self::$matches);

		if ($livescore != '')
		{
			include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'connectors' . DIRECTORY_SEPARATOR . 'livescore.php';
			self::$params->prefix = self::$params->get('prefix_livescore', '');
			LivescoreConnector::getMatches($caldates, self::$params, $this->matches);
		}

		$matches       = self::sortArray(self::$matches, 'asc', 'date');
		self::$matches = $matches;

		return $matches;
	}

	/**
	 * JSMCalendar::sortArray()
	 *
	 * @param   mixed   $array
	 * @param   mixed   $comparefunction
	 * @param   string  $property
	 *
	 * @return
	 */
	static function sortArray($array, $comparefunction, $property = '')
	{
		$zcount = count($array);

		for ($i = 1; $i < $zcount; $i++)
		{
			for ($a = $zcount - 1; $a >= $i; $a--)
			{
				if (self::$comparefunction($array[$a - 1][$property], $array[$a][$property]) > 0)
				{
					$tempzal       = $array[$a - 1];
					$array[$a - 1] = $array[$a];
					$array[$a]     = $tempzal;
				}
			}
		}

		return $array;
	}

	/**
	 * JSMCalendar::getDateLink()
	 *
	 * @param   mixed  $day
	 * @param   mixed  $month
	 * @param   mixed  $year
	 *
	 * @return
	 */
	function getDateLink($day, $month, $year) // This function is called from getMonthView(month,year) to get the link of the given day
	{
		// If this function returns nothing (""), then getMonthView wont put a link on that day
		// Reference global application object
		$app = Factory::getApplication();

		$link = "";

		if (strlen($month) < 2)
		{
			$month = '0' . $month;
		}

		if (strlen($day) < 2)
		{
			$day = '0' . $day;
		}

		$date = $year . $month . $day;

		if (isset(self::$linklist[$date]['link']))
		{
			$link = self::$linklist[$date]['link'];  // $this->linklist[$date] was set for every date in the foreach bucle at lines 50-83
		}

		return $link;
	}

	/**
	 * JSMCalendar::getDateClick()
	 *
	 * @param   mixed  $day
	 * @param   mixed  $month
	 * @param   mixed  $year
	 *
	 * @return
	 */
	function getDateClick($day, $month, $year) // This function is called from getMonthView(month,year) to get the link of the given day
	{
		// If this function returns nothing (""), then getMonthView wont put a link on that day
		$link = "";

		if (strlen($month) < 2)
		{
			$month = '0' . $month;
		}

		if (strlen($day) < 2)
		{
			$day = '0' . $day;
		}

		$date = $year . $month . $day;

		if (isset(self::$linklist[$date]['click']))
		{
			$link = self::$linklist[$date]['click'];  // $this->linklist[$date] was set for every date in the foreach bucle at lines 50-83
		}

		return $link;
	}

	/**
	 * JSMCalendar::getCalendarLink()
	 *
	 * @param   mixed  $month
	 * @param   mixed  $year
	 *
	 * @return
	 */
	function getCalendarLink($month, $year)
	{
		$getquery     = Factory::getApplication()->input->get('GET'); // Get the GET query
		$calendarLink = Uri::current() . '?'; // get the current url, without the GET query; and add "?", to set the GET vars

		if (!empty($getquery))
		{
			foreach ($getquery as $key => $value)
			{
				// This bucle goes through every GET variable that was in the url

				if ($key != 'month' && $key != 'year' && $key != 'day' && $value)
				{
					// The month,year, and day Variables must be diferent of the current ones, because this is a link for a diferent month

					$calendarLink .= $key . '=' . $value . '&amp;';
				}
			}

			$calendarLink .= 'month=' . $month . '&amp;year=' . $year; // Add the month and the year that was passed to the function to the GET string
		}

		return $calendarLink;
	}

	/**
	 * JSMCalendar::matches_output()
	 *
	 * @param   mixed  $month
	 * @param   mixed  $year
	 *
	 * @return
	 */
	function matches_output($month, $year)
	{
		// Global $mainframe;
		// Reference global application object
		$app = Factory::getApplication();

		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			$config        = Factory::getConfig();
			$offset        = $config->get('offset');
			$dateformat    = 'Format';
			$dateoutformat = 'Y-m-d';
		}
		else
		{
			$config        = Factory::getConfig();
			$offset        = $config->get('offset');
			$dateformat    = 'toFormat';
			$dateoutformat = '%Y-%m-%d';
		}

		$language = Factory::getLanguage(); // Get the current language
		$language->load('mod_sportsmanagement_calendar'); // load the language ini file of the module
		$article     = $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_VALUEMATCH');
		$articles    = $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_VALUEMATCHES'); // This strings are used for the titles of the links
		$article2    = $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_MATCHTHISDAY');
		$noarticle   = $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_NOMATCHES');
		$outstring   = '';
		$todaystring = '';
		$matches     = self::$matches;
		$div         = '';
		$now         = new JDate;

		$today = $now->$dateformat($dateoutformat);

		$todaytitle = '';
		$pm         = '';

		// $offset = 0; // $mainframe->getCfg('offset');
		$update_module    = self::$params->get('update_module', 0);
		$totalgamesstring = (count($matches) > 0) ? count($matches) : $noarticle;
		$totalgamesstring .= ' ';
		$totalgamesstring .= (count($matches) > 1) ? $articles : $article;
		$totalgamesstring .= ' ';
		$totalgamesstring .= $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_VALUEMATCHESMONTH') . ' ' . $this->monthNames[$month - 1] . ' ' . $year;
		$thistitle        = ($todaytitle != '') ? $todaytitle : $totalgamesstring;
		$thistitle        = $totalgamesstring;
		$format           = array();
		$format[]         = array('tag' => 'span', 'divid' => 'oldjlCalListTitle-' . $this->modid, 'class' => 'jlcal_hiddenmatches', 'text' => $totalgamesstring);
		$format[]         = array('tag' => 'span', 'divid' => 'jlCalListTitle-' . $this->modid, 'class' => 'jlCalListTitle', 'text' => $thistitle);
		$format[]         = array('tag' => 'span', 'divid' => 'jlCalListDayTitle-' . $this->modid, 'class' => 'jlCalListTitle', 'text' => '');

		for ($x = 0; $x < count($matches); $x++)
		{
			$sclass = ($x % 2) ? 'sectiontableentry1' : 'sectiontableentry2';
			$row    = $matches[$x];
			$thispm = $row['project_id'] . '_' . $row['matchcode'] . '_' . $row['type'];

			$da = new JDate($row['date'], $offset);

			if ($div != $da->$dateformat($dateoutformat))
			{
				$counter  = 0;
				$div      = $da->$dateformat($dateoutformat);
				$format[] = array('tag' => 'div', 'divid' => 'jlcal_' . $div . "-" . $this->modid, 'class' => 'jlcal_hiddenmatches');
				$format[] = array('tag' => 'table', 'divid' => 'jlcal_' . $div . "-" . $this->modid, 'class' => 'jlcal_result_table');
			}

			if ($pm != $thispm)
			{
				$format[]  = array('tag' => 'headingrow', 'text' => $row['headingtitle']);
				$roundname = $row['headingtitle'];
			}

			$pm       = $thispm;
			$format[] = $row;
			$counter++;

			if (isset($matches[$x + 1]))
			{
				$nd = new JDate($matches[$x + 1]['date'], $offset);
			}
			else
			{
				$nd = false;
			}

			if (!$nd || $nd->$dateformat($dateoutformat) != $da->$dateformat($dateoutformat))
			{
				$pm        = '';
				$format[]  = array('tag' => 'tableend');
				$format[]  = array('tag' => 'divend');
				$titletext = $counter;
				$titletext .= ' ';
				$titletext .= ($counter > 1) ? $articles : $article;
				$titletext .= ' ';
				$titletext .= ($today == $da->$dateformat($dateoutformat)) ? $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_TODAY') : $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_AT');
				$titletext .= ' ' . $da->$dateformat('d') . '. ' . $this->monthNames[$month - 1] . ' ' . $year;
				$format[]  = array('tag' => 'span', 'divid' => 'jlcaltitte_' . $div . "-" . $this->modid, 'class' => 'jlcal_hiddenmatches', 'text' => $titletext);
			}
		}

		return $format;
	}

	/**
	 * JSMCalendar::output_teamlist()
	 *
	 * @return
	 */
	function output_teamlist()
	{
		$teamslist = array();

		if (count(self::$teams) > 0 && self::$params->get('show_teamslist', 0) == 1)
		{
			$teams       = self::sortObject(self::$teamslist, 'asc', 'name');
			$teamslist[] = HTMLHelper::_('select.option', 0, Text::_(self::$params->get('teamslist_option')));

			foreach ($teams AS $id => $obj)
			{
				$teamslist[] = HTMLHelper::_('select.option', $obj->value, Text::_($obj->name));
			}
		}

		return $teamslist;
	}

	/**
	 * JSMCalendar::sortObject()
	 *
	 * @param   mixed   $array
	 * @param   mixed   $comparefunction
	 * @param   string  $property
	 *
	 * @return
	 */
	function sortObject($array, $comparefunction, $property = '')
	{
		$zcount = count($array);

		for ($i = 1; $i < $zcount; $i++)
		{
			for ($a = $zcount - 1; $a >= $i; $a--)
			{
				if ($this->$comparefunction($array[$a - 1]->$property, $array[$a]->$property) > 0)
				{
					$tempzal       = $array[$a - 1];
					$array[$a - 1] = $array[$a];
					$array[$a]     = $tempzal;
				}
			}
		}

		return $array;
	}
}
