<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_playground.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @subpackage playground
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); 
?>
<div class="row-fluid">
<table class="table">
	<tr class="">
		<th colspan="2">
			<?php
			echo JText::_( 'COM_SPORTSMANAGEMENT_PLAYGROUND_DATA' );
			?>
		</th>
	</tr>
	<?php if (($this->config['show_shortname'])==1) { ?>
	<tr>
		<th class="" width="">

				<?php
				echo JText::_( 'COM_SPORTSMANAGEMENT_PLAYGROUND_SHORT' );
				?>

		</th>
		<td width="">
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
			<th class="" width=''><?php echo JText::_( 'COM_SPORTSMANAGEMENT_PLAYGROUND_ADDRESS' ); ?></th>
			<td width=''>
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
			<th class="" width="">
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
			<th class="" width="">

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
</div>	
<br />
