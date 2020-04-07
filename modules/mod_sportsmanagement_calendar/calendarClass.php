<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage rules
 * @file       calendarClass.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    mod_sportsmanagement_calendar
 */


//this is the PHP Calendar Class

// PHP Calendar Class Version 1.4 (5th March 2001)
//
// Copyright David Wilkinson 2000 - 2001. All Rights reserved.
//
// This software may be used, modified and distributed freely
// providing this copyright notice remains intact at the head
// of the file.
//
// This software is freeware. The author accepts no liability for
// any loss or damages whatsoever incurred directly or indirectly
// from the use of this script. The author of this software makes
// no claims as to its fitness for any purpose whatsoever. If you
// wish to use this software you should first satisfy yourself that
// it meets your requirements.
//
// URL:   http://www.cascade.org.uk/software/php/calendar/
// Email: davidw@cascade.org.uk

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

class PHPCalendar
{



    function getDayNames()
    {
        return $this->dayNames;
    }
    function setDayNames($names)
    {
        $this->dayNames = $names;
    }
    function getMonthNames()
    {
        return $this->monthNames;
    }
    function setMonthNames($names)
    {
        $this->monthNames = $names;
    }
    function getStartDay()
    {
        return $this->startDay;
    }
    function setStartDay($day)
    {
        $this->startDay = $day;  
    }
    function getStartMonth()
    {
        return $this->startMonth;
    }
    function setStartMonth($month)
    {
        $this->startMonth = $month;
    }
   
    /*
        Return the HTML for a specified month
    */
    function getMonthView($month, $year)
    {
        return $this->getMonthHTML($month, $year);
    }
  
 
  
  
    /********************************************************************************
  
        The rest are private methods. No user-servicable parts inside.
      
        You shouldn't need to call any of these functions directly.
      
    *********************************************************************************/


    /*
        Calculate the number of days in a month, taking into account leap years.
    */
    function getDaysInMonth($month, $year)
    {
        if ($month < 1 || $month > 12) {
            return 0;
        }
 
        $d = $this->daysInMonth[$month - 1];
 
        if ($month == 2) {
            // Check for leap year
            // Forget the 4000 rule, I doubt I'll be around then...
      
            if ($year%4 == 0) {
                if ($year%100 == 0) {
                    if ($year%400 == 0) {
                        $d = 29;
                    }
                }
                else
                {
                    $d = 29;
                }
            }
        }
  
        return $d;
    }


