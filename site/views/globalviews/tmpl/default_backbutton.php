<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      deafault_backbutton.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage globalviews
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

if ( isset( $this->overallconfig['show_back_button'] ) )
{
	?>
	<br />
	<?php
	if ( $this->overallconfig['show_back_button'] == '1' )
	{
		$alignStr = 'left';
	}
	else
	{
		$alignStr = 'right';
	}
	if ( $this->overallconfig['show_back_button'] != '0' )
	{
	    	
	?>
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="text-align:<?php echo $alignStr; ?>; ">
    
		<div class="btn back_button">
			<a href='javascript:history.go(-1)'>
				<?php
				echo JText::_( 'COM_SPORTSMANAGEMENT_BACKBUTTON_BACK' );
				?>
			</a>
		</div>
	</div>
	<?php
	}
}
?>