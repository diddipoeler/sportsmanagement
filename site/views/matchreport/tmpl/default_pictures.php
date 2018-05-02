<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_picture.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage matchreport
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); 

$actualItems = count( $this->matchimages );
$setItems = count( $this->matchimages ) ;
$rssitems_colums = $this->config['show_pictures_columns'] ;
$pictures_width = $this->config['show_pictures_width'] ;

if ($setItems > $actualItems) {
			$totalItems = $actualItems;
		} else {
			$totalItems = $setItems;
		}
    
?>

<table class="table table-responsive">
<?php
$j = 0;
foreach ( $this->matchimages as $images )
{

if (($j % $rssitems_colums) == 0 ) : 
							$row = 'row'.(floor($j / $rssitems_colums) % $rssitems_colums) ;

?>
					<tr class="<?php echo $row; ?>">
					<?php endif; ?>
					<td class="item" style="width:<?php echo floor(99/$rssitems_colums)."%";?>">
          <a href="<?php echo $images->sitepath.DS.$images->name;?>" alt="<?php echo $images->name;?>" title="<?php echo $images->name;?>" class="highslide" onclick="return hs.expand(this)">
					<?php
echo sportsmanagementHelperHtml::getBootstrapModalImage($images->name,
$images->sitepath.DS.$images->name,
$images->name,
$pictures_width,
'',
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']);					
					?>
          </a>
					</td>
					<?php if (($j % $rssitems_colums) == ($rssitems_colums-1) ) : ?>
					</tr>
					<?php endif; ?>
					<?php						
						
$j++;
}
?>
</table>		