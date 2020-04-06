<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage currentseasons
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementViewCurrentseasons
 * 
 * @package 
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewCurrentseasons extends sportsmanagementView
{
    
    /**
     * sportsmanagementViewCurrentseasons::init()
     * 
     * @return void
     */
    public function init()
    {
       
        if ($this->items ) {
            foreach ($this->items as $item)
            {
                $item->count_projectdivisions = 0;
                $mdlProjectDivisions = BaseDatabaseModel::getInstance("divisions", "sportsmanagementModel");
                $item->count_projectdivisions = $mdlProjectDivisions->getProjectDivisionsCount($item->id);
        
                $item->count_projectpositions = 0;
                $mdlProjectPositions = BaseDatabaseModel::getInstance("Projectpositions", "sportsmanagementModel");
                $item->count_projectpositions = $mdlProjectPositions->getProjectPositionsCount($item->id);
        
                $item->count_projectreferees = 0;
                $mdlProjectReferees = BaseDatabaseModel::getInstance("Projectreferees", "sportsmanagementModel");
                $item->count_projectreferees = $mdlProjectReferees->getProjectRefereesCount($item->id);
        
                $item->count_projectteams = 0;
                $mdlProjecteams = BaseDatabaseModel::getInstance("Projectteams", "sportsmanagementModel");
                $item->count_projectteams = $mdlProjecteams->getProjectTeamsCount($item->id);
        
                $item->count_matchdays = 0;
                $mdlRounds = BaseDatabaseModel::getInstance("Rounds", "sportsmanagementModel");
                $item->count_matchdays = $mdlRounds->getRoundsCount($item->id);
       
            }
        }
 
    }

    /**
    * Add the page title and toolbar.
    *
    * @since 1.6
    */
    protected function addToolbar()
    {

        // Set toolbar items for the page
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_TITLE');
        $this->icon = 'currentseason';

        
        parent::addToolbar();
    }
}
?>
