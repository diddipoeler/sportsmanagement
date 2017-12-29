<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_rssfeed.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 */ 

// no direct access
defined('_JEXEC') or die('Restricted access'); ?>

<?php
//$rssitems_colums = $this->overallconfig['rssitems_colums'] ;

foreach ($this->rssfeeditems as $feed) 
{
	if( $feed != false )
	{
		// Image handling
		$iUrl   = $feed->image ? $feed->image : null;
		$iTitle = $feed->imagetitle ? $feed->imagetitle : null;
		?>
		<div style="direction: <?php echo $rssrtl ? 'rtl' :'ltr'; ?>; text-align: <?php echo $rssrtl ? 'right' :'left'; ?> !important"  class="feed">
		<?php
		// Feed description
		if ( $feed->title !== null )
		{
			?>
					<h2 class="<?php echo $direction; ?>">
						<a href="<?php echo htmlspecialchars($rssurl, ENT_COMPAT, 'UTF-8'); ?>" target="_blank">
						<?php echo $feed->title; ?></a>
					</h2>
			<?php
		}
		// Feed description
		//if ($params->get('rssdesc', 1))
//		{
		?>
			<?php echo $feed->description; ?>
			<?php
		//}
		// Feed image
		if ( $iUrl ) :
		?>
			<img src="<?php echo $iUrl; ?>" alt="<?php echo @$iTitle; ?>">
		<?php endif; ?>


	<!-- Show items -->
	<?php if (!empty($feed))
	{ ?>
		<ul class="newsfeed">
		<?php for ($i = 0, $max = count($feed); $i < $max; $i++) { ?>
			<?php
				$uri   = (!empty($feed[$i]->uri) || $feed[$i]->uri !== null) ? trim($feed[$i]->uri) : trim($feed[$i]->guid);
				$uri   = strpos($uri, 'http') !== 0 ? $uri : $uri;
				$text  = !empty($feed[$i]->content) || $feed[$i]->content !== null ? trim($feed[$i]->content) : trim($feed[$i]->description);
				$title = trim($feed[$i]->title);
			?>
				<li>
					<?php if (!empty($uri)) : ?>
						<span class="feed-link">
						<a href="<?php echo htmlspecialchars($uri, ENT_COMPAT, 'UTF-8'); ?>" target="_blank">
						<?php echo $feed[$i]->title; ?></a></span>
					<?php else : ?>
						<span class="feed-link"><?php echo $title; ?></span>
					<?php endif; ?>

					<?php if ( !empty($text) ) : ?>
						<div class="feed-item-description">
						<?php
							// Strip the images.
							$text = JFilterOutput::stripImages($text);

							//$text = JHtml::_('string.truncate', $text, $params->get('word_count'));
							echo str_replace('&apos;', "'", $text);
						?>
						</div>
					<?php endif; ?>
				</li>
		<?php } ?>
		</ul>
	<?php } ?>
	</div>
	<?php



	} 
} 
?>

