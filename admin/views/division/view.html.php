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
 * @subpackage division
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementViewDivision
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewDivision extends sportsmanagementView
{
    
    /**
     * sportsmanagementViewDivision::init()
     * 
     * @return
     */
    public function init()
    {
        
        $mdlProject = BaseDatabaseModel::getInstance("Project", "sportsmanagementModel");
        $project = $mdlProject->getProject($this->project_id);
        $this->project = $project;
        $count_teams = $this->model->count_teams_division($this->item->id);
        $extended = sportsmanagementHelper::getExtended($this->item->rankingparams, 'division');
        $this->extended = $extended;
        $this->extended->setFieldAttribute('rankingparams', 'rankingteams', $count_teams);

    }

    
    /**
    * Add the page title and toolbar.
    *
    * @since 1.7
    */
    protected function addToolbar()
    {    
        $this->jinput->set('hidemainmenu', true);
        $this->jinput->set('pid', $this->project_id);
          $isNew = $this->item->id ? $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DIVISIONS_EDIT') : $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DIVISIONS_NEW');
          $this->icon = 'division';
          parent::addToolbar();
    }    
    
    

}
?>
