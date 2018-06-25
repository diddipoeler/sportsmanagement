<?php 
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      default_savematchdays.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage jlextdfbkeyimport
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

JHtml::_( 'behavior.tooltip' );

// Set toolbar items for the page
//JToolbarHelper::title( JText::_( JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_SAVE_MATCHDAY_INFO_1' ) ) );
//JToolbarHelper::save();
//JToolbarHelper::addNewX();
//JToolbarHelper::apply( 'insert' );

/*
echo '<pre>';
print_r($_POST);
echo '</pre>';
*/

/*
echo '<pre>';
print_r($this->import);
echo '</pre>';
*/

?>

<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm">
	<div id="editcell">
		<fieldset class="adminform">
			<legend>
				<?php
				echo JText::sprintf( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_SAVE_MATCHDAY_INFO_2', $this->projectid  );
				?>
			</legend>

<table class="<?php echo $this->table_data_class; ?>">
<thead>
<tr>
<th class="title" nowrap="nowrap" style="vertical-align:top; ">
<?PHP echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_SAVE_MATCHDAY_INFO_3' ); ?>
</th>
<th class="title" nowrap="nowrap" style="vertical-align:top; ">
<?PHP echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_SAVE_MATCHDAY_INFO_4' ); ?>
</th>
<th class="title" nowrap="nowrap" style="vertical-align:top; ">
<?PHP echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_SAVE_MATCHDAY_INFO_5' ); ?>
</th>
<th class="title" nowrap="nowrap" style="vertical-align:top; ">
<?PHP echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_SAVE_MATCHDAY_INFO_6' ); ?>
</th>

<th class="title" nowrap="nowrap" style="vertical-align:top; ">
<?PHP echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_SAVE_MATCHDAY_INFO_7' ); ?>
</th>


</tr>
</thead>


<?PHP
$i=0;
foreach($this->import as $rowdays) 
{
?>
<tr>
<input type="hidden" name="round_id[]" value="<?php echo $rowdays->round_id;?> " />
<input type="hidden" name="roundcode[]" value="<?php echo $rowdays->spieltag;?> " />
<td><?php echo $rowdays->spieltag;?></td>

<input type="hidden" name="match_number[]" value="<?php echo $rowdays->spielnummer;?> " />
<td><?php echo $rowdays->spielnummer;?></td>

<input type="hidden" name="projectteam1_id[]" value="<?php echo $rowdays->projectteam1_id;?> " />
<td><?php echo $rowdays->projectteam1_name;?></td>

<input type="hidden" name="projectteam2_id[]" value="<?php echo $rowdays->projectteam2_id;?> " />
<td><?php echo $rowdays->projectteam2_name;?></td>

<td> 
<?php
$date1 =  JFactory::getDate( $rowdays->match_date)->format( '%d-%m-%Y' );
$append = ' style="background-color:#bbffff;" ';

echo JHtml::calendar(	$date1,
			'match_date['.$i.']',
			'match_date['.$i.']',
			'%d-%m-%Y',
			'size="10" ' . $append .
			'onchange="document.getElementById(\'cb' . $i . '\').checked=true"' );

?>
</td>


</tr>
<?PHP

$i++;
}

/*
<input type="hidden" name="controller"	value="dfbkeys" />
<input type="hidden" name="view"	value="dfbkeys" /> 
<input type="hidden" name="task"			value="addNewX" />
*/
?>


</table>
</fieldset>
</div>

<fieldset class="actions">
						
							
</fieldset>
<input type="hidden" name="sent"			value="3" />
<input type="hidden" name="projectid"			value="<?php echo $this->projectid;?> " />
<input type="hidden" name="task"			value="" />
</form>
		   
