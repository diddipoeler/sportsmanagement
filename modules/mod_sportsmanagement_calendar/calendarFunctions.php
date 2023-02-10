<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage rules
 * @file       calendarClass.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    mod_sportsmanagement_calendar
 * 
 * 
 * https://www.codexworld.com/build-event-calendar-using-jquery-ajax-php/
 * 
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

/** 
 * Load function based on the Ajax request 
 */ 
if(isset($_POST['func']) && !empty($_POST['func'])){ 
    switch($_POST['func']){ 
        case 'getCalender': 
            getCalender($_POST['year'],$_POST['month']); 
            break; 
        case 'getEvents': 
            getEvents($_POST['date']); 
            break; 
        default: 
            break; 
    } 
} 
 
/** 
 * Generate event calendar in HTML format 
 */ 
function getCalender($year = '', $month = ''){ 
    $dateYear = ($year != '')?$year:date("Y"); 
    $dateMonth = ($month != '')?$month:date("m"); 
    $date = $dateYear.'-'.$dateMonth.'-01'; 
    $currentMonthFirstDay = date("N",strtotime($date)); 
    $totalDaysOfMonth = cal_days_in_month(CAL_GREGORIAN,$dateMonth,$dateYear); 
    $totalDaysOfMonthDisplay = ($currentMonthFirstDay == 1)?($totalDaysOfMonth):($totalDaysOfMonth + ($currentMonthFirstDay - 1)); 
    $boxDisplay = ($totalDaysOfMonthDisplay <= 35)?35:42; 
     
    $prevMonth = date("m", strtotime('-1 month', strtotime($date))); 
    $prevYear = date("Y", strtotime('-1 month', strtotime($date))); 
    $totalDaysOfMonth_Prev = cal_days_in_month(CAL_GREGORIAN, $prevMonth, $prevYear); 
?> 
    <main class="calendar-contain"> 
        <section class="title-bar"> 
            <a href="javascript:void(0);" class="title-bar__prev" onclick="getCalendar('calendar_div','<?php echo date("Y",strtotime($date.' - 1 Month')); ?>','<?php echo date("m",strtotime($date.' - 1 Month')); ?>');"></a> 
            <div class="title-bar__month"> 
                <select class="month-dropdown"> 
                    <?php echo getMonthList($dateMonth); ?> 
                </select> 
            </div> 
            <div class="title-bar__year"> 
                <select class="year-dropdown"> 
                    <?php echo getYearList($dateYear); ?> 
                </select> 
            </div> 
            <a href="javascript:void(0);" class="title-bar__next" onclick="getCalendar('calendar_div','<?php echo date("Y",strtotime($date.' + 1 Month')); ?>','<?php echo date("m",strtotime($date.' + 1 Month')); ?>');"></a> 
        </section> 
         
        <aside class="calendar__sidebar" id="event_list"> 
            <?php echo getEvents(); ?> 
        </aside> 
         
        <section class="calendar__days"> 
            <section class="calendar__top-bar"> 
                <span class="top-bar__days">Mon</span> 
                <span class="top-bar__days">Tue</span> 
                <span class="top-bar__days">Wed</span> 
                <span class="top-bar__days">Thu</span> 
                <span class="top-bar__days">Fri</span> 
                <span class="top-bar__days">Sat</span> 
                <span class="top-bar__days">Sun</span> 
            </section> 
             
            <?php  
                $dayCount = 1; 
                $eventNum = 0; 
                 
                echo '<section class="calendar__week">'; 
                for($cb=1;$cb<=$boxDisplay;$cb++){ 
                    if(($cb >= $currentMonthFirstDay || $currentMonthFirstDay == 1) && $cb <= ($totalDaysOfMonthDisplay)){ 
                        // Current date 
                        $currentDate = $dateYear.'-'.$dateMonth.'-'.$dayCount; 
                         
                        //// Get number of events based on the current date 
//                        global $db; 
//                        $result = $db->query("SELECT title FROM events WHERE date = '".$currentDate."' AND status = 1"); 
//                        $eventNum = $result->num_rows; 
                         
                        // Define date cell color 
                        if(strtotime($currentDate) == strtotime(date("Y-m-d"))){ 
                            echo ' 
                                <div class="calendar__day today" onclick="getEvents(\''.$currentDate.'\');"> 
                                    <span class="calendar__date">'.$dayCount.'</span> 
                                    <span class="calendar__task calendar__task--today">'.$eventNum.' Events</span> 
                                </div> 
                            '; 
                        }elseif($eventNum > 0){ 
                            echo ' 
                                <div class="calendar__day event" onclick="getEvents(\''.$currentDate.'\');"> 
                                    <span class="calendar__date">'.$dayCount.'</span> 
                                    <span class="calendar__task">'.$eventNum.' Events</span> 
                                </div> 
                            '; 
                        }else{ 
                            echo ' 
                                <div class="calendar__day no-event" onclick="getEvents(\''.$currentDate.'\');"> 
                                    <span class="calendar__date">'.$dayCount.'</span> 
                                    <span class="calendar__task">'.$eventNum.' Events</span> 
                                </div> 
                            '; 
                        } 
                        $dayCount++; 
                    }else{ 
                        if($cb < $currentMonthFirstDay){ 
                            $inactiveCalendarDay = ((($totalDaysOfMonth_Prev-$currentMonthFirstDay)+1)+$cb); 
                            $inactiveLabel = 'expired'; 
                        }else{ 
                            $inactiveCalendarDay = ($cb-$totalDaysOfMonthDisplay); 
                            $inactiveLabel = 'upcoming'; 
                        } 
                        echo ' 
                            <div class="calendar__day inactive"> 
                                <span class="calendar__date">'.$inactiveCalendarDay.'</span> 
                                <span class="calendar__task">'.$inactiveLabel.'</span> 
                            </div> 
                        '; 
                    } 
                    echo ($cb%7 == 0 && $cb != $boxDisplay)?'</section><section class="calendar__week">':''; 
                } 
                echo '</section>'; 
            ?> 
        </section> 
    </main> 
 
    <script> 
        function getCalendar(target_div, year, month){ 
            $.ajax({ 
                type:'POST', 
                url:'functions.php', 
                data:'func=getCalender&year='+year+'&month='+month, 
                success:function(html){ 
                    $('#'+target_div).html(html); 
                } 
            }); 
        } 
         
        function getEvents(date){ 
            $.ajax({ 
                type:'POST', 
                url:'functions.php', 
                data:'func=getEvents&date='+date, 
                success:function(html){ 
                    $('#event_list').html(html); 
                } 
            }); 
        } 
         
        $(document).ready(function(){ 
            $('.month-dropdown').on('change',function(){ 
                getCalendar('calendar_div', $('.year-dropdown').val(), $('.month-dropdown').val()); 
            }); 
            $('.year-dropdown').on('change',function(){ 
                getCalendar('calendar_div', $('.year-dropdown').val(), $('.month-dropdown').val()); 
            }); 
        }); 
    </script> 
<?php 
} 
 
