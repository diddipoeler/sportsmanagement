<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      comments.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage helpers
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

/**
 * sportsmanagementComments
 *
 * @package
 * @author jst71
 * @copyright diddi
 * @version 2020
 * @access public
 */
class sportsmanagementModelComments
{
	static function CreateInstance(&$config) {
		// prefer Kunena Comments, if configured in overall template
		if (($config['show_project_kunena_link'] == true) && (ComponentHelper::isEnabled('com_kunena')))
		{
			return new sportsmanagementModelCommentsKunena($config);
		}
		else
		{
			if(ComponentHelper::isEnabled('com_jcomments'))
			{
				return new sportsmanagementModelCommentsJSMJComments($config);
			}
			else
			{
				// return a base class instance as dummy
				Log::add( Text::_('Es ist keine Kommentarkomponente installiert'));
				return new sportsmanagementModelComments();
			}
		}
	}

    /**
	 * get information, if comments are enabled and working
	 *
	 * @return bool
	 */
	function isEnabled() {
		return false;
	}

    /**
	 * display the configured match specific Commenticon
	 *
	 * @param object $match the match to check comments for
	 * @param object $hometeam the team 1 which take part on the match
	 * @param object $guestteam the team 2 which take part on the match
	 * @param object $config configuration of the parent screen / template
	 * @param object $project reference to parent project (used for slug)
	 * @return string
	 */
	function showMatchCommentIcon(&$match, &$hometeam, &$guestteam, &$config, &$project)
	{
		$imgTitle = "Comments not available";
		if ($config['show_comments_count'] == 1) {
			$href_text		= HTMLHelper::image( Uri::root().'media/com_sportsmanagement/jl_images/discuss.gif', $imgTitle, array(' title' => $imgTitle,' border' => 0,' style' => 'vertical-align: middle'));
		} elseif ($config['show_comments_count'] == 2) {
			$href_text		= '<span title="'. $imgTitle .'">(0)</span>';
		}
		//Link
		if (isset($match->team1_result))
		{
	        $routeparameter = array();
			$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database',0);
			$routeparameter['s'] = Factory::getApplication()->input->getInt('s',0);
			$routeparameter['p'] = $project->slug;
			$routeparameter['mid'] = $match->id;
			$link = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter);

		} else {
			$link=sportsmanagementHelperRoute::getNextMatchRoute($project->slug,$match->id).'#comments';
		}
		$viewComment	= HTMLHelper::link($link, $href_text);
		return $viewComment;
	}

    /**
	 * display the configured match specific Commenticon
	 *
	 * @param object $match the match to check comments for
	 * @param object $hometeam the team 1 which take part on the match
	 * @param object $guestteam the team 2 which take part on the match
	 * @param object $config configuration of the parent screen / template
	 * @param object $project reference to parent project (used for slug)
	 * @return array
	 */
	function showMatchComments(&$match, &$hometeam, &$guestteam, &$config, &$project)
	{
		return "Comments not available";
	}

    /**
	 * helper method to get the recommended subject of a topic to discuss a match
	 *
	 * Diese statische Methode kann aus Kunena aufgerufen werden, um den Betreff des Themas (Spielpaarung "Heim - Gast")
	 * automatisch auszufüllen
	 *
	 * Codesnippet: components/com_kunena/controller/topic/form/create/display.php, e/o function before()
	 * 	protected function before()
	 * 	{
	 * 		parent::before();
	 * 		$catid = $this->input->getInt('catid', 0);
	 *		(...)
	 * 		$this->headerText = Text::_('COM_KUNENA_NEW_TOPIC');
	 *
	 * 		// +++snip: JSM Match comments
	 * 		$CommentMatchID = $this->input->getInt('CommentMatchID', 0);
	 * 		if ($CommentMatchID > 0) {
	 * 			JLoader::import('components.com_sportsmanagement.helpers.comments', JPATH_SITE);
	 * 			$this->message->subject = sportsmanagementModelComments::getForumSubjectFromMatchID($CommentMatchID);
	 * 		}
	 * 		// ---snip: JSM Match comments
	 * 		return true;
	 * 	}
	 *
	 * @param int $match_id
	 * @return string
	 */
	static function getForumSubjectFromMatchID($match_id) {
		$subject = "Bitte Spielpaarung hier eingeben!";
		if ($match_id > 0)
		{
			$database = JFactory::getDBO();
			$query = sprintf("SELECT t1.name AS home,t2.name AS away
									FROM `#__sportsmanagement_match` AS m
									INNER JOIN #__sportsmanagement_project_team tj1 ON tj1.id=m.projectteam1_id
									INNER JOIN #__sportsmanagement_team t1 ON t1.id=tj1.team_id
									INNER JOIN #__sportsmanagement_project_team tj2 ON tj2.id=m.projectteam2_id
									INNER JOIN #__sportsmanagement_team t2 ON t2.id=tj2.team_id
									WHERE m.id= '%s'",$match_id);
			$database->setQuery($query);
			$teamnames = $database->loadAssoc();
			//echo"<pre>".print_r($teamnames)."</pre>";
			if (is_array($teamnames)) {
				$subject = $teamnames['home'] . " - " . $teamnames['away'];
			}
		}
		return $subject;
	}

    /**
	 * helper method to get the Icon and the alternative Text to display the status of matches comments
	 *
	 * @param int $match_id
	 * @return string
	 */
	protected function getHRefText($count, &$config)
	{
		$href_text ="";
		if ($count == 1) {
			$imgTitle		= $count.' '.Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_COMMENTS_COUNT_SINGULAR');
			if ($config['show_comments_count'] == 1) {
				$href_text		= HTMLHelper::image( Uri::root().'media/com_sportsmanagement/jl_images/discuss_active.gif', $imgTitle, array(' title' => $imgTitle,' border' => 0,' style' => 'vertical-align: middle'));
			} elseif ($config['show_comments_count'] == 2) {
				$href_text		= '<span title="'. $imgTitle .'">('.$count.')</span>';
			}
		}
		elseif ($count > 1) {
			$imgTitle	= $count.' '.Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_COMMENTS_COUNT_PLURAL');
			if ($config['show_comments_count'] == 1) {
				$href_text		= HTMLHelper::image( Uri::root().'media/com_sportsmanagement/jl_images/discuss_active.gif', $imgTitle, array(' title' => $imgTitle,' border' => 0,' style' => 'vertical-align: middle'));
			} elseif ($config['show_comments_count'] == 2) {
				$href_text		= '<span title="'. $imgTitle .'">('.$count.')</span>';
			}
		}
		else {
			$imgTitle	= Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_COMMENTS_COUNT_NOCOMMENT');
			if ($config['show_comments_count'] == 1) {
				$href_text		= HTMLHelper::image( Uri::root().'media/com_sportsmanagement/jl_images/discuss.gif', $imgTitle, array(' title' => $imgTitle,' border' => 0,' style' => 'vertical-align: middle'));
			} elseif ($config['show_comments_count'] == 2) {
				$href_text		= '<span title="'. $imgTitle .'">('.$count.')</span>';
			}
		}
		return $href_text;
	}
}

