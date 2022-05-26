<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_sports_type_statistics
 * @file       mod_sportsmanagement_sports_type_statistics.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;

/**
 * modJSMSportsHelper
 *
 * @package
 * @author    abcde
 * @copyright 2015
 * @version   $Id$
 * @access    public
 */
class modJSMSportsHelper
{

	/**
	 * modJSMSportsHelper::getData()
	 *
	 * @param   mixed  $params
	 *
	 * @return array
	 */
	public static function getData(&$params)
	{
	   $app    = Factory::getApplication();
       $data_array = array();
       
       //$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' params<pre>' . print_r($params, true) . '</pre>', 'Error');
       
		if (!class_exists('sportsmanagementModelSportsTypes'))
		{
			JLoader::import('components.com_sportsmanagement.models.sportstypes', JPATH_ADMINISTRATOR);
		}

		$model = BaseDatabaseModel::getInstance('SportsTypes', 'sportsmanagementModel');
        
        $data_array['sportstype'] = $model->getSportsTypes();
        
        if( $params['show_project'] )
        {
            $data_array['projectscount'] = $model->getProjectsCount($params->get('sportstypes'));
        }
        if( $params['show_leagues'] )
        {
            $data_array['leaguescount'] = $model->getLeaguesCount($params->get('sportstypes'));
        }
        if( $params['show_seasons'] )
        {
            $data_array['seasonscount'] = $model->getSeasonsCount($params->get('sportstypes'));
        }
        if( $params['show_playgrounds'] )
        {
            $data_array['playgroundscount'] = $model->getPlaygroundsOnlyCount($params->get('sportstypes'));
        }
        if( $params['show_clubs'] )
        {
            $data_array['clubscount'] = $model->getClubsOnlyCount($params->get('sportstypes'));
        }
        if( $params['show_teams'] )
        {
            $data_array['projectteamscount'] = $model->getProjectTeamsCount($params->get('sportstypes'));
        }
        if( $params['show_players'] )
        {
            $data_array['personscount'] = $model->getPersonsOnlyCount($params->get('sportstypes'));
        }
        if( $params['show_divisions'] )
        {
            $data_array['projectdivisionscount'] = $model->getProjectDivisionsCount($params->get('sportstypes'));
        }
        if( $params['show_rounds'] )
        {
            $data_array['projectroundscount'] = $model->getProjectRoundsCount($params->get('sportstypes'));
        }
        if( $params['show_matches'] )
        {
            $data_array['projectmatchescount'] = $model->getProjectMatchesCount($params->get('sportstypes'));
        }
        if( $params['show_player_events'] )
        {
            $data_array['projectmatcheseventscount'] = $model->getProjectMatchesEventsCount($params->get('sportstypes'));
        }
        if( $params['show_player_stats'] )
        {
            $data_array['projectmatchesstatscount'] = $model->getProjectMatchesStatsCount($params->get('sportstypes'));
        }
        
        
        
        

/*
		return array(
		             'projectteamsplayerscount'  => $model->getProjectTeamsPlayersCount($params->get('sportstypes')),
		);
*/        
        //$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' data<pre>' . print_r($data_array, true) . '</pre>', 'Error');
        return $data_array;
        
	}
}
