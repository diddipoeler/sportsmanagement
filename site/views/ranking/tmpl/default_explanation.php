<?php defined('_JEXEC') or die('Restricted access');

$config   = &$this->tableconfig;

$columns = explode( ",", $config['ordered_columns'] );
$column_names	= explode( ',', $config['ordered_columns_names'] );
?>

<br />
<table width="96%" align="center" border="0" cellpadding="0" cellspacing="0">
	<tr class="explanation">
		<td>
		<?php
		$d = 0;
		foreach (  $columns as $k => $column)
		{
			if (empty($column_names[$k])){$column_names[$k]='???';}	
			$c=strtoupper(trim($column));
			$c="COM_JOOMLEAGUE_".$c;
			echo "<td class=\"col$d\">";
			echo $column_names[$k] ." = ".JText::_($c) ;
			echo "</td>";
			$d=(1-$d);
		}
		?>
		</td>
	</tr>
</table>