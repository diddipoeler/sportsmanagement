<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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

// no direct access
defined('_JEXEC') or die('Restricted access'); ?>

<div style="direction: <?php echo $rssrtl ? 'rtl' :'ltr'; ?>; text-align: <?php echo $rssrtl ? 'right' :'left'; ?>">
<?php
$rssitems_colums = $this->overallconfig['rssitems_colums'] ;

foreach ($this->rssfeeditems as $feed) 
{
	if( $feed != false )
	{
		//image handling
		$iUrl 	= isset($feed->image->url)   ? $feed->image->url   : null;
		$iTitle = isset($feed->image->title) ? $feed->image->title : null;
		?>
		<table class="table table-striped">
		<?php
		// feed description
		if (!is_null( $feed->title ) && $this->overallconfig['rsstitle'] ) {
			?>
			<tr>
				<td>
					<div class="jefeedpro_heading_title">
					<?php if ( $this->overallconfig['rsstitle_linkable'] ) { ?>
						<a href="<?php echo str_replace( '&', '&amp', $feed->link ); ?>" target="<?php echo $this->overallconfig['link_target'] ?>">
						<?php echo $feed->title; ?></a>
					<?php } else { 
						echo $feed->title;
					 } ?>	
					</div>
				</td>
			</tr>
			<?php
		}
	
		// feed description
		if ( $this->overallconfig['rssdesc'] ) 
        {
		?>
			<tr>
				<td class="jefeedpro_heading_desc"><div class="jefeedpro_heading_desc"><?php echo $feed->description; ?></div></td>
				<?php if ( $this->overallconfig['rssimage'] && $iUrl) {?>
				<td align="center" class="jefeedpro_heading_image"><div class="jefeedpro_heading_image"><img src="<?php echo $iUrl; ?>" alt="<?php echo @$iTitle; ?>"/></div></td>
				<?php } ?>
			</tr>
			<?php
		}
	
		$actualItems = count( $feed->items );
		$setItems    = $this->overallconfig['rssitems'] ;
	
		if ($setItems > $actualItems) {
			$totalItems = $actualItems;
		} else {
			$totalItems = $setItems;
		}
		?>
		<tr>
			<td colspan="2">
				<table class="jefeedpro<?php //echo $params->get( 'moduleclass_sfx'); ?>">
				<?php
				$words = $this->overallconfig['word_count'] ;
				$word_tooltip = $this->overallconfig['tooltip_wordcount_desc'] ;

				for ($j = 0; $j < $totalItems; $j ++)
				{
					$currItem = & $feed->items[$j];
					// item title
					if (($j % $rssitems_colums) == 0 ) : 
						if ( $this->overallconfig['rssrow_alternate'] ) {
							$row = 'row'.(floor($j / $rssitems_colums) % $rssitems_colums) ;
						} else {
							$row = 'row0';
						}
					?>
					<tr class="<?php echo $row; ?>">
					<?php endif; ?>
					<td class="item" style="width:<?php echo floor(99/$rssitems_colums)."%";?>">
					<?php
					if ( !is_null( $currItem->get_link() ) ) {
						// Get tooltip description
						//$des_tooltip = ($word_tooltip == 0) ? $currItem->get_description() : modJeFeedHelper::limitText($currItem->get_description(),$word_tooltip); 		
						$des_tooltip	= $this->model->limitText($currItem->get_description(),$word_tooltip);

					?>
					<?php 
						if ( $this->overallconfig['rss_enable_tooltip'] && (!$this->overallconfig['rssitemdesc'])){
							$tooltip_content =  ' class="editlinktip hasTip" title="' . $currItem->get_title() . '::' . addslashes(htmlspecialchars($des_tooltip)) . '"';
						} else {
							$tooltip_content = '';
						}
					?>
						<span <?php echo $tooltip_content ?>><a href="<?php echo $currItem->get_link(); ?>" target="<?php echo $this->overallconfig['link_target']  ?>" rel="<?php echo $this->overallconfig['no_follow']  ?>" ><?php echo $currItem->get_title(); ?></a></span>
					<?php
					}
					// item description rssitemdesc
					if ( $this->overallconfig['rssitemdesc'] )
					{
						?>
						<div style="text-align: <?php echo $this->overallconfig['rssrtl'] ? 'right': 'left'; ?> !important">
							<?php echo  $des_tooltip ; ?>
						</div>
						<?php
					}
					?>
					</td>
					<?php if (($j % $rssitems_colums) == ($rssitems_colums-1) ) : ?>
					</tr>
					<?php endif; ?>
					<?php
				}
				?>
				</table>
			</td>
			</tr>
		</table>
	<?php 
	} 
} 
?>
</div>
