<?php 
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
 * @version   1.0.05
 * @file      default_playfield.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage player
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); 
?>

<table class="table table-responsive">
<tr>
<td width="50%">
<h2><?php echo '&nbsp;' . JText::_( 'COM_SPORTSMANAGEMENT_PERSON_PLAYFIELD' ); ?></h2>

<?php

if ( !isset($this->teamPlayer->position_name)  )
{
    $this->teamPlayer->position_name = 'hauptposition.png';
}	
				
$backimage = 'images/com_sportsmanagement/database/person_playground/' . $this->teamPlayer->position_name . '.png'; 					
$hauptimage = 'images/com_sportsmanagement/database/person_playground/hauptposition.png';
$nebenimage = 'images/com_sportsmanagement/database/person_playground/nebenposition.png';

if ( isset($this->person_position) )
{
?>
<div style="position:relative;height:170px;background-image:url(<?PHP echo $backimage;?>);background-repeat:no-repeat;">
<img src="<?PHP echo $hauptimage;?>" class="<?PHP echo $this->person_position;?>" alt="<?PHP echo $this->teamPlayer->position_name; ?>" title="<?PHP echo $this->teamPlayer->position_name; ?>" />

<?PHP


if ( isset($this->person_parent_positions) )
{

if ( is_array($this->person_parent_positions) )
{
foreach ( $this->person_parent_positions as $key => $value)
{
?>
<img src="<?PHP echo $nebenimage;?>" class="<?PHP echo $value;?>" alt="Nebenposition" title="Nebenposition" />
<?PHP
}
}
else
{
?>
<img src="<?PHP echo $nebenimage;?>" class="<?PHP echo $this->person_parent_positions;?>" alt="Nebenposition" title="Nebenposition" />
<?PHP
}

}
?>
</div>
<?PHP
}


?>
</td>
</tr>
</table>