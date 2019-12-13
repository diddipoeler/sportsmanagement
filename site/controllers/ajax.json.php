<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      ajax.json.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage 
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;


/**
 * sportsmanagementControllerAjax
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllerAjax extends BaseController {

    /**
     * sportsmanagementControllerAjax::__construct()
     * 
     * @return
     */
    public function __construct() {
        // Get the document object.
        $document = Factory::getDocument();
        // Set the MIME type for JSON output.
        $document->setMimeEncoding('application/json');
        parent::__construct();
    }


/**
 * sportsmanagementControllerAjax::getLink()
 * 
 * @return void
 */
public function getLink()
	{
	$app = Factory::getApplication();
    // Get the document object.
        $document = Factory::getDocument();
        // JInput object
        $jinput = $app->input; 
//        $project_id = $jinput->getInt('project_id');
$linktext = $jinput->getVar('linktext');
$document->addScriptOptions('linktext', $linktext);
        $model = $this->getModel('ajax');
        $res['linktext'] = $linktext;
        $res['link'] = $model->getLink($jinput->getVar('view'),$jinput->getInt('project_id'));
        echo json_encode($res);
        $app->close();   
       
}

       
/**
 * sportsmanagementControllerAjax::getProjectTeams()
 * 
 * @return void
 */
public function getProjectTeams()
	{
 $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input; 
        $project_id = $jinput->getInt('project_id');
        $model = $this->getModel('ajax');
        $res = $model->getProjectTeams($project_id);
        echo json_encode($res);
        $app->close();
	}
    
/**
 * sportsmanagementControllerAjax::getProjectSelect()
 * 
 * @return void
 */
public function getProjectSelect()
	{
 $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input; 
        $league_id = $jinput->getInt('league_id');
        $model = $this->getModel('ajax');
        $res = $model->getProjectSelect($league_id);
        echo json_encode($res);
        $app->close();
	}
    
    
/**
 * sportsmanagementControllerAjax::getAssocLeagueSelect()
 * 
 * @return void
 */
public function getAssocLeagueSelect()
	{
 $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input; 
        $assoc_id = $jinput->getInt('assoc_id');
        $country = $jinput->getVar('country');
        $model = $this->getModel('ajax');
        $res = $model->getAssocLeagueSelect($country,$assoc_id);
        echo json_encode($res);
        $app->close();
}

/**
 * sportsmanagementControllerAjax::getCountrySubSubAssocSelect()
 * 
 * @return void
 */
public function getCountrySubSubAssocSelect()
{
   $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input; 
        $subassoc_id = $jinput->getInt('subassoc_id');
        $model = $this->getModel('ajax');
        $res = $model->getCountrySubSubAssocSelect($subassoc_id);
        echo json_encode($res);
        $app->close();
        
}

/**
 * sportsmanagementControllerAjax::getCountrySubAssocSelect()
 * 
 * @return void
 */
public function getCountrySubAssocSelect()
{
   $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input; 
        $assoc_id = $jinput->getInt('assoc_id');
        $model = $this->getModel('ajax');
        $res = $model->getCountrySubAssocSelect($assoc_id);
        echo json_encode($res);
        $app->close();
        
}

/**
 * sportsmanagementControllerAjax::getcountryassoc()
 * 
 * @return void
 */
