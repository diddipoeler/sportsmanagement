<?php defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<?php if (!empty($this->rounds)): ?>
<table class="not-playing" width="96%" align="center" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td style="text-align:left; ">
			<?php echo $this->showNotPlayingTeams($this->matches, $this->teams, $this->config, $this->favteams, $this->project); ?>
		</td>
	</tr>
</table>
<?php endif; ?>