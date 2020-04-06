<?php 
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage rivals
 */
 
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * sportsmanagementViewRivals
 * 
 * @package 
 * @author    Dieter Plöger
 * @copyright 2016
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewRivals extends sportsmanagementView
{
    
    /**
     * sportsmanagementViewRivals::init()
     * 
     * @return void
     */
    function init()
    {
        $this->document->addScript(Uri::root(true).'/components/'. $this->option .'/assets/js/smsportsmanagement.js');
    
        //$this->project = sportsmanagementModelProject::getProject();
        //$this->overallconfig = sportsmanagementModelProject::getOverallConfig();
        
        if (!isset($this->overallconfig['seperator'])) {
            $this->overallconfig['seperator'] = "-";
        }
        //$this->config = $config;
        $this->opos = $this->model->getOpponents();
        $this->team = $this->model->getTeam();
       
        // Set page title
        $titleInfo = sportsmanagementHelper::createTitleInfo(Text::_('COM_SPORTSMANAGEMENT_RIVALS_PAGE_TITLE'));
        if (!empty($this->team)) {
            $titleInfo->team1Name = $this->team->name;
        }
        if (!empty($this->project)) {
            $titleInfo->projectName = $this->project->name;
            $titleInfo->leagueName = $this->project->league_name;
            $titleInfo->seasonName = $this->project->season_name;
        }
        if (!empty($this->division) && $this->division->id != 0) {
            $titleInfo->divisionName = $this->division->name;
        }
                        
        $this->pagetitle = sportsmanagementHelper::formatTitle($titleInfo, $this->config["page_title_format"]);
        $this->document->setTitle($this->pagetitle);
        
        $this->headertitle = $this->pagetitle;

    }
}