public function getcountryassoc()
{
   $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input; 
        $country = $jinput->getVar('country');
        $model = $this->getModel('ajax');
        $res = $model->getCountryAssocSelect($country);
        echo json_encode($res);
        $app->close();
        
}
    /**
     * sportsmanagementControllerAjax::getprojectsoptions()
     * 
     * @return
     */
    public function getprojectsoptions() {
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $season = $jinput->getInt('s');
        $league = $jinput->getInt('l');
        $ordering = $jinput->getInt('o');

        $model = $this->getModel('ajax');

        $res = $model->getProjectsOptions($season, $league, $ordering);

        echo json_encode($res);

        $app->close();
    }

    /**
     * sportsmanagementControllerAjax::getroute()
     * 
     * @return
     */
    public function getroute() {
        $view = Factory::getApplication()->input->getCmd('view');

        switch ($view) {
            case "matrix":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
                $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
                $routeparameter['p'] = Factory::getApplication()->input->getVar('p');
                $routeparameter['division'] = Factory::getApplication()->input->getVar('division');
                $routeparameter['r'] = Factory::getApplication()->input->getVar('r');
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('matrix', $routeparameter);
                break;

            case "teaminfo":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
                $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
                $routeparameter['p'] = Factory::getApplication()->input->getVar('p');
                $routeparameter['tid'] = Factory::getApplication()->input->getVar('tid');
                $routeparameter['ptid'] = 0;
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo', $routeparameter);
                break;

            case "referees":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
                $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
                $routeparameter['p'] = Factory::getApplication()->input->getVar('p');
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('referees', $routeparameter);
                break;

            case "results":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
                $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
                $routeparameter['p'] = Factory::getApplication()->input->getVar('p');
                $routeparameter['r'] = Factory::getApplication()->input->getVar('r');
                $routeparameter['division'] = Factory::getApplication()->input->getVar('division');
                $routeparameter['mode'] = 0;
                $routeparameter['order'] = '';
                $routeparameter['layout'] = '';
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('results', $routeparameter);
                break;

            case "resultsranking":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
                $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
                $routeparameter['p'] = Factory::getApplication()->input->getVar('p');
                $routeparameter['r'] = 0;
                $routeparameter['division'] = 0;
                $routeparameter['mode'] = 0;
                $routeparameter['order'] = '';
                $routeparameter['layout'] = '';
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('results', $routeparameter);
                break;

            case "rankingmatrix":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
                $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
                $routeparameter['p'] = Factory::getApplication()->input->getVar('p');
                $routeparameter['type'] = 0;
                $routeparameter['r'] = Factory::getApplication()->input->getVar('r');
                $routeparameter['from'] = 0;
                $routeparameter['to'] = 0;
                $routeparameter['division'] = Factory::getApplication()->input->getVar('division');
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('rankingmatrix', $routeparameter);
                break;

            case "resultsrankingmatrix":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
                $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
                $routeparameter['p'] = Factory::getApplication()->input->getVar('p');
                $routeparameter['r'] = Factory::getApplication()->input->getVar('r');
                $routeparameter['division'] = Factory::getApplication()->input->getVar('division');
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('resultsrankingmatrix', $routeparameter);
                break;

            case "teamplan":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
                $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
                $routeparameter['p'] = Factory::getApplication()->input->getVar('p');
                $routeparameter['tid'] = Factory::getApplication()->input->getVar('tid');
                $routeparameter['division'] = Factory::getApplication()->input->getVar('division');
                $routeparameter['mode'] = 0;
                $routeparameter['ptid'] = 0;
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('teamplan', $routeparameter);
                break;

            case "roster":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
                $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
                $routeparameter['p'] = Factory::getApplication()->input->getVar('p');
                $routeparameter['tid'] = Factory::getApplication()->input->getVar('tid');
                $routeparameter['ptid'] = 0;
                $routeparameter['division'] = Factory::getApplication()->input->getVar('division');
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('roster', $routeparameter);
                break;

            case "eventsranking":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
                $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
                $routeparameter['p'] = Factory::getApplication()->input->getVar('p');
                $routeparameter['division'] = Factory::getApplication()->input->getVar('division');
                $routeparameter['tid'] = Factory::getApplication()->input->getVar('tid');
                $routeparameter['evid'] = 0;
                $routeparameter['mid'] = 0;
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('eventsranking', $routeparameter);
                break;

            case "curve":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
                $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
                $routeparameter['p'] = Factory::getApplication()->input->getVar('p');
                $routeparameter['tid1'] = Factory::getApplication()->input->getVar('tid');
                $routeparameter['tid2'] = 0;
                $routeparameter['division'] = Factory::getApplication()->input->getVar('division');
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('curve', $routeparameter);
                break;

            case "statsranking":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
                $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
                $routeparameter['p'] = Factory::getApplication()->input->getVar('p');
                $routeparameter['division'] = Factory::getApplication()->input->getVar('division');
                $routeparameter['tid'] = 0;
                $routeparameter['sid'] = 0;
                $routeparameter['order'] = '';
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('statsranking', $routeparameter);
                break;

            default:
            case "ranking":
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
                $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
                $routeparameter['p'] = Factory::getApplication()->input->getVar('p');
                $routeparameter['type'] = 0;
                $routeparameter['r'] = Factory::getApplication()->input->getVar('r');
                $routeparameter['from'] = 0;
                $routeparameter['to'] = 0;
                $routeparameter['division'] = Factory::getApplication()->input->getVar('division');
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('ranking', $routeparameter);
                break;
        }

        echo json_encode($link);
    }

}
