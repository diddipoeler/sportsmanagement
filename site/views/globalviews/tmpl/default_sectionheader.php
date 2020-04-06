<?php 
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_sectionheader.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage globalviews
 */

defined('_JEXEC') or die('Restricted access'); 
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

// Reference global application object
$app = Factory::getApplication();
// JInput object
$jinput = $app->input;
$view = $jinput->getVar("view");        
$modalheight = ComponentHelper::getParams($jinput->getCmd('option'))->get('modal_popup_height', 600);
$modalwidth = ComponentHelper::getParams($jinput->getCmd('option'))->get('modal_popup_width', 900);
?>
<!-- START: Contentheading -->
<div class="<?php echo $this->divclassrow;?>" id="sectionheader">
<?php
switch ( $view )
{
    
case 'matchreport':
    ?>
    
    <table class="table">
    <tr>
        <td class="contentheading"><?php
        $pageTitle = 'COM_SPORTSMANAGEMENT_MATCHREPORT_TITLE';
        $timestamp = strtotime($this->match->match_date);
        if (isset($this->round->name) && $timestamp ) {
            $matchDate = sportsmanagementHelper::getTimestamp($this->match->match_date, 1);
            echo '&nbsp;' . Text::sprintf(
                $pageTitle,
                $this->round->name,
                HTMLHelper::date($matchDate, Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_GAMES_DATE')),
                sportsmanagementHelperHtml::showMatchTime($this->match, $this->config, $this->overallconfig, $this->project) 
            );
        
        }
        elseif (isset($this->round->name) && !$timestamp ) {
            echo '&nbsp;' . Text::sprintf($pageTitle, $this->round->name, '', '');
        }  
        else
        {
            echo '&nbsp;' . Text::sprintf($pageTitle, '', '', '');
        }
    ?></td>
    </tr>
</table>
    
    <?PHP
    break;
case 'player':
    ?>
    <table class="table">
    <tr>
        <td class="contentheading">
    <?php
    echo Text::sprintf('COM_SPORTSMANAGEMENT_PLAYER_INFORMATION', sportsmanagementHelper::formatName(null, $this->person->firstname, $this->person->nickname, $this->person->lastname, $this->config["name_format"]));
    
    if ($this->showediticon ) {
        
          // $link = sportsmanagementHelperRoute::getPlayerRoute( $this->project->id, $this->teamPlayer->team_id, $this->person->id, 'person.edit' );
    
          $routeparameter = array();
        $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
        $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
        $routeparameter['p'] = $this->project->id;
        $routeparameter['tid'] = $this->teamPlayer->team_id;
        $routeparameter['pid'] = $this->person->id;
        $link = sportsmanagementHelperRoute::getSportsmanagementRoute('editperson', $routeparameter, 'person.edit');
        echo sportsmanagementHelperHtml::getBootstrapModalImage(
            'editperson'.$this->person->id,
            'administrator/components/com_sportsmanagement/assets/images/edit.png',
            Text::_('COM_SPORTSMANAGEMENT_PERSON_EDIT_DETAILS'),
            '20',
            $link,
            $this->modalwidth,
            $this->modalheight,
            $this->overallconfig['use_jquery_modal']
        );      
                
    }

    if (isset($this->teamPlayer->injury) && $this->teamPlayer->injury ) {
        $imageTitle = Text::_('COM_SPORTSMANAGEMENT_PERSON_INJURED');
        echo "&nbsp;&nbsp;" . HTMLHelper::image(
            'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/injured.gif',
            $imageTitle,
            array( 'title' => $imageTitle ) 
        );
    }

    if (isset($this->teamPlayer->suspension) && $this->teamPlayer->suspension ) {
        $imageTitle = Text::_('COM_SPORTSMANAGEMENT_PERSON_SUSPENDED');
        echo "&nbsp;&nbsp;" . HTMLHelper::image(
            'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/suspension.gif',
            $imageTitle,
            array( 'title' => $imageTitle ) 
        );
    }


    if (isset($this->teamPlayer->away) && $this->teamPlayer->away ) {
        $imageTitle = Text::_('COM_SPORTSMANAGEMENT_PERSON_AWAY');
        echo "&nbsp;&nbsp;" . HTMLHelper::image(
            'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/away.gif',
            $imageTitle,
            array( 'title' => $imageTitle ) 
        );
    }
    ?>
        </td>
    </tr>
</table>
    
    <?PHP
    break;
case 'staff':
    ?>
    <table class="table">
    <tr>
        <td class="contentheading">
    <?php
    echo $this->title;
            
            
    if ($this->showediticon ) {
        $link = "index.php?option=com_sportsmanagement&tmpl=component&view=editperson&id=<?php echo $this->person->id; ?>";
        echo sportsmanagementHelperHtml::getBootstrapModalImage(
            'personedit'.$this->person->id,
            'administrator/components/com_sportsmanagement/assets/images/edit.png',
            Text::_('COM_SPORTSMANAGEMENT_PERSON_EDIT_DETAILS'),
            '20',
            $link,
            $this->modalwidth,
            $this->modalheight,
            $this->overallconfig['use_jquery_modal']
        );         
    
    
                
    }
    ?>
        </td>
    </tr>
</table>
    
    <?PHP
    break;
case 'results':
    ?>
    <!-- section header e.g. ranking, results etc. -->
<table class="table">
    <tr>
        <td class="contentheading">
    <?php
    if ($this->roundid) {
        $title = Text::_('COM_SPORTSMANAGEMENT_RESULTS_ROUND_RESULTS');
        if (isset($this->division) ) {
            $title = Text::sprintf('COM_SPORTSMANAGEMENT_RESULTS_ROUND_RESULTS2', '<i>' . $this->division->name . '</i>');
        }
        sportsmanagementHelperHtml::showMatchdaysTitle($title, $this->roundid, $this->config);

        if ($this->showediticon ) {
            $routeparameter = array();
            $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
            $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
            $routeparameter['p'] = sportsmanagementModelProject::$projectslug;
            $routeparameter['r'] = sportsmanagementModelProject::$roundslug;
            $routeparameter['division'] = sportsmanagementModelResults::$divisionid;
            $routeparameter['mode'] = sportsmanagementModelResults::$mode;
            $routeparameter['order'] = sportsmanagementModelResults::$order;
            $routeparameter['layout'] = $this->config['result_style_edit'];
            $link = sportsmanagementHelperRoute::getSportsmanagementRoute('results', $routeparameter);
                
            $imgTitle = Text::_('COM_SPORTSMANAGEMENT_RESULTS_ENTER_EDIT_RESULTS');
            $desc = HTMLHelper::image('media/com_sportsmanagement/jl_images/edit.png', $imgTitle, array( ' title' => $imgTitle ));
            echo ' ';
            echo HTMLHelper::link($link, $desc);
        }
    }
    else
    {
        //1 request for current round
        // seems to be this shall show a plan of matches of a team???
        sportsmanagementHelperHtml::showMatchdaysTitle(Text::_('COM_SPORTSMANAGEMENT_RESULTS_PLAN'), 0, $this->config);
    }
    ?>
        </td>
    <?php if ($this->config['show_matchday_dropdown'] == 1 ) { 
    ?>
        <form name='resultsRoundSelector' method='post'>
        <input type='hidden' name='option' value='com_sportsmanagement' />
        <td>
        <?php

        $imgtitle = Text::_('COM_SPORTSMANAGEMENT_GLOBAL_PREV');
        echo HTMLHelper::link(sportsmanagementModelPagination::$prevlink, HTMLHelper::image('images/com_sportsmanagement/database/jl_images/arrow_left_small.png', $imgtitle, 'title= "' . $imgtitle . '"'));
        //echo sportsmanagementModelPagination::$prevlink;
    ?>
        </td>
                <td class="contentheading" style="text-align:right; font-size: 100%;">
    <?php echo sportsmanagementHelperHtml::getRoundSelectNavigation(false, Factory::getApplication()->input->getInt('cfg_which_database', 0)); ?>
                </td>
                <td>
        <?php
        if (sportsmanagementModelPagination::$nextlink ) {
                $imgtitle = Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NEXT');
                echo HTMLHelper::link(sportsmanagementModelPagination::$nextlink, HTMLHelper::image('images/com_sportsmanagement/database/jl_images/arrow_right_small.png', $imgtitle, 'title= "' . $imgtitle . '"'));
        }
    ?>
        </td>
                </form>
            <?php 
} 
    ?>
        </tr>
</table>
    
    <?PHP
    break;
case 'teamplan':
    ?>
    <table class="table">
    <tr>
        <td class="contentheading">
    <?php
    $output='';
    if ((isset($this->division)) && (is_a($this->division, 'LeagueDivision'))) {
        $output .= ' '.$this->division->name.' ';
    }
    if (!empty($this->ptid)) {
        $output .= ' '.$this->teams[$this->ptid]->name;
    }
    else
    {
        $output .= ' '.$this->project->name;
    }
    echo Text::sprintf('COM_SPORTSMANAGEMENT_TEAMPLAN_PAGE_TITLE', $output);
    ?>
        </td>
    <?php
    if($this->config['show_ical_link']) {
        ?>
       <td class="contentheading" style="text-align: right;">
        <?php
        if (!is_null($this->ptid)) {
            $routeparameter = array();
            $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
            $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
            $routeparameter['p'] = $this->project->id;
            $routeparameter['tid'] = $this->teams[$this->ptid]->team_id;
            $routeparameter['division'] = 0;
            $routeparameter['mode'] = 0;
            $routeparameter['ptid'] = $this->ptid;
            $link = sportsmanagementHelperRoute::getSportsmanagementRoute('ical', $routeparameter);
                    
            //$link = sportsmanagementHelperRoute::getIcalRoute($this->project->id,$this->teams[$this->ptid]->team_id,null,null);
            $text = HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/calendar.png', Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_ICAL_EXPORT'));
            $attribs = array('title' => Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_ICAL_EXPORT'));
            echo HTMLHelper::_('link', $link, $text, $attribs);
        }
        ?>
       </td>
        <?php
    }
    ?>
    </tr>
</table><br />
    <?PHP
    break;
case 'curve':
    ?>
 <table class="table">
        <tr>
            <td class="contentheading"><a name="division<?php echo $this->divisions;?>"></a>
            
    <?php 
                echo Text::_('COM_SPORTSMANAGEMENT_CURVE_TITLE');
    if ($this->division) {
        echo ' '.$this->division->name;
    }
    ?>
            </td>
        </tr>
    </table>
<br/>	
    <?PHP
    break;
case 'matrix':
    ?>
 <table class="table" >
        <tr>
            <td class="contentheading">
                <?php
                echo '&nbsp;' . Text::_('COM_SPORTSMANAGEMENT_MATRIX');
                if ($this->divisionid ) {
                    echo " " . $this->division->name;
                }
                if ($this->roundid ) {
                    echo " - " . $this->round->name;
                }
                ?>
            </td>
        </tr>
    </table>
<br />
    <?PHP
    break;
case 'roster':
    ?>
    <table class="table">
    <tr>
        <td class="contentheading">
    <?php
    if ($this->config['show_team_shortform'] == 1 && !empty($this->team->short_name)) {
        echo '&nbsp;' . Text::sprintf('COM_SPORTSMANAGEMENT_ROSTER_TITLE2', $this->team->name, $this->team->short_name);
    }
    else
    {
        echo '&nbsp;' . Text::sprintf('COM_SPORTSMANAGEMENT_ROSTER_TITLE', $this->team->name);
    }
    ?>
        </td>

        <form name='resultsRoundSelector' method='post'>
        <input type='hidden' name='option' value='com_sportsmanagement' />
    <?php
    if ($this->config['show_drop_down_menue'] ) {
        if ($this->config['show_players'] ) {
            echo "<td>".HTMLHelper::_('select.genericlist', $this->lists['type'], 'type', 'class="inputbox" size="1" onchange="this.form.submit();" ', 'value', 'text', $this->type)."</td>";
        }
        if ($this->config['show_staff'] ) {
            echo "<td>".HTMLHelper::_('select.genericlist', $this->lists['typestaff'], 'typestaff', 'class="inputbox" size="1" onchange="this.form.submit();" ', 'value', 'text', $this->typestaff)."</td>";
        }
    }
    ?>
    </form>
    </tr>
</table>
<br />
    <?PHP
    break;
case 'nextmatch':
    ?>
    <table class="table">
    <tr>
        <td class="contentheading"><?php
        if ($this->match->match_date == "0000-00-00" ) {
            echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_DATE_EMPTY');;
        }
        else
        {
            echo HTMLHelper::date($this->match->match_date, Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_GAMES_DATE')). " ".
            sportsmanagementHelperHtml::showMatchTime($this->match, $this->config, $this->overallconfig, $this->project);
        } 
    ?></td>
    </tr>
</table>
    <?PHP
    break;
case 'teaminfo':
    ?>
    <h4>
    <?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_PAGE_TITLE') . " - " . $this->team->tname;
    if ($this->showediticon ) {
        $link = "index.php?option=com_sportsmanagement&tmpl=component&view=editprojectteam&ptid=".$this->projectteamid."&tid=".$this->teamid."&p=".$this->project->id;    
        echo sportsmanagementHelperHtml::getBootstrapModalImage(
            'projectteamedit'.$this->projectteamid,
            'administrator/components/com_sportsmanagement/assets/images/edit.png',
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMINFO_EDIT_DETAILS'),
            '20',
            $link,
            $this->modalwidth,
            $this->modalheight,
            $this->overallconfig['use_jquery_modal']
        );             

        $link = "index.php?option=com_sportsmanagement&tmpl=component&view=editteam&ptid=".$this->projectteamid."&tid=".$this->teamid."&p=".$this->project->id;    
        echo sportsmanagementHelperHtml::getBootstrapModalImage(
            'teamedit'.$this->projectteamid,
            'administrator/components/com_sportsmanagement/assets/images/teams.png',
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMINFO_EDIT_DETAILS'),
            '20',
            $link,
            $this->modalwidth,
            $this->modalheight,
            $this->overallconfig['use_jquery_modal']
        ); 
            
    } else {
        //echo "no permission";
    }
    ?>
    </h4>


<?PHP
    break;
case 'clubinfo':
?>


<h4>
<?php
echo Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_TITLE') . " " . $this->club->name;

if ($this->showediticon ) {
                    
    $link = sportsmanagementHelperRoute::getClubInfoRoute($this->project->id, $this->club->id, "club.edit");
                    
    echo sportsmanagementHelperHtml::getBootstrapModalImage(
        'clubedit'.$this->club->id,
        'administrator/components/com_sportsmanagement/assets/images/edit.png',
        Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBINFO_EDIT_DETAILS'),
        '20',
        $link,
        $this->modalwidth,
        $this->modalheight,
        $this->overallconfig['use_jquery_modal']
    );                  
                
                
}
    ?>
        </h4>
    <?PHP    
    break;
default:
    ?>

    <table class="table" id="sectionheader">
    <tr>
    <td class="contentheading">
    
    <?php
    echo $this->headertitle;
    ?>
    </td>
    </tr>
    </table>

    <br />
    <?PHP
    break;
}
?>
</div>
<!-- END: Contentheading -->
