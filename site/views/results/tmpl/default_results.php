<?php 
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
 * @version   1.0.05
 * @file      default_results.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @subpackage results
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

if ( !isset ( $this->project ) )
{
	JError::raiseWarning( 'ERROR_CODE', JText::_( 'Error: ProjectID was not submitted in URL or selected project was not found in database!' ) );
}
else
{
	?>
	<div class="<?php echo COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS; ?>">
		<br />
		<?php
		if ( count( $this->matches ) > 0 )
		{
			switch ( $this->config['result_style'] )
			{
				case 4:
							{
								echo $this->loadTemplate('results_style_dfcday');
							}
							break;
        case 3:
							{
								echo $this->loadTemplate('results_style3');
							}
							break;

				case 0:
				case 1:
				case 2:				
				default:
							{
								echo $this->loadTemplate('results_style0');
							}
							break;
			}
		}
		?>
	</div>
	<!-- Main END -->
	<?php
	if ( $this->config['show_dnp_teams'] ) 
    { 
        echo $this->loadTemplate('freeteams'); 
        }
        
}
?>