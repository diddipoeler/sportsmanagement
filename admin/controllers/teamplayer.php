<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage controllers
 * @file       teamplayer.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * sportsmanagementControllerteamplayer
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllerteamplayer extends JSMControllerForm
{

   /**
	 * sportsmanagementControllerteamplayer::__construct()
	 *
	 * @param   mixed  $config
	 *
	 * @return void
	 */
	function __construct($config = array())
	{

if ( Factory::getConfig()->get('debug') )
{        
Factory::getApplication()->enqueueMessage(Text::_(__METHOD__ . ' ' . ' ' . __LINE__ . ' ' . 'project_team_id<pre>'.print_r(Factory::getApplication()->input->getInt('project_team_id'),true).'</pre>'), 'notice');
Factory::getApplication()->enqueueMessage(Text::_(__METHOD__ . ' ' . ' ' . __LINE__ . ' ' . 'season_team_id<pre>'.print_r(Factory::getApplication()->input->getInt('season_team_id'),true).'</pre>'), 'notice');
Factory::getApplication()->enqueueMessage(Text::_(__METHOD__ . ' ' . ' ' . __LINE__ . ' ' . 'season_id<pre>'.print_r(Factory::getApplication()->input->getInt('season_id'),true).'</pre>'), 'notice');
Factory::getApplication()->enqueueMessage(Text::_(__METHOD__ . ' ' . ' ' . __LINE__ . ' ' . 'person_id<pre>'.print_r(Factory::getApplication()->input->getInt('person_id'),true).'</pre>'), 'notice');
Factory::getApplication()->enqueueMessage(Text::_(__METHOD__ . ' ' . ' ' . __LINE__ . ' ' . 'id<pre>'.print_r(Factory::getApplication()->input->getInt('id'),true).'</pre>'), 'notice');
Factory::getApplication()->enqueueMessage(Text::_(__METHOD__ . ' ' . ' ' . __LINE__ . ' ' . 'pid<pre>'.print_r(Factory::getApplication()->input->getInt('pid'),true).'</pre>'), 'notice');
Factory::getApplication()->enqueueMessage(Text::_(__METHOD__ . ' ' . ' ' . __LINE__ . ' ' . 'team_id<pre>'.print_r(Factory::getApplication()->input->getInt('team_id'),true).'</pre>'), 'notice');
Factory::getApplication()->enqueueMessage(Text::_(__METHOD__ . ' ' . ' ' . __LINE__ . ' ' . 'persontype<pre>'.print_r(Factory::getApplication()->input->getInt('persontype'),true).'</pre>'), 'notice');
}

Factory::getApplication()->setUserState("teamplayer.project_team_id", Factory::getApplication()->input->getInt('project_team_id'));
Factory::getApplication()->setUserState("teamplayer.season_team_id", Factory::getApplication()->input->getInt('season_team_id'));
Factory::getApplication()->setUserState("teamplayer.season_id", Factory::getApplication()->input->getInt('season_id'));
Factory::getApplication()->setUserState("teamplayer.person_id", Factory::getApplication()->input->getInt('person_id'));
Factory::getApplication()->setUserState("teamplayer.id", Factory::getApplication()->input->getInt('id'));
Factory::getApplication()->setUserState("teamplayer.pid", Factory::getApplication()->input->getInt('pid'));
Factory::getApplication()->setUserState("teamplayer.team_id", Factory::getApplication()->input->getInt('team_id'));
Factory::getApplication()->setUserState("teamplayer.persontype", Factory::getApplication()->input->getInt('persontype'));


       
		parent::__construct($config);
	}

}