    /*
        Generate the HTML for a given month
    */
    function getMonthHTML($m, $y, $showYear = 1)
    {
    
        $doc = Factory::getDocument();
        $s = "";
      
        $a = $this->adjustDate($m, $y);
        $month = $a[0];
        $year = $a[1];      
      
        $daysInMonth = $this->getDaysInMonth($month, $year);
        $date = getdate(mktime(12, 0, 0, $month, 1, $year));
        $daysInLastMonth = $this->getDaysInMonth(($month-1), $year);
        $first = $date["wday"];
        $monthName = $this->monthNames[$month - 1];
    
        $prev = $this->adjustDate($month - 1, $year);
        $next = $this->adjustDate($month + 1, $year);
    
        if ($showYear == 1) {
            $prevMonth = JSMCalendar::getCalendarLink($prev[0], $prev[1]);
            $nextMonth = JSMCalendar::getCalendarLink($next[0], $next[1]);
            $nextYear  = JSMCalendar::getCalendarLink($next[0], $next[1]);
            $prevYear  = JSMCalendar::getCalendarLink($prev[0], $prev[1]);
        }
        else
        {
            $prevMonth = "";
            $nextMonth = "";
            $prevYear = "";
            $nextYear = "";
        }
        $todaylink='';
        $language= Factory::getLanguage(); //get the current language
        $language->load('mod_sportsmanagement_calendar'); //load the language ini file of the module
        $header = $monthName . (($showYear > 0) ? " " . $year : "");
        $s .= '<table id="jlctableCalendar-'.$this->modid.'" class="jlcCalendar">'."\n";
        $s .= "   <tr>\n";
        $s .= '      <td align="center" class="jlcCalendarHeader jlcheaderArrow">' . '<a class="jlcheaderArrow" title="'
           .  $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_PREVYEAR').'" id="jlcprevYear-' . $this->modid
           .  '" href="javascript:void(0)" onclick="jlcnewDate('.$month.",". ($year-1).",".$this->modid
           .  ');">&lt;&lt;</a>'  . "</td>\n";
        $s .= '      <td align="center" class="jlcCalendarHeader jlcheaderArrow">' . '<a class="jlcheaderArrow" title="'
           .  $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_PREVMONTH').'" id="jlcprevMonth-' . $this->modid
           .  '" href="javascript:void(0)" onclick="jlcnewDate('.$prev[0].",". $prev[1].",".$this->modid
           .  ');">&lt;</a>'  . "</td>\n";
        $s .= '<td align="center" class="jlcCalendarHeader jlcheaderDate" colspan="3">'.$header."</td>\n";
        $s .= '<td align="center" class="jlcCalendarHeader jlcheaderArrow">' . '<a class="jlcheaderArrow" title="'
           .  $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_NEXTMONTH').'" id="jlcnextMonth-' . $this->modid
           .  '" href="'.'javascript:void(0)'.'" onclick="'."jlcnewDate(".$next[0].",". $next[1].",".$this->modid
           .');" >&gt;</a>'  . "</td>\n";
        $s .= '<td align="center" class="jlcCalendarHeader jlcheaderArrow">' . '<a class="jlcheaderArrow" title="'
           .  $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_NEXTYEAR').'" id="jlcnextYear-' . $this->modid
           .  '" href="'.'javascript:void(0)'.'" onclick="'."jlcnewDate(".$month.",". ($year+1).",".$this->modid
           .');" >&gt;&gt;</a>'  . "</td>\n";
        $s .= "</tr>\n";
    
        $s .= "<tr>\n";
        for($i=0;$i<7;$i++)
        {
      
            $s .= '<td class="jlcdayName">' . $this->dayNames[($this->startDay+$i)%7] . "</td>\n";
      
        }
        $s .= "</tr>\n";
    
        $d = $this->startDay + 1 - $first;
        while ($d > 1)
        {
            $d -= 7;
        }

        // if(!$day){
          $today = getdate(time());
        //}
        //else{$today = array("year"=>$y,"mon"=>$m,"mday"=>$day);}
    
  
        while ($d <= $daysInMonth)
        {
        
            $s .= "<tr>\n";
            for ($i = 0; $i < 7; $i++)
            {
                $class = ($year == $today["year"] && $month == $today["mon"] && $d == $today["mday"]) ? "highlight jlcCalendarDay jlcCalendarToday " : "jlcCalendarDay ";
                $s .= "<td class=\"";
                $tdEnd='">';
                if ($d > 0 && $d <= $daysInMonth) {
                    $divday = ($d > 9) ? $d : '0'.$d;
                    $divm = ($month > 9) ? $month : '0'.$month;
                    $divid = 'jlcal_' . $year.'-'.$divm.'-'.$divday;
                    $link = JSMCalendar::getDateLink($d, $month, $year);
                    $click = JSMCalendar::getDateClick($d, $month, $year);
              
                    //echo $link.'<br>';
                    //echo $click.'<br>';
              
                    $modalrel = '';
                    //$modalrel = ($link == "") ? '' : " rel=\"{handler: 'adopt', adopt:'jlCalList-".$this->modid."_temp'}\"";
                    if($link && $class=="highlight jlcCalendarDay jlcCalendarToday ") {
                        $todaylink=$click;
                        $s .= $class . 'jlcCalendarTodayLink' . $tdEnd
                         . '<a class="hasTip jlcCalendarToday jlcmodal' . $this->modid. '"'
                         . ' href="' . $link . '" onclick="' . $click . '"';
                        $s .= $modalrel . " >$divday</a>";
                    }
                    else
                    {
                        $s .= (($link == "") ? $class.$tdEnd.$divday : "jlcCalendarDay"
                         .  $tdEnd.'<a class="jlcCalendarDay hasTip jlcmodal' . $this->modid
                         .  '" href="' . $link . '" onclick="' . $click . '"'
                         .  $modalrel . " >$divday</a>");
                    }
                }
                else
                {
                    if($d <= 0) {
                        $do = ($daysInLastMonth + $d);
                    }
                    else { $do = '0'.($d - $daysInMonth);
                    }
                    $s .= "jlcCalendarDay jlcCalendarDayEmpty " . $tdEnd . "$do";
                }
                $s .= "</td>\n";     
                $d++;
            }
            $s .= "</tr>\n";  
        }

        $s .= "</table>\n";

        if($todaylink!='' && $this->ajax == 0 && $this->lightbox == 1 && $this->lightbox_on_pageload == 1) {
            $doc->addScriptDeclaration(
                'window.addEvent(\'domready\', function() {
  			'.$todaylink.'
  		});
		'
            );
        }
        $output = array();
        $output['calendar'] = $s;
        $output['list'] = JSMCalendar::matches_output($m, $y, $d);
        $output['teamslist'] = JSMCalendar::output_teamlist();
 
        return $output;
    }
  

    function adjustDate($month, $year)
    {
        $a = array();
        $a[0] = $month;
        $a[1] = $year;
      
        while ($a[0] > 12)
        {
            $a[0] -= 12;
            $a[1]++;
        }
      
        while ($a[0] <= 0)
        {
            $a[0] += 12;
            $a[1]--;
        }
      
        return $a;
    }


    /*
        The start day of the week. This is the day that appears in the first column
        of the calendar. Sunday = 0.
    */
    var $startDay = 0;

    /*
        The start month of the year. This is the month that appears in the first slot
        of the calendar in the year view. January = 1.
    */
    var $startMonth = 1;

    /*
        The labels to display for the days of the week. The first entry in this array
        represents Sunday.
    */
    var $dayNames = array("S", "M", "T", "W", "T", "F", "S");
  
    /*
        The labels to display for the months of the year. The first entry in this array
        represents January.
    */
    var $monthNames = array("January", "February", "March", "April", "May", "June",
                            "July", "August", "September", "October", "November", "December");
                          
                          
    /*
        The number of days in each month. You're unlikely to want to change this...
        The first entry in this array represents January.
    */
    var $daysInMonth = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

    var $modid = "";
    var $ajax = 0;
    var $lightbox = 0;
    var $lightbox_on_pageload = 0;
    var $usedteams = '';
    var $usedclubs = '';
}

?>
