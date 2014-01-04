<?php 
defined( '_JEXEC' ) or die( 'Restricted access' ); 

if ( $this->show_debug_info )
{
echo 'this->matchimages<br /><pre>~' . print_r($this->matchimages,true) . '~</pre><br />';
}

$actualItems = count( $this->matchimages );
$setItems    = count( $this->matchimages ) ;
$rssitems_colums = $this->config['show_pictures_columns'] ;
$pictures_width = $this->config['show_pictures_width'] ;

if ($setItems > $actualItems) {
			$totalItems = $actualItems;
		} else {
			$totalItems = $setItems;
		}
    
?>

<table cellpadding="0" cellspacing="0" class="moduletable<?php //echo $params->get('moduleclass_sfx'); ?>">
<?php
$j = 0;
foreach ( $this->matchimages as $images )
{
// echo JHTML::image($images->sitepath.DS.$images->name, $images->name , array('title' => $images->name ,'width' => "200" ));
// echo '<br>';

if (($j % $rssitems_colums) == 0 ) : 
// 						if ( $this->overallconfig['rssrow_alternate'] ) {
							$row = 'row'.(floor($j / $rssitems_colums) % $rssitems_colums) ;
// 						} else {
// 							$row = 'row0';
// 						}

?>
					<tr class="<?php echo $row; ?>">
					<?php endif; ?>
					<td class="item" style="width:<?php echo floor(99/$rssitems_colums)."%";?>">
          <a href="<?php echo $images->sitepath.DS.$images->name;?>" alt="<?php echo $images->name;?>" title="<?php echo $images->name;?>" class="highslide" onclick="return hs.expand(this)">
					<?php
echo JHTML::image($images->sitepath.DS.$images->name, $images->name , array('title' => $images->name ,'width' => $pictures_width ));					
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