class sportsmanagementModelCommentsKunena extends sportsmanagementModelComments
{
	protected $sbItemid = 0;

	function __construct(&$config) {
		$database = JFactory::getDBO();

		$this->sbItemid = 0; //doesn't change much from one call to he other...
		if ( $this->sbItemid == 0 ) { //this way, we only trig this request once
			$database->setQuery("SELECT id FROM #__menu WHERE link LIKE 'index.php?option=com_kunena&view=home%'");
			$this->sbItemid = $database->loadResult();
		}
	}

    /**
	 * description see base class
	 */
	function isEnabled() {
		return true;
	}

    /**
	 * description see base class
	 */
	function showMatchCommentIcon(&$match, &$hometeam, &$guestteam, &$config, &$project)
	{
		// Keine HTML Tags in den Mannschaftsnamen
		$match_home = preg_replace("|<[^>]*>|", "", $hometeam->name);
		$match_away = preg_replace("|<[^>]*>|", "", $guestteam->name);
		$title = sprintf("%s - %s",addslashes($match_home),addslashes($match_away));

		$query = sprintf("SELECT id, posts FROM #__kunena_topics WHERE category_id = %s AND subject = '%s'",$project->sb_catid, $title);
		$database = JFactory::getDBO();
		$database->setQuery($query);
		$result = $database->loadAssoc();

		$title = sprintf("%s - %s",$match_home,$match_away);
		$count = 0;
		if (is_array($result)){
			$count = $result['posts'];
		}
		$href_text = parent::getHrefText($count, $config);

		if (!is_array($result)){
			$showlink = JRoute::_("index.php?option=com_kunena&view=topic&&catid=".$project->sb_catid."&Itemid=".$this->sbItemid."&layout=create&CommentMatchID=".$match->id);
		}
		else{
			$showlink = JRoute::_("index.php?option=com_kunena&view=topic&catid=".$project->sb_catid."&Itemid=".$this->sbItemid."&id=".$result['id']);
		}
		$viewComment	= HTMLHelper::link($showlink, $href_text);
		return $viewComment;
	}

