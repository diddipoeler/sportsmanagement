<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_description.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage player
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); 
?>
<!-- Team Player Description START -->
<?php
	$description = "";
	if ( isset($this->teamPlayer) && !empty($this->teamPlayer->notes) )
	{
		$description = $this->teamPlayer->notes;
	}
	else
	{
		if ( !empty($this->person->notes) )
		{
			$description = $this->person->notes;
		}
	}

	if ( !empty($description) )
	{
		?>
		<h2><?php echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_INFO' );	?></h2>
		<div class="personinfo">
			<?php	
			$description = JHtml::_('content.prepare', $description);
			echo stripslashes( $description ); 
			?>
		</div>
		<?php
	}
	?>
<!-- Team Player Description END -->