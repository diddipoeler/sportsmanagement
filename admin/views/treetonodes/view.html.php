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
 * @subpackage treetonodes
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * sportsmanagementViewTreetonodes
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2018
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewTreetonodes extends sportsmanagementView
{

    /**
     * sportsmanagementViewTreetonodes::init()
     *
     * @return
     */
    public function init()
    {
      
        if ($this->getLayout()=='default' || $this->getLayout()=='default_3' || $this->getLayout()=='default_4' ) {
            $this->_displayDefault();
            return;
        }

    }

    /**
     * sportsmanagementViewTreetonodes::_displayDefault()
     *
     * @return void
     */
    function _displayDefault()
    {
        $this->node = $this->items;
        $this->project_id = $this->app->getUserState("$this->option.pid", '0');
        $mdlProject = BaseDatabaseModel::getInstance('Project', 'sportsmanagementModel');
        $projectws = $mdlProject->getProject($this->project_id);
        $mdltreeto = BaseDatabaseModel::getInstance('treeto', 'sportsmanagementModel');
        $treetows = $mdltreeto->getTreeToData($this->jinput->get('tid'));

        //build the html options for teams
        $team_id[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TEAMS_LEGEND'));
        if ($projectteams = $this->model->getProjectTeamsOptions() ) {
            $team_id = array_merge($team_id, $projectteams);
        }
        $lists['team'] = $team_id;
        unset($team_id);
      


        $style  = 'style="background-color: #dddddd; ';
        $style .= 'border: 0px solid white;';
        $style .= 'font-weight: normal; ';
        $style .= 'font-size: 8pt; ';
        $style .= 'width: 150px; ';
        $style .= 'font-family: verdana; ';
        $style .= 'text-align: center;"';
        $path = 'media/com_sportsmanagement/treebracket/onwhite/';
      
        // build the html radio for adding into new round / exist round
        $createYesNo = array(0 => Text::_('JNO'),1 => Text::_('JYES'));
        $createLeftRight = array(0 => Text::_('L'),1 => Text::_('R'));
        $ynOptions = array();
        $lrOptions = array();
        foreach($createYesNo AS $key => $value){$ynOptions[]=JHtmlSelect::option($key, $value);
        }
        foreach($createLeftRight AS $key => $value){$lrOptions[]=JHtmlSelect::option($key, $value);
        }
        $lists['addToRound'] = JHtmlSelect::radiolist($ynOptions, 'addToRound', 'class="inputbox"', 'value', 'text', 1);

        // build the html radio for auto publish new matches
        $lists['autoPublish'] = JHtmlSelect::radiolist($ynOptions, 'autoPublish', 'class="inputbox"', 'value', 'text', 0);

        // build the html radio for Left or Right redepth
        $lists['LRreDepth'] = JHtmlSelect::radiolist($lrOptions, 'LRreDepth', 'class="inputbox"', 'value', 'text', 0);
        // build the html radio for create new treeto
        $lists['createNewTreeto'] = JHtmlSelect::radiolist($ynOptions, 'createNewTreeto', 'class="inputbox"', 'value', 'text', 1);

        $this->lists = $lists;
        $this->style = $style;
        $this->path = $path;
        $this->projectws = $projectws;
        $this->treetows = $treetows;
        $this->matches = $this->model->getteamsprorunde($this->project_id, $this->treetows);
        //$this->app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . '<pre>'.print_r($this->node,true).'</pre>'  , '');		
        foreach($this->node as $key => $value)
        {
             $value->team_id = $this->matches[$value->node]->team_id;
             $value->team_name = $this->matches[$value->node]->team_name;
             $value->title = $this->matches[$value->node]->team_name;
             $value->content = $this->matches[$value->node]->team_name;  
             $value->match_id = $this->matches[$value->node]->match_id;
             $value->roundcode = $this->matches[$value->node]->roundcode;  
        }
        $this->model->savenode($this->node);
      
    }
  
  
        /**
         * sportsmanagementViewTreetonodes::addToolBar()
         *
         * @return void
         */
    protected function addToolBar()
    {
        // $istree = $this->treetows->tree_i;
        //$isleafed = $this->treetows->leafed;
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_TITLE');
        switch ($this->treetows->leafed)
        {
        case 1:
            ToolbarHelper::apply('treetonode.saveshort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_SAVE_APPLY'), false);
            ToolbarHelper::custom('treetonode.removenode', 'delete.png', 'delete_f2.png', Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_DELETE_ALL'), false);
            break;
        case 2:
            ToolbarHelper::apply('treetonode.saveallleaf', Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_TEST_SHOW'), false);
            ToolbarHelper::custom('treetonode.removenode', 'delete.png', 'delete_f2.png', Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_DELETE'), false);
            break;
        case 3:
            ToolbarHelper::apply('treetonode.savefinishleaf', Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_SAVE_LEAF'), false);
            ToolbarHelper::custom('treetonode.removenode', 'delete.png', 'delete_f2.png', Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_DELETE'), false);
            break;
      
        }
        parent::addToolbar();
     
    }

}
?>
