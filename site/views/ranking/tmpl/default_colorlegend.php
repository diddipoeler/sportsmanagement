<?php defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<!-- colors legend START -->
<?php
	if (!isset($this->tableconfig['show_colors_legend'])){$this->tableconfig['show_colors_legend']=1;}
	if ($this->tableconfig['show_colors_legend'])
	{
		?>
		<br />
		<table width='96%' align='center' cellpadding='0' cellspacing='0' border='0'>
			<tr>
				<?php
				JoomleagueHelper::showColorsLegend($this->colors);
				?>
			</tr>
		</table>
		<?php
	}
?>
<!-- colors legend END -->