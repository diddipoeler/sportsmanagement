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

echo 'Hier können Sie die Notizen zum Stadion hinterlegen. Wie Name oder unterschiedliche Kapazitäten.';

?>

<table class="table table-striped">
  <thead>
    <tr>
    
  <th>
    id
    </th>
    <th>
    playground_id
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
  </tr>
  </thead>
  
<tbody>
  
<?php  
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
    echo $value->date_von;
    ?>
    </td>

     <td>
    <?php
    echo $value->date_bis;
    ?>
    </td>
     <td>
    <?php
    echo $value->name_visitors;
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
  
  
  ?>
  
  
  
  
  
  
  
  
  
  
  </tbody>  
</table>
