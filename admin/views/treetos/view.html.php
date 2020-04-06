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
 * @subpackage treetos
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * sportsmanagementViewTreetos
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2016
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewTreetos extends sportsmanagementView
{

    /**
     * sportsmanagementViewTreetos::init()
     *
     * @return void
     */
    public function init()
    {
      
        $this->project_id = $this->app->getUserState("$this->option.pid", '0');
        $mdlProject = BaseDatabaseModel::getInstance('Project', 'sportsmanagementModel');
        $projectws = $mdlProject->getProject($this->project_id);
      
        $division = $this->app->getUserStateFromRequest($this->option.'tt_division', 'division', '', 'string');

        //build the html options for divisions
        $divisions[] = JHtmlSelect::option('0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_DIVISION'));
        $mdlDivisions = BaseDatabaseModel::getInstance("divisions", "sportsmanagementModel");
        if ($res = $mdlDivisions->getDivisions($this->project_id)) {
            $divisions = array_merge($divisions, $res);
        }
        $lists['divisions'] = $divisions;
        unset($divisions);
  
        //$this->user = $user;
        $this->lists = $lists;
        //$this->items = $items;
        $this->projectws = $projectws;
        $this->division = $division;
        //$this->total = $total;
        //$this->pagination = $pagination;
        //$this->request_url = $uri;
      
        //$this->setLayout('default');

        //$this->addToolbar();
        //		parent::display($tpl);
    }

    /**
     * sportsmanagementViewTreetos::addToolbar()
     *
     * @return void
     */
    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_TITLE'), 'Tree');

        ToolbarHelper::apply('treeto.saveshort');
        ToolbarHelper::publishList('treetos.publish');
        ToolbarHelper::unpublishList('treetos.unpublish');
        ToolbarHelper::divider();

        ToolbarHelper::addNew('treetos.save');
        ToolbarHelper::deleteList(Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_WARNING'), 'treeto.remove');
        ToolbarHelper::divider();
      
        parent::addToolbar();

      
    }
}
?>
