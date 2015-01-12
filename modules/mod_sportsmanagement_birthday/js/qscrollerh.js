<script type="text/javascript">
	window.addEvent('domready', function() {
	var opt<?php echo $params->get( 'moduleclass_sfx' ); ?> = {
	  duration: 3000,
	  delay: <?php echo $tickerpause ?>000,
	  auto:true,
	  direction: 'h',
	  onMouseEnter: function(){this.stop();},
	  onMouseLeave: function(){this.play();}
	};
	var scroller<?php echo $params->get( 'moduleclass_sfx' ); ?> = new QScroller('qscroller<?php echo $params->get( 'moduleclass_sfx' ); ?>',opt<?php echo $params->get( 'moduleclass_sfx' ); ?>);
	scroller<?php echo $params->get( 'moduleclass_sfx' ); ?>.load();
	});
</script>
