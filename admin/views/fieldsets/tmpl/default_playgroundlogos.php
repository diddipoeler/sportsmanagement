<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fieldsets
 * @file       default_playgroundlogos.php
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
use Joomla\CMS\Form\Form;

echo 'Hier können Sie die Wappen zu den Stadien hinterlegen. <br>';


//echo JPATH_COMPONENT.'<br>';


$myForm = new Form('playgroundlogohistory');
$myForm->addFormPath(JPATH_COMPONENT . '/models/forms');
$myForm->loadFile('playgroundlogohistory', false);


//echo '<pre>'.print_r($myForm,true).'</pre>';

//echo $myForm->renderFieldset('picture');



?>
<table class="table">
  <tr>
<td>
    <?php
  echo $myForm->renderFieldset('picture');
  ?>
    </td>  
  
    <td>
     <?php
  echo $myForm->renderFieldset('seasons');
  ?>
    </td>
  </tr>
</table>
<script type="text/javascript">
        function addRows()
        {

 // Reference to the table body
var body = jQuery("#playgroundlogos").find('tbody');
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
<!--
<input type="button" value="Neues Bild hinzufügen" onclick="addRows()" />
-->

<table class="table table-striped" id="playgroundlogos">
  <thead>
    <tr>
    
  <th>
    id
    </th>
    <th>
    seasonname
    </th>
    <th>
    logo_big
    </th>
    
  </tr>
  </thead>
  
<tbody>
  
<?php  

  foreach( $this->logohistory as $key => $value )
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
    echo $value->seasonname;
    ?>
    </td>

     <td>
    <?php
    //echo $value->logo_big;
   echo sportsmanagementHelper::getBootstrapModalImage('collapseModallogo_big' . $value->id, Uri::root() . $value->logo_big, 'Logo', '20', Uri::root() . $value->logo_big);
    ?>
    </td>

   
    
    
    
  
  </tr>
 
    <?php  
  }
  
  
  ?>
  
  
  
  
  
  
  
  
  
  
  </tbody>  
</table>
