<?php 
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      default_createdays.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage jlextdfbkeyimport
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

JHtml::_( 'behavior.tooltip' );

//// Set toolbar items for the page
//JToolbarHelper::title( JText::_( JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_MATCHDAY_INFO_1' ) ) );
//JToolbarHelper::save();

/*
echo '<pre>';
print_r($_POST);
echo '</pre>';
*/

//echo 'projekt ->'.$this->projectid.'<br>';

/*
echo '<pre>';
print_r($this->newmatchdays);
echo '</pre>';
*/

?>
<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm">
	<div id="editcell">
		<fieldset class="adminform">
			<legend>
				<?php
				echo JText::sprintf( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_MATCHDAY_INFO_2',  $this->projectid  );
				?>
			</legend>

<table class="adminlist">
<thead>
<tr>
<th class="title" nowrap="nowrap" style="vertical-align:top; ">
<?PHP echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_MATCHDAY_INFO_3' ); ?>
</th>
<th class="title" nowrap="nowrap" style="vertical-align:top; ">
<?PHP echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_MATCHDAY_INFO_4' ); ?>
</th>
<th class="title" nowrap="nowrap" style="vertical-align:top; ">
<?PHP echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_MATCHDAY_INFO_5' ); ?>
</th>
<th class="title" nowrap="nowrap" style="vertical-align:top; ">
<?PHP echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_MATCHDAY_INFO_6' ); ?>
</th>

</tr>
</thead>

<?PHP
$i=0;
foreach($this->newmatchdays as $rowdays) 
{
?>
<tr>
<input type="hidden" name="roundcode[]" value="<?php echo $rowdays->spieltag;?> " />
<td><?php echo $rowdays->spieltag;?></td>
<td> <input type="text" name="name[]" value="<?php echo $rowdays->spieltag.JText::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_MATCHDAY_INFO_7') ;?> " /> </td>

<td> 
<?php
$append = ' style="background-color:#bbffff;" ';
$date1 = '';
echo JHtml::calendar($date1,
		'round_date_first['.$i.']',
		'round_date_first['.$i.']',
		'%d-%m-%Y',
		'size="10" ' . $append .
		'onchange="document.getElementById(\'cb' . $i . '\').checked=true"' );

?>
</td>
<td>  

<?php
$append = ' style="background-color:#bbffff;" ';

echo JHtml::calendar($date1,
		'round_date_last['.$i.']',
		'round_date_last['.$i.']',
		'%d-%m-%Y',
		'size="10" ' . $append .
		'onchange="document.getElementById(\'cb' . $i . '\').checked=true"' );

?>


</td>

</tr>

<?PHP

$i++;
}
?>
</table>
</fieldset>
</div>

<fieldset class="actions">
						
							
</fieldset>
<input type="hidden" name="sent" value="1" />
<input type="hidden" name="projectid" value="<?php echo $this->projectid;?> " />
<input type="hidden" name="task" value="" />
<input type="hidden" name="divisionid" value="<?php echo $this->division_id;?> " />

               			
</form>

