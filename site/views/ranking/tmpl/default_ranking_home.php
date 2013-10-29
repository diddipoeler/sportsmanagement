<?php defined( '_JEXEC' ) or die( 'Restricted access' );?>

<!-- Main START -->
<a name="jl_top" id="jl_top"></a>

<!-- content -->
<?php
foreach ( $this->homeRank as $division => $cu_rk )
{
	if ($division)
	{
	?>
	<table width="96%" align="center" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td class="contentheading">
				<?php
					//get the division name from the first team of the division 
					foreach( $cu_rk as $ptid => $team )
					{
						echo $this->divisions[$division]->name;
						break;
					}
				?>
			</td>
		</tr>
	</table>

	<table width="96%" align="center" border="0" cellpadding="0" cellspacing="0">
	<?php
		foreach( $cu_rk as $ptid => $team )
		{
			echo $this->loadTemplate('rankingheading');
			break;
		}
		$this->division = $division;
		$this->current  = &$cu_rk;
		echo $this->loadTemplate('rankingrows');
	?>
	</table>
	<?php
	}
	else
	{
	?>
	<table width="96%" align="center" border="0" cellpadding="0" cellspacing="0">
		<?php
			echo $this->loadTemplate('rankingheading');
			$this->division = $division;
			$this->current  = &$cu_rk;
			echo $this->loadTemplate('rankingrows');
		?>
	</table>
	<br />
	<?php
	}
}
	?>
<!-- ranking END -->



