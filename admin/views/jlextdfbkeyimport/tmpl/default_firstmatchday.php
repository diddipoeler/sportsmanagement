<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

JHtml::_( 'behavior.tooltip' );

$url = 'components/com_joomleague/extensions/jlextdfbkey/admin/assets/images/dfb-key.jpg';
$alt = 'Lmo Logo';
// $attribs['width'] = '170px';
// $attribs['height'] = '26px';
$attribs['align'] = 'left';
$logo = JHtml::_('image', $url, $alt, $attribs);

//// Set toolbar items for the page
//JToolbarHelper::title( JText::_( JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_FIRST_MATCHDAY_INFO_1' ) ) );
//JToolbarHelper::apply();

/*
echo '<pre>';
print_r($_POST);
echo '</pre>';
*/

//$savedfb =& $this->import; 

/*
echo '<pre>';
print_r($this->import);
echo '</pre>';
*/

/*
echo '<pre>';
print_r($this->lists['dfbday']);
echo '</pre>';
*/

?>


<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm">
	<div id="editcell">
		<fieldset class="adminform">
			<legend>
				<?php
				echo JText::sprintf( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_FIRST_MATCHDAY_INFO_2', $this->dfbteams  );
				?>
			</legend>


	<table class='adminlist'>
			<thead>
      <tr>
      <th><?php echo JHtml::_('image', $url, $alt, $attribs);; ?>
      
      <?php echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_EDIT_LIST_DFBKEY_HINT11' ); ?>
      </th>
      </tr>
      </thead>
  </table>

			
<table class="<?php echo $this->table_data_class; ?>">
<thead>
<tr>
<th class="title" nowrap="nowrap" style="vertical-align:top; ">
<?PHP echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_FIRST_MATCHDAY_INFO_3' ); ?>
</th>
<th class="title" nowrap="nowrap" style="vertical-align:top; ">
<?PHP echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_FIRST_MATCHDAY_INFO_4' ); ?>
</th>
<th class="title" nowrap="nowrap" style="vertical-align:top; ">
<?PHP echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_FIRST_MATCHDAY_INFO_5' ); ?>
</th>
<th class="title" nowrap="nowrap" style="vertical-align:top; ">
<?PHP echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_FIRST_MATCHDAY_INFO_6' ); ?>
</th>
<th class="title" nowrap="nowrap" style="vertical-align:top; ">
<?PHP echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_FIRST_MATCHDAY_INFO_7' ); ?>
</th>
<th class="title" nowrap="nowrap" style="vertical-align:top; ">
<?PHP echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_FIRST_MATCHDAY_INFO_8' ); ?>
</th>
<th class="title" nowrap="nowrap" style="vertical-align:top; ">
<?PHP echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_FIRST_MATCHDAY_INFO_9' ); ?>
</th>
</tr>
</thead>
				
<?PHP

$startteamdfb = 0;
foreach($this->lists['dfbday'] as $rowdfb) 
{
echo "<tr><td>".$rowdfb->schluessel."</td>";
echo "<td>".$rowdfb->spieltag."</td>";

$teile = explode(",", $rowdfb->paarung);
echo "<td>".$teile[0]."</td>"; // Teil1
//echo "<td>".JHtml::_('select.genericlist', $this->lists['projectteams'], 'chooseteam_'.$teile[0] , 'class="inputbox" size="1"', 'value', 'text', $this->lists['projectteams'] )."</td>";
echo "<td>".JHtml::_('select.genericlist', $this->lists['projectteams'], 'chooseteam_'.$teile[0] , 'class="inputbox" size="1"', 'value', 'text', $startteamdfb )."</td>";

echo "<td>".$teile[1]."</td>"; // Teil2
//echo "<td>".JHtml::_('select.genericlist', $this->lists['projectteams'], 'chooseteam_'.$teile[1] , 'class="inputbox" size="1"', 'value', 'text', $this->lists['projectteams'] )."</td>";
echo "<td>".JHtml::_('select.genericlist', $this->lists['projectteams'], 'chooseteam_'.$teile[1] , 'class="inputbox" size="1"', 'value', 'text', $startteamdfb )."</td>";

echo "<td>".$rowdfb->spielnummer."</td></tr>";
}
?>

</table>
			
</fieldset>
	</div>

<fieldset class="actions">
							
							
						</fieldset>

<input type="hidden" name="sent"			value="1" />
<input type="hidden" name="option"			value="com_sportsmanagement" />
<input type="hidden" name="task"			value="apply" />
                			
</form>
<?php

?>