/** 
 * Generate months options list for select box 
 */ 
function getMonthList($selected = ''){ 
    $options = ''; 
    for($i=1;$i<=12;$i++) 
    { 
        $value = ($i < 10)?'0'.$i:$i; 
        $selectedOpt = ($value == $selected)?'selected':''; 
        $options .= '<option value="'.$value.'" '.$selectedOpt.' >'.date("F", mktime(0, 0, 0, $i+1, 0, 0)).'</option>'; 
    } 
    return $options; 
} 
 
/** 
 * Generate years options list for select box 
 */ 
function getYearList($selected = ''){ 
    $yearInit = !empty($selected)?$selected:date("Y"); 
    $yearPrev = ($yearInit - 5); 
    $yearNext = ($yearInit + 5); 
    $options = ''; 
    for($i=$yearPrev;$i<=$yearNext;$i++){ 
        $selectedOpt = ($i == $selected)?'selected':''; 
        $options .= '<option value="'.$i.'" '.$selectedOpt.' >'.$i.'</option>'; 
    } 
    return $options; 
} 
 
/** 
 * Generate events list in HTML format 
 */ 
function getEvents($date = ''){ 
    $date = $date?$date:date("Y-m-d"); 
     
    $eventListHTML = '<h2 class="sidebar__heading">'.date("l", strtotime($date)).'<br>'.date("F d", strtotime($date)).'</h2>'; 
     
   // // Fetch events based on the specific date 
//    global $db; 
//    $result = $db->query("SELECT title FROM events WHERE date = '".$date."' AND status = 1"); 
//    if($result->num_rows > 0){ 
//        $eventListHTML .= '<ul class="sidebar__list">'; 
//        $eventListHTML .= '<li class="sidebar__list-item sidebar__list-item--complete">Events</li>'; 
//        $i=0; 
//        while($row = $result->fetch_assoc()){ $i++; 
//            $eventListHTML .= '<li class="sidebar__list-item"><span class="list-item__time">'.$i.'.</span>'.$row['title'].'</li>'; 
//        } 
//        $eventListHTML .= '</ul>'; 
//    } 
    echo $eventListHTML; 
}
?>
