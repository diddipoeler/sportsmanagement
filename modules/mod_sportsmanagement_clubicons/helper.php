<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       helper.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage mod_sportsmanagement_clubicons
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

/**
 * modJSMClubiconsHelper
 *
 * @package 
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class modJSMClubiconsHelper
{
    var $project;
    var $ranking;
    var $teams = array ();
    var $params;
    var $module;
    var $placeholders = array (
    'logo_small' => 'images/com_sportsmanagement/database/placeholders/placeholder_small.png',
    'logo_middle' => 'images/com_sportsmanagement/database/placeholders/placeholder_50.png',
    'logo_big' => 'images/com_sportsmanagement/database/placeholders/placeholder_150.png',
            'projectteam_picture' => 'images/com_sportsmanagement/database/placeholders/placeholder_450_2.png',
    'team_picture' => 'images/com_sportsmanagement/database/placeholders/placeholder_450_2.png'
    );
    /**
     * modJSMClubiconsHelper::__construct()
     *
     * @param  mixed $params
     * @return
     */
    function __construct( &$params,$module )
    {
        $this->params = $params;
        $this->module = $module;
        self::_getData();
    }
  
    /**
     * modJSMClubiconsHelper::_getData()
     *
     * @return
     */
    private function _getData()
    {
        $app = Factory::getApplication();

        $project_id = ($app->input->getVar('option', '') == 'com_sportsmanagement' AND
                                    $app->input->getInt('p', 0) > 0 AND
                                    $this->params->get('usepfromcomponent', 0) == 1 ) ?
                                    $app->input->getInt('p') : $this->params->get('project_ids');
        if (is_array($project_id)) { $project_id = $project_id[0];
        }
      
        if ($project_id ) {
            //sportsmanagementModelProject::setProjectId($project_id);
            sportsmanagementModelProject::$projectid = $project_id;
            sportsmanagementModelProject::$cfg_which_database = $this->params->get('cfg_which_database');
            $this->project = sportsmanagementModelProject::getProject($this->params->get('cfg_which_database'));

            $ranking = JSMRanking::getInstance($this->project, $this->params->get('cfg_which_database'));
            sportsmanagementModelRanking::$projectid = $project_id;
            $divisionid = explode(':', $this->params->get('division_id', 0));
            $divisionid = $divisionid[0];
            $this->ranking = $ranking->getRanking(null, null, $divisionid, $this->params->get('cfg_which_database'));

            if ($this->params->get('logotype') == 'logo_small') {
                       $teams = sportsmanagementModelProject::getTeamsIndexedByPtid($divisionid, 'name', $this->params->get('cfg_which_database'));
            }
            else //get the teams cause we don't have logo_middle and big in ranking model's getTeams:
            {
                       $teams = sportsmanagementModelProject::getTeams($divisionid, 'name', $this->params->get('cfg_which_database'));
            }
      
      
            self::buildData($teams);
            unset($teams);
            //unset($model);
        }

    }
  
    /**
     * modJSMClubiconsHelper::buildData()
     *
     * @param  mixed $result
     * @return
     */
    function buildData( &$result )
    {
          $app = Factory::getApplication();
      
        if (count($result)) {
            foreach($result as $r)
            {
                $this->teams[$r->projectteamid] = array();
                $this->teams[$r->projectteamid]['link'] = self::getLink($r);
                $class = (!empty($this->teams[$r->projectteamid]['link'])) ? 'img-zoom' : 'img-zoom';
                $this->teams[$r->projectteamid]['logo'] = self::getLogo($r, $class);
            }
        }
    }
  
    /**
     * modJSMClubiconsHelper::getLogo()
     *
     * @param  mixed $item
     * @param  mixed $class
     * @return
     */
    function getLogo( & $item, $class )
    {
          $app = Factory::getApplication();
      
        $imgtype = $this->params->get('logotype', 'logo_middle');
        $logourl = $item->$imgtype;
      
        if (!$logourl ) {
             $logourl = $this->placeholders[$imgtype];  
        }
      
        $cfg_which_database = $this->params->get('cfg_which_database');
      
        if ($cfg_which_database ) {
             $paramscomponent = ComponentHelper::getParams('com_sportsmanagement');  
             $logourl = $paramscomponent->get('cfg_which_database_server').$logourl;
        }

     
        $imgtitle = Text::_('View ') . $item->name;
        return HTMLHelper::image($logourl, $item->name, 'border="0" width="'.$this->params->get('jcclubiconsglobalmaxwidth').'" class="'.$class.'" title="'.$imgtitle.'"');
    }
  
    /**
     * modJSMClubiconsHelper::getLink()
     *
     * @param  mixed $item
     * @return
     */
    function getLink( &$item )
    {
          $app = Factory::getApplication();
      
        $routeparameter = array();
        $routeparameter['cfg_which_database'] = $this->params->get('cfg_which_database');
        $routeparameter['s'] = $this->params->get('s');
        $routeparameter['p'] = $this->project->slug;
        switch ($this->params->get('teamlink'))
        {
        case 0:
            return '';
        case 1:
            $routeparameter['tid'] = $item->team_slug;
            $routeparameter['ptid'] = 0;
            return sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo', $routeparameter);
        case 2:
            $routeparameter['tid'] = $item->team_slug;
            $routeparameter['ptid'] = 0;
            return sportsmanagementHelperRoute::getSportsmanagementRoute('roster', $routeparameter);
        case 3:
            $routeparameter['tid'] = $item->team_slug;
            $routeparameter['division'] = 0;
            $routeparameter['mode'] = 0;
            $routeparameter['ptid'] = 0;
            return sportsmanagementHelperRoute::getSportsmanagementRoute('teamplan', $routeparameter);
        case 4:
            return sportsmanagementHelperRoute::getClubInfoRoute($this->project->slug, $item->club_slug);
        case 5:
            return (isset($item->club_www)) ? $item->club_www : $item->website;
        }
    }
}
