<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage rankingplayerbillard
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * https://github.com/parallax/jsPDF
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

echo $this->project->name;
?>
<script type="text/javascript" src="//unpkg.com/xlsx/dist/shim.min.js"></script>
<script type="text/javascript" src="//unpkg.com/xlsx/dist/xlsx.full.min.js"></script>

<script type="text/javascript" src="//unpkg.com/blob.js@1.0.1/Blob.js"></script>
<script type="text/javascript" src="//unpkg.com/file-saver@1.3.3/FileSaver.js"></script>

<script>
function doit(type, fn, dl) {
    var elt = document.getElementById('rankingplayerbillard');
    var wb = XLSX.utils.table_to_book(elt, {sheet:"Sheet JS"});
    return dl ?
        XLSX.write(wb, {bookType:type, bookSST:true, type: 'base64'}) :
        XLSX.writeFile(wb, fn || ('SheetJSTableExport.' + (type || 'xlsx')));
}
</script>

<!--
<script src="https://unpkg.com/jspdf@2.5.2/dist/jspdf.umd.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.2/jspdf.min.js"></script>
-->
<script type="text/javascript">
  window.jsPDF = window.jspdf.jsPDF;
  window.html2canvas = html2canvas;

  
  function demoFromHTML2() {

    var doc = new jsPDF('l', 'pt', "a4");
    
    //var elem = document.getElementById("rankingplayerbillard");
    //var res = doc.autoTableHtmlToJson(elem);
    //doc.autoTable(res.columns, res.data);
    doc.autoTable({ html: '#rankingplayerbillard' })
    doc.save("table.pdf");
    

    
  /**
var doc = new jsPDF('l', 'pt', "a4",'','1');
    doc.setFontSize(6);
var pdf_el=document.getElementById('rankingplayerbillard');
doc.html(  pdf_el , {x:20, y:75,maxWidth:522 , callback: function(doc_e){
    doc_e.save("bbb.pdf");
}});
*/
    }
  
        function demoFromHTML() {
            var pdf = new jsPDF('l', 'pt', 'letter');
            // source can be HTML-formatted string, or a reference
            // to an actual DOM element from which the text will be scraped.
            source = $('#customers')[0];

            // we support special element handlers. Register them with jQuery-style 
            // ID selector for either ID or node name. ("#iAmID", "div", "span" etc.)
            // There is no support for any other type of selectors 
            // (class, of compound) at this time.
            specialElementHandlers = {
                // element with id of "bypass" - jQuery style selector
                '#bypassme': function(element, renderer) {
                    // true = "handled elsewhere, bypass text extraction"
                    return true
                }
            };
            margins = {
                top: 80,
                bottom: 60,
                left: 40,
                width: 522
            };
            // all coords and widths are in jsPDF instance's declared units
            // 'inches' in this case
            pdf.html(
                    source, // HTML string or DOM elem ref.
                    margins.left, // x coord
                    margins.top, {// y coord
                        'width': margins.width, // max width of content on PDF
                        'elementHandlers': specialElementHandlers
                    },
            function(dispose) {
                // dispose: object with X, Y of the last line add to the PDF 
                //          this allow the insertion of new lines after html
                pdf.save('Test.pdf');
            }
            , margins);
        }
    </script>



<button onclick="javascript:demoFromHTML2()"><?php echo HTMLHelper::_('image', 'media/com_sportsmanagement/jl_images/pdf.png', Text::_('COM_SPORTSMANAGEMENT_SCORESHEET_EXPORT'), array(' width' => 40));?>  PDF</button>
<button onclick="javascript:doit('xlsx')"><?php echo HTMLHelper::_('image', 'media/com_sportsmanagement/jl_images/excel.png', Text::_('COM_SPORTSMANAGEMENT_SCORESHEET_EXPORT'), array(' width' => 40));?> EXCEL</button>
<div class="row table-responsive" id="customers">
<table class="table table-striped" id="rankingplayerbillard">
  
<?php
  echo '<tr>';

echo '<td>';
echo Text::_('Ranking');
echo '</td>';    
echo '<td>';
echo Text::_('Naam speler');
echo '</td>';    
  echo '<td>';
echo Text::_('Ploeg');
echo '</td>';    
  echo '<td>';
echo '';
echo '</td>';    

  
foreach ( $this->rounds as $key => $value )
  {
echo '<td colspan="2">';
echo $value->roundcode;

echo '</td>';    
  }
  /**
echo '<td>';
echo 'Total';

echo '</td>';
  */
echo '<td>';
echo 'G';

echo '</td>';
echo '<td>';
echo 'V';

echo '</td>';
  
echo '</tr>';  

  /** datum der runde */
echo '<tr>';
echo '<td>';

echo '</td>';    
echo '<td>';

echo '</td>';    
  echo '<td>';

echo '</td>';  
 echo '<td>';

echo '</td>';
  foreach ( $this->rounds as $key => $value )
  {
echo '<td colspan="2" nowrap>';
echo date( "d-m", strtotime($value->round_date_first)) ;

echo '</td>';        
  }
  
  
echo '</tr>';  


/** g/v der runde */  
echo '<tr>';
echo '<td>';

echo '</td>';    
echo '<td>';

echo '</td>';    
  echo '<td>';

echo '</td>';    
  echo '<td>';

foreach ( $this->rounds as $key => $value )
  {
echo '<td>';
echo 'G';
echo '</td>';    
echo '<td>';
echo 'V';
echo '</td>';        
  }


  
echo '</td>';    




echo '</tr>'; 


//echo '<pre>'.print_r($this->ranking,true).'</pre>';  
  
foreach ( $this->ranking as $rankkey => $rankvalue ) if ( $rankvalue['teamplayerid'] )
{ 

  switch ( $rankkey )
    {
      case 0:
echo '<tr style="border-top: 4px solid black; border-left: 4px solid black; border-right: 4px solid black">';
      break;
      case 1:
      case 2:
      case 3:
      case 4:
      case 5:
      case 6:
      case 7:
      case 8:
      case 9:
      case 10:
      case 11:
      case 12:
      case 13:
      case 14:
echo '<tr style="border-left: 4px solid black; border-right: 4px solid black">';
      break;

      case 15:
echo '<tr style="border-bottom: 4px solid black; border-left: 4px solid black; border-right: 4px solid black">';
      break;

      default:
echo '<tr>';
      break;
    }


  
echo '<td>';
  $platz = $rankkey + 1;
echo $platz;
echo '</td>';
echo '<td nowrap>';
//echo $rankvalue['teamplayerid'];
$playerinfo = sportsmanagementModelPlayer::getTeamPlayer($this->project->id, 0, $rankvalue['teamplayerid']);  

 
 //echo '<pre>'.print_r($playerinfo,true).'</pre>';  

  foreach ($playerinfo as $player)
					{
//echo $player->firstname . ' ' . $player->lastname.' ('.$player->knvbnr.')';
echo $player->firstname . ' ' . $player->lastname;                      
                      }
echo '</td>';  

echo '<td nowrap>';
$teaminfo = sportsmanagementModelProject::getTeaminfo($rankvalue['projectteamid'], 0);
//echo '<pre>'.print_r($teaminfo,true).'</pre>';  
  echo $teaminfo->name;
echo '</td>'; 
echo '<td>';
echo $player->knvbnr;
  
echo '</td>'; 
  
foreach ( $this->rounds as $key => $value )
  {
echo '<td>';
echo $rankvalue[$value->roundcode]['G'];
echo '</td>';

    echo '<td>';
echo $rankvalue[$value->roundcode]['V'];
    echo '</td>';
  }
  /**
echo '<td>';
echo $rankvalue['total'];
echo '</td>';
*/
echo '<td>';
echo $rankvalue['totalG'];
echo '</td>';

echo '<td>';
echo $rankvalue['totalV'];
echo '</td>';

   
echo '</tr>'; 
 } 
























  
?>
  </table>
</div>
<?php
echo $this->loadTemplate('jsminfo');