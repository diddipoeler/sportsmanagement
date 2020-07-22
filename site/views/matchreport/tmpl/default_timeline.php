<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage matchreport
 * @file       default_timeline.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

?>
<style>  

#semesterbilder { 
    position: relative; 
    height: 300px; 
    background-color: #F0F0F0; 
} 

#matchreport-homeline { 
    position: relative; 
    height: 80px; 
    
}   
#matchreport-guestline { 
    position: relative; 
    height: 80px; 
    
} 

#bild1 { 
    position: absolute; 
    left: -15px; 
    top: 0px; 
    z-index: 1; 
} 
#bild2 { 
    position: absolute; 
    left: 171px; 
    top: 109px; 
    z-index: 2; 
} 
#bild3 { 
    position: absolute; 
    left: 366px; 
    top: 19px; 
    z-index: 3; 
} 
#matchreport-homeline {
background-image: url("/images/com_sportsmanagement/database/matchreport/spielfeld_top.png");
background-repeat: no-repeat;
background-size: 100% 79px;
height: 80px;

}
#matchreport-guestline {
background-image: url("/images/com_sportsmanagement/database/matchreport/spielfeld_bottom.png");
background-repeat: no-repeat;
background-size: 100% 79px;
height: 80px;
vertical-align: baseline;

}  
</style>  
<!--  
<h2><a id="Semesterpraesentationen"></a>Semesterpräsentationen</h2> 
<div id="semesterbilder"> 
    <div id="bild1"><img src="/images/com_sportsmanagement/database/clubs/large/200px-logo-sc_freiburg_svg_1327138422.png" width="auto" height="20"></div> 
    <div id="bild2"><img src="/images/com_sportsmanagement/database/clubs/large/200px-logo-sc_freiburg_svg_1327138422.png" width="auto" height="20"></div> 
    <div id="bild3"><img src="/images/com_sportsmanagement/database/clubs/large/200px-logo-sc_freiburg_svg_1327138422.png" width="auto" height="20"></div> 
</div> 
--> 
  
  
<!-- START of match timeline -->
<div class="<?php echo $this->divclassrow; ?> " id="matchreport-timeline" >
    <script type="text/javascript">
        function gotoevent(row) {
            var t = document.getElementById('event-' + row)
            t.scrollIntoView()
        }
    </script>
    <h2><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_TIMELINE'); ?></h2>
    <div id="timelineheader" class="row-fluid" style="position:relative;height:15px;">
                <div id="timelinetop" style="position:relative;width:100%;">
                    <div id="firsthalftime"
                         style="position:absolute; top:0px; left:0px; width:50%; height:15px;text-align: center;color:#FFFFFF;background-color:lightgrey;">
						<?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_TIMELINE_FIRST_HALF'); ?>
                    </div>

                    <div id="secondhalftime"
                         style="position:absolute; top:0px; left:50%; width:50%; height:15px;text-align: center;color:#FFFFFF;background-color:grey;">
						<?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_TIMELINE_SECOND_HALF'); ?>
                    </div>
                </div>
    </div>

<div class="" id="matchreport-homeline" >                          
<div id="bild1">                          
<?php
echo sportsmanagementModelProject::getClubIconHtml($this->team1, 1, 0, 'logo_big', Factory::getApplication()->input->getInt('cfg_which_database', 0), 0, $this->modalwidth, $this->modalheight, $this->overallconfig['use_jquery_modal']);
?>        
  
</div>    
<div>
  <?php
					echo $this->showSubstitution_Timelines1();
					echo $this->showEvents_Timelines1();
					?>                    
</div>                      
                      
</div>                                                    
                          
                          
<div class="" id="matchreport-guestline" >                          
<div id="bild1">                          
<?php
echo sportsmanagementModelProject::getClubIconHtml($this->team2, 1, 0, 'logo_big', Factory::getApplication()->input->getInt('cfg_which_database', 0), 0, $this->modalwidth, $this->modalheight, $this->overallconfig['use_jquery_modal']);
?>                          
</div>  
  
<div>
  <?php
					echo $this->showSubstitution_Timelines2();
					echo $this->showEvents_Timelines2();
					?>                    
</div>                      
                      
</div>                          
                          
                          
                          
                          
                          
                          
                          
                          
                          
                          
                          
                          
                          
                          
    
</div>                          
