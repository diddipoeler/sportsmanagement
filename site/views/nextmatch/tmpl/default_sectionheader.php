<?php defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<!-- START: Contentheading -->
<table width="100%" class="contentpaneopen">
	<tr>
		<td class="contentheading"><?php
		echo JHtml::date($this->match->match_date, JText::_( 'COM_SPORTSMANAGEMENT_NEXTMATCH_GAMES_DATE' ) ). " ".
		sportsmanagementHelperHtml::showMatchTime($this->match, $this->config, $this->overallconfig, $this->project); 
		?></td>
	</tr>
</table>

<!-- END: Contentheading -->
