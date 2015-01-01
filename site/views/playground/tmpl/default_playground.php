<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); 
?>
<!-- <table width="96%" align="center" border="0" cellpadding="0" cellspacing="0" class="venueinfo"> -->
<table class="table">
	<tr class="sectiontableheader">
		<th colspan="2">
			<?php
			echo JText::_( 'COM_SPORTSMANAGEMENT_PLAYGROUND_DATA' );
			?>
		</th>
	</tr>
	<?php if (($this->config['show_shortname'])==1) { ?>
	<tr>
		<th class="td_r" width="30%">

				<?php
				echo JText::_( 'COM_SPORTSMANAGEMENT_PLAYGROUND_SHORT' );
				?>

		</th>
		<td width="70%">
			<?php
			echo $this->playground->short_name;
			?>
		</td>
	</tr>
	<?php } ?>

	<?php
	if ( ( $this->playground->address ) ||
		 ( $this->playground->zipcode ) )
	{
		?>
		<tr>
			<th class="td_r_t" width='30%'><?php echo JText::_( 'COM_SPORTSMANAGEMENT_PLAYGROUND_ADDRESS' ); ?></th>
			<td width='70%'>
				<?php
				echo JSMCountries::convertAddressString(	'',
														$this->playground->address,
														'',
														$this->playground->zipcode,
														$this->playground->city,
														$this->playground->country,
														'COM_SPORTSMANAGEMENT_PLAYGROUND_ADDRESS_FORM' );
				?>
			</td>
		</tr>
		<?php
	}
	?>

	<?php
	if ( $this->playground->website )
	{
		?>
		<tr>
			<th class="td_r" width="30%">
			   <?php echo JText::_( 'COM_SPORTSMANAGEMENT_PLAYGROUND_WEBSITE' ); ?>
			</th>
			<td>
				<?php
				echo JHtml::link( $this->playground->website, $this->playground->website, array( 'target' => '_blank' ) );
				?>
			</td>
		</tr>
		<?php
	}
	?>

	<?php
	if ( $this->playground->max_visitors )
	{
		?>
		<tr>
			<th class="td_r" width="30%">

					<?php
					echo JText::_( 'COM_SPORTSMANAGEMENT_PLAYGROUND_MAX_VISITORS' );
					?>

			</th>
			<td>
				<?php
				echo $this->playground->max_visitors;
				?>
			</td>
		</tr>
		<?php
	}
	?>
</table>
<br />
