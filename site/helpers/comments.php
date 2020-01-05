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
	protected $separate_comments;
	protected $comJcomments;

	function __construct(&$config) {
		if(!ComponentHelper::isEnabled('com_jcomments', true))
		{
			$this->comJcomments = false;
			Log::add( Text::_('Die Komponente JComments ist nicht installiert'));
		}
		else
		{
			$this->comJcomments = true;
		}
		$dispatcher = JDispatcher::getInstance();
		if(file_exists(JPATH_ROOT.'/components/com_jcomments/classes/config.php'))
		{
			require_once JPATH_ROOT.'/components/com_jcomments/classes/config.php';
			require_once JPATH_ROOT.'/components/com_jcomments/jcomments.class.php';
			require_once JPATH_ROOT.'/components/com_jcomments/models/jcomments.php';
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
	 * get information, if comments are enabled and working
	 *
	 * @return bool
	 */
	function isEnabled() {
		return ($this->comJcomments == true);
	}

    /**
	 * display the configured match specific Commenticon
	 *
	 * @param object $match the match to check comments for
	 * @param object $config configuration of the parent screen / template
	 * @param object $project reference to parent project (used for slug)
	 * @return string
	 */
	function showMatchCommentIcon(&$match, &$config, &$project)
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
		$count = 0;
		if (class_exists('JCommentsModel'))
		{
			$count = JCommentsModel::getCommentsCount($options);
		}
		if ($count == 1) {
			$imgTitle		= $count.' '.Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_COMMENTS_COUNT_SINGULAR');
			if ($config['show_comments_count'] == 1) {
				$href_text		= HTMLHelper::image( Uri::root().'media/com_sportsmanagement/jl_images/discuss_active.gif', $imgTitle, array(' title' => $imgTitle,' border' => 0,' style' => 'vertical-align: middle'));
			} elseif ($config['show_comments_count'] == 2) {
				$href_text		= '<span title="'. $imgTitle .'">('.$count.')</span>';
			}
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
		}
		elseif ($count > 1) {
			$imgTitle	= $count.' '.Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_COMMENTS_COUNT_PLURAL');
			if ($config['show_comments_count'] == 1) {
				$href_text		= HTMLHelper::image( Uri::root().'media/com_sportsmanagement/jl_images/discuss_active.gif', $imgTitle, array(' title' => $imgTitle,' border' => 0,' style' => 'vertical-align: middle'));
			} elseif ($config['show_comments_count'] == 2) {
				$href_text		= '<span title="'. $imgTitle .'">('.$count.')</span>';
			}
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
		}
		else {
			$imgTitle	= Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_COMMENTS_COUNT_NOCOMMENT');
			if ($config['show_comments_count'] == 1) {
				$href_text		= HTMLHelper::image( Uri::root().'media/com_sportsmanagement/jl_images/discuss.gif', $imgTitle, array(' title' => $imgTitle,' border' => 0,' style' => 'vertical-align: middle'));
			} elseif ($config['show_comments_count'] == 2) {
				$href_text		= '<span title="'. $imgTitle .'">('.$count.')</span>';
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
		}
		return $viewComment;
	}

    /**
	 * display the configured match specific Commenticon
	 *
	 * @param object $match the match to check comments for
	 * @param object $team1 team 1 of match
	 * @param object $team2 team 2 of match
	 * @return array
	 */
	function showMatchComments(&$match, &$team1, &$team2)
	{
		$comments = array();
		if ($this->separate_comments) {
			// Comments integration trigger when separate_comments in plugin is set to yes/1
			Factory::getApplication()->triggerEvent( 'onMatchReportComments', array( $match, $team1->name .' - '. $team2->name, &$comments ));
		}
		else {
			// Comments integration trigger when separate_comments in plugin is set to no/0
			Factory::getApplication()->triggerEvent( 'onMatchComments', array( $match, $team1->name .' - '. $team2->name, &$comments ));
		}
		return $comments;
	}
}
?>