    /**
	 * description see base class
	 */
	function showMatchComments(&$match, &$hometeam, &$guestteam, &$config, &$project)
	{
		// TODO: probably we can add an seamless preview her using the 
		// KunenaForum::display('topics', $layout, null, $this->params); method 
		// as done in kunenalatest module, but it wasn't a quick implementation

		if (!isset($config['show_comments_count']))
		{
			// for nextmatch we do not have show_comments_count configurable
			$config['show_comments_count'] = 2;
		}
		// no on-page preview available, provide the link to forum instead
		return $this->showMatchCommentIcon($match, $hometeam, $guestteam, $config, $project);
	}
}

class sportsmanagementModelCommentsJSMJComments extends sportsmanagementModelComments
{
	protected $separate_comments;
	protected $comJcomments;

	function __construct(&$config) {
		$this->comJcomments = true;
		$dispatcher = JDispatcher::getInstance();
		if(file_exists(JPATH_ROOT.'/components/com_jcomments/classes/config.php'))
		{
			require_once JPATH_ROOT.'/components/com_jcomments/classes/config.php';
			require_once JPATH_ROOT.'/components/com_jcomments/jcomments.class.php';
			require_once JPATH_ROOT.'/components/com_jcomments/models/jcomments.php';
		}
		else
		{
			$this->comJcomments = false;
		}

		/**
		 * load sportsmanagement comments plugin files
		 */
		PluginHelper::importPlugin('content','sportsmanagement_comments');

		/**
		 * get sportsmanagement comments plugin params
		 */
		$plugin = PluginHelper::getPlugin('content', 'sportsmanagement_comments');

		if (is_object($plugin)) {
			$pluginParams = new Registry($plugin->params);
		}
		else {
			$pluginParams = new Registry('');
		}
		$this->separate_comments 	= $pluginParams->get( 'separate_comments', 0 );
	}

    /**
	 * description see base class
	 */
	function isEnabled() {
		return ($this->comJcomments == true);
	}

    /**
	 * description see base class
	 */
	function showMatchCommentIcon(&$match, &$hometeam, &$guestteam, &$config, &$project)
	{
		if ($this->separate_comments) {
			// Comments integration trigger when separate_comments in plugin is set to yes/1
			if (isset($match->team1_result))
			{
				$joomleage_comments_object_group = 'com_sportsmanagement_matchreport';
			}
			else {
				$joomleage_comments_object_group = 'com_sportsmanagement_nextmatch';
			}
		}
		else {
			// Comments integration trigger when separate_comments in plugin is set to no/0
			$joomleage_comments_object_group = 'com_sportsmanagement';
		}

		$options 					= array();
		$options['object_id']		= (int) $match->id;
		$options['object_group']	= $joomleage_comments_object_group;
		$options['published']		= 1;
		$count = JCommentsModel::getCommentsCount($options);

		$href_text = parent::getHrefText($count, $config);

		//Link
		if (isset($match->team1_result))
		{
			$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database',0);
			$routeparameter['s'] = Factory::getApplication()->input->getInt('s',0);
			$routeparameter['p'] = $project->slug;
			$routeparameter['mid'] = $match->id;
			$link = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter);

		} else {
			$link=sportsmanagementHelperRoute::getNextMatchRoute($project->slug,$match->id).'#comments';
		}
		$viewComment	= HTMLHelper::link($link, $href_text);
		return $viewComment;
	}

    /**
	 * description see base class
	 */
	function showMatchComments(&$match, &$hometeam, &$guestteam, &$config, &$project)
	{
		$comments = array();
		if ($this->separate_comments) {
			// Comments integration trigger when separate_comments in plugin is set to yes/1
			Factory::getApplication()->triggerEvent( 'onMatchReportComments', array( $match, $hometeam->name .' - '. $guestteam->name, &$comments ));
		}
		else {
			// Comments integration trigger when separate_comments in plugin is set to no/0
			Factory::getApplication()->triggerEvent( 'onMatchComments', array( $match, $hometeam->name .' - '. $guestteam->name, &$comments ));
		}
		return $comments;
	}
}
?>
