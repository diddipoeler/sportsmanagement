<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fieldsets
 * @file       default_clublogos.php
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

echo 'Hier können Sie die Wappen zu den Vereinen hinterlegen. <br>';

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
image.attr('type', 'picture');
//image.attr('id', 'plnotic_id');          
//image.attr('type', 'text');
//image.attr('name', 'plnotic_id[]');
//image.attr('disabled', 'disabled');
//image.attr('value', 'NEW');   

// Append the image to the column element
column.append(image);
// Append the column to the row element
row.append(column);





            
            
// Append the row to the table body
body.append(row);            
         }   
</script>            

<input type="button" value="Neues Wappen hinzufügen" onclick="addRows()" />

<table class="table table-striped" id="clublogos">
  <thead>
    <tr>
    
  <th>
    id
    </th>
    <th>
    club_id
    </th>
    <th>
    picture
    </th>
    
  </tr>
  </thead>
  
<tbody>
  
<?php  
/**
  foreach( $this->playgroundnotic as $key => $value )
  {
    ?>
  <tr>
  <td>
    <?php
    echo $value->id;
    ?>
    </td>

     <td>
    <?php
    echo $value->playground_id;
    ?>
    </td>

     <td>
    <?php
    //echo $value->date_von;
    echo sportsmanagementHelper::convertDate($value->date_von, 1)
    ?>
    </td>

     <td>
    <?php
    //echo $value->date_bis;
    echo sportsmanagementHelper::convertDate($value->date_bis, 1)
    ?>
    </td>
     <td>
    <?php
    echo $value->name_visitors;
    ?>
    </td>
     <td>
    <?php
    echo $value->notes;
    ?>
    </td>
     <td>
    <?php
    echo $value->max_visitors;
    ?>
    </td>
  
  </tr>
 
    <?php  
  }
  */
  
  ?>
  
  
  
  
  
  
  
  
  
  
  </tbody>  
</table>
