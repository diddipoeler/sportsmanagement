<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage allprojectrounds
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

/**
 * sportsmanagementViewallprojectrounds
 *
 * @package 
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewallprojectrounds extends sportsmanagementView
{

    /**
     * sportsmanagementViewallprojectrounds::display()
     *
     * @param  mixed $tpl
     * @return
     */
    function init()
    {

        // Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        // Get a refrence of the page instance in joomla
        $document = Factory::getDocument();
        if (version_compare(JSM_JVERSION, '4', 'eq')) {
            $uri = Uri::getInstance();
        } else {
            $uri = Factory::getURI();
        }
        $model = $this->getModel();

        //$this->tableclass = $jinput->getVar('table_class', 'table','request','string');
        $this->tableclass = $jinput->request->get('table_class', 'table', 'STR');
        $option = $jinput->getCmd('option');
        $starttime = microtime();

        $project = sportsmanagementModelProject::getProject();

        $this->project = $project;
        $this->projectid = $this->project->id;
        $this->projectmatches = $model->getProjectMatches();
        $this->rounds = sportsmanagementModelProject::getRounds();
        $this->overallconfig = sportsmanagementModelProject::getOverallConfig();
        $this->config = array_merge($this->overallconfig, $model->_params);
        $this->favteams = sportsmanagementModelProject::getFavTeams($this->projectid);
        $this->projectteamid = $model->getProjectTeamID($this->favteams);
        $this->content = $model->getRoundsColumn($this->rounds, $this->config);
        $this->headertitle = Text::sprintf('COM_SPORTSMANAGEMENT_RESULTS_ROUND_RESULTS2', $this->project->name);
    }

}

?>
