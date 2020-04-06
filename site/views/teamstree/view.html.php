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
 * @subpackage teamstree
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementViewTeamsTree
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2018
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewTeamsTree extends sportsmanagementView
{

    /**
     * sportsmanagementViewTeamsTree::init()
     *
     * @return void
     */
    function init()
    {

        $this->teams = sportsmanagementModelProject::getTeams($this->jinput->getInt("division", 0), 'name', $this->jinput->getInt('cfg_which_database', 0));

        foreach( $this->teams as $rowclub )
        {
 
             $mdlClubInfo = BaseDatabaseModel::getInstance("ClubInfo", "sportsmanagementModel");
             $mdlClubInfo::$tree_fusion = '';
             $mdlClubInfo::$historyhtmltree = '';
             $mdlClubInfo::$first_club_id = 0;
             $tree_club_id = $rowclub->club_id;

             $this->findclub[$rowclub->club_id] = $rowclub->club_id;  
             /**
 * ist das die erste club_id in der kette des stammbaumes ?
 */
            if ($rowclub->new_club_id ) {
                $this->firstclubid = $mdlClubInfo::getFirstClubId($rowclub->club_id, $rowclub->new_club_id);
                $firstclubid = $mdlClubInfo::$first_club_id;
                $tree_club_id = $firstclubid;
                $mdlClubInfo::$clubid = $rowclub->club_id;   
            }

             $this->clubhistory = $mdlClubInfo::getClubHistory($tree_club_id);
             $this->clubhistoryhtml = $mdlClubInfo::getClubHistoryHTML($tree_club_id);
             $this->clubhistoryfamilytree = $mdlClubInfo::fbTreeRecurse($tree_club_id, '', array (), $mdlClubInfo::$tree_fusion, 10, 0, 1);
             $this->genfamilytree = $mdlClubInfo::generateTree($tree_club_id, $this->config['show_bootstrap_tree']);
             $this->familytree = $mdlClubInfo::$historyhtmltree;

            if (!array_key_exists($tree_club_id, $this->familyteamstree) ) {
                $this->familyteamstree[$tree_club_id] = $this->familytree;
            }
  
            if ($tree_club_id ) {
                $firstrowclub = $mdlClubInfo::getFirstClub($tree_club_id);  
                $firstrowclub->club_name = $firstrowclub->name;  
                $this->familyclub[$tree_club_id] = $firstrowclub;  
                //$this->familyclub[$tree_club_id] = $rowclub;		
            }  
            else
             {  
                $this->familyclub[$tree_club_id] = $rowclub;
            }
  
        }

        if ($this->config['show_bootstrap_tree'] ) {  
               $this->document->addStyleSheet(Uri::base().'components/'.$this->option.'/assets/css/bootstrap-familytree.css');
        }
        else
        {
            $javascript = "\n";  
            $javascript .= "
jQuery(function ($) {
    $('.tree li:has(ul)').addClass('parent_li').find(' > span').attr('title', 'Collapse this branch');
    $('.tree li.parent_li > span').on('click', function (e) {
        var children = $(this).parent('li.parent_li').find(' > ul > li');
        if (children.is(\":visible\")) {
            children.hide('fast');
            $(this).attr('title', 'Expand this branch').find(' > i').addClass('icon-plus-sign').removeClass('icon-minus-sign');
        } else {
            children.show('fast');
            $(this).attr('title', 'Collapse this branch').find(' > i').addClass('icon-minus-sign').removeClass('icon-plus-sign');
        }
        e.stopPropagation();
    });
});


";  
  
            $this->document->addScriptDeclaration($javascript);
            $this->document->addStyleSheet(Uri::base().'components/'.$this->option.'/assets/css/bootstrap-tree2.css');  
        }

        $this->document->setTitle(Text::_('COM_SPORTSMANAGEMENT_TEAMSTREE_PAGE_TITLE'));
      
    }

}
?>
