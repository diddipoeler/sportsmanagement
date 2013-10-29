<?php defined('_JEXEC') or die('Restricted access');
?>
<!-- matchdays pageNav -->
<br />
<table width='96%' align='center' cellpadding='0' cellspacing='0' border='0'>
	<tr>
		<td>
			<?php
			if (!empty($this->rounds))
			{
				$pageNavigation  = "<div class='pagenav'>";
				$pageNavigation .= JoomleaguePagination::pagenav($this->project);
				$pageNavigation .= "</div>";
				echo $pageNavigation;
			}
			?>
		</td>
	</tr>
</table>
<!-- matchdays pageNav END -->