<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_article.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage matchreport
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); 
?>

<!-- START of match summary -->
<?php
if (!empty($this->match_article->introtext))
{
    
	?>
	<table class="table table-responsive" >
		<tr>
			<td class="contentheading">
				<?php
				echo '&nbsp;' . JText::_( 'COM_SPORTSMANAGEMENT_MATCHREPORT_MATCH_ARTICLE' );
				?>
			</td>
		</tr>
	</table>
	<table class="table table-responsive" >
		<tr>
			<td>
			<?php
			$summary = $this->match_article->introtext;
			$summary = JHtml::_('content.prepare', $summary);

			echo $summary;

			?>
			</td>
		</tr>
	</table>
	<?php
}

?>
<!-- END of match summary -->

