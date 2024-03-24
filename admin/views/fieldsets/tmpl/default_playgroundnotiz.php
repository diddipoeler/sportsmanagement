<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fieldsets
 * @file       default_playgroundnotiz.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

//echo '<pre>'.print_r($this->playgroundnotic,true).'</pre';

echo 'Hier können Sie die Notizen zum Stadion hinterlegen. Wie Name oder unterschiedliche Kapazitäten.<br>';

?>
<script type="text/javascript">
        function addRows()
        {
            // Reference to the table body
var body = jQuery("#playgroundnotic").find('tbody');
// Create a new row element
var row = jQuery('<tr>');
// Create a new column element
var column = jQuery('<td>');
// Create a new image element
var image = jQuery('<input>');
image.attr('type', 'text');
image.attr('id', 'plnotic_id');          
image.attr('type', 'text');
image.attr('name', 'plnotic_id[]');
image.attr('disabled', 'disabled');
image.attr('value', 'NEW');   

// Append the image to the column element
column.append(image);
// Append the column to the row element
row.append(column);

var column1 = jQuery('<td>');          
var input = jQuery('<input>');          
input.attr('type', 'text');          
input.attr('id', 'playground_id');
input.attr('name', 'playground_id[]');
input.attr('disabled', 'disabled');          
input.attr('value', '<?php echo $this->item->id; ?>');          
column1.append(input);          
row.append(column1); 
 
var column7 = jQuery('<td>');          
var input = jQuery('<input>');          
input.attr('type', 'hidden');          
input.attr('id', 'change_delete');
input.attr('name', 'change_delete[]');
input.attr('size', '10');          
column7.append(input);          
row.append(column7);  
          
var column2 = jQuery('<td>');          
var input = jQuery('<input>');          
input.attr('type', 'text');          
input.attr('id', 'date_von');
input.attr('value', '00-00-0000');
input.attr('name', 'date_von[]');
//input.attr('class', 'form-control datetimepicker-input');          
//input.attr('data-toggle', 'datetimepicker');
//input.attr('data-target', '#date_von<?php echo $this->item->id; ?>');
          
column2.append(input);          
row.append(column2);          

var column3 = jQuery('<td>');          
var input = jQuery('<input>');          
input.attr('type', 'text');          
input.attr('id', 'date_bis');
input.attr('value', '00-00-0000');
input.attr('name', 'date_bis[]');
column3.append(input);          
row.append(column3);                    
          
var column4 = jQuery('<td>');          
//var input = jQuery('<input>');          
//input.attr('type', 'text');          
//input.attr('id', 'name_visitors');
//input.attr('name', 'name_visitors[]');
//input.attr('value', '<?php echo ''; ?>');
var input = jQuery('<select>');          
input.attr('name', 'name_visitors[]');          
        
var inputoption = jQuery('<option>');
inputoption.attr('value', 'NAME');          
inputoption.prop('text', 'NAME');          
input.append(inputoption);           
var inputoption = jQuery('<option>');
inputoption.attr('value', 'VISITORS');          
inputoption.prop('text', 'VISITORS');          
input.append(inputoption);          
          
          
          
          
//column4.append(input);  
//var inputoption = jQuery('<option>');           
//inputoption.attr('value', 'NAME');
          
//input.append(inputoption);           
          
column4.append(input);            
row.append(column4);            
          
          
var column5 = jQuery('<td>');          
var input = jQuery('<input>');          
input.attr('type', 'text');          
input.attr('id', 'notes');
input.attr('name', 'notes[]');
column5.append(input);          
row.append(column5); 
          
          
var column6 = jQuery('<td>');          
var input = jQuery('<input>');          
input.attr('type', 'text');          
input.attr('id', 'max_visitors');
input.attr('name', 'max_visitors[]');
input.attr('size', '10');          
column6.append(input);          
row.append(column6);


          
var column8 = jQuery('<td>');          
var input = jQuery('<input>');          
input.attr('type', 'text');          
input.attr('id', 'max_visitors_int');
input.attr('name', 'max_visitors_int[]');
input.attr('size', '10');          
column8.append(input);          
row.append(column8);            
          
          
// Append the row to the table body
body.append(row);
        }
  
  
jQuery(function ($) {
				$('#date_von<?php echo $this->item->id; ?>').datetimepicker(
                {
					format: 'DD-MM-YYYY',
					locale: <?php echo "'" . $currentLanguage . "'"; ?>
                }
                );
		$("#date_von<?php echo $this->item->id; ?>").on("change.datetimepicker", ({date, oldDate}) => {
              console.log("New date", date);
              console.log("Old date", oldDate);
			  //document.getElementById('cb<?php echo $this->count_i; ?>').checked=true
              //alert("Changed date")
      })    
            });
  
  
        </script>

<input type="button" value="Neuen eintrag einfügen. " onclick="addRows()" />
<table class="table table-striped" id="playgroundnotic">
  <thead>
    <tr>
    
  <th>
    id
    </th>
    <th>
    playground_id
    </th>
	     <th>
    löschen ?
    </th>
    <th>
    date_von
    </th>
    <th>
    date_bis
    </th>
    <th>
    name_visitors
    </th>
    <th>
    notes
    </th>
    <th>
    max_visitors
    </th>
       <th>
    max_visitors_int
    </th>
  </tr>
  </thead>
  
<tbody>
  
<?php  
  foreach( $this->playgroundnotic as $key => $value )
  {
    
    //echo '<pre>'.print_r($value,true).'</pre';
    
    ?>
  <tr>
  <td>
    <input type="hidden" id="id" name="change_id[]" value="<?php echo $value->id;?>"  />
    <?php
    echo $value->id;
    ?>
    </td>

     <td>
       <input type="hidden" id="playground_id" name="change_playground_id[]" value="<?php echo $value->playground_id;?>"  />
    <?php
    echo $value->playground_id;
    ?>
    </td>

<td>
<?php
    $daysOfWeek = array(0 => Text::_('Nein'),
			                    1 => Text::_('Ja'));
			$dwOptions  = array();

			foreach ($daysOfWeek AS $key1 => $value1)
			{
				$dwOptions[] = HTMLHelper::_('select.option', $key1, $value1);
			}

	
				$lists['delete'] = HTMLHelper::_('select.genericlist', $dwOptions, 'change_delete[]', 'class="inputbox"', 'value', 'text', 0);
	
    echo $lists['delete'];
    ?>

	</td>
     <td>
              <input type="text" id="date_von" name="change_date_von[]" value="<?php echo sportsmanagementHelper::convertDate($value->date_von, 1);?>" />
    <?php
    //echo sportsmanagementHelper::convertDate($value->date_von, 1);
    ?>
    </td>

     <td>
       <input type="text" id="date_bis" name="change_date_bis[]" value="<?php echo sportsmanagementHelper::convertDate($value->date_bis, 1);?>" />
    <?php
    //echo sportsmanagementHelper::convertDate($value->date_bis, 1);
    ?>
    </td>
     <td>
       
       <input type="hidden" id="name_visitors" name="name_visitors[]" value="<?php echo $value->name_visitors;?>" /> 
    <?php
    
    echo $value->name_visitors;
    ?>
    </td>
     <td>
       <input type="text" id="notes" name="change_notes[]" value="<?php echo $value->notes;?>" />
    <?php
    //echo $value->notes;
    ?>
    </td>
     <td>
       <input type="text" id="max_visitors" name="change_max_visitors[]" value="<?php echo $value->max_visitors;?>" />
    <?php
    //echo $value->max_visitors;
    ?>
    </td>
    
    <td>
       <input type="text" id="max_visitors_int" name="change_max_visitors_int[]" value="<?php echo $value->max_visitors_int;?>" />
    <?php
    //echo $value->max_visitors;
    ?>
    </td>
  
  </tr>
 
    <?php  
  }
  
  
  ?>
  
  
  
  
  
  
  
  
  
  
  </tbody>  
</table>
