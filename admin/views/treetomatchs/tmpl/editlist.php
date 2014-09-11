<?php defined( '_JEXEC' ) or die( 'Restricted access' );

JHtml::_( 'behavior.tooltip' );

// Set toolbar items for the page
//$edit = JRequest::getVar('edit',true);

JToolBarHelper::title( JText::_( 'COM_JOOMLEAGUE_ADMIN_TREETOMATCH_ASSIGN' ) );

JLToolBarHelper::save( 'treetomatch.save_matcheslist' );

// for existing items the button is renamed `close` and the apply button is showed
//JLToolBarHelper::cancel( 'cancel', 'COM_JOOMLEAGUE_GLOBAL_CLOSE' );
JToolBarHelper::back('Back','index.php?option=com_joomleague&view=treetonodes&task=treetonode.display');

JToolBarHelper::help( 'screen.joomleague', true );
$uri = JFactory::getURI();
?>
<!-- import the functions to move the events between selection lists  -->
<?php
$version = urlencode(JoomleagueHelper::getVersion());
echo JHtml::script( 'JL_eventsediting.js?v='.$version,'administrator/components/com_joomleague/assets/js/');
?>
<script>
	function submitbutton(pressbutton)
	{
		var form = $('adminForm');
		if (pressbutton == 'cancel')
		{
			submitform( pressbutton );
			return;
		}
		var mylist = document.getElementById('node_matcheslist');
		for(var i=0; i<mylist.length; i++)
		{
			  mylist[i].selected = true;
		}
		submitform( pressbutton );
	}
</script>

<style type="text/css">
	table.paramlist td.paramlist_key {
		width: 92px;
		text-align: left;
		height: 30px;
	}
</style>

<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm">
	<div class="col50">
		<fieldset class="adminform">
			<legend>
				<?php
				echo JText::sprintf( 'COM_JOOMLEAGUE_ADMIN_TREETOMATCH_ASSIGN_TITLE', '<i>' . $this->projectws->name . '</i>');
				?>
			</legend>
			<table class="admintable" border="0">
				<tr>
					<td>
						<b>
							<?php
							echo JText::_( 'COM_JOOMLEAGUE_ADMIN_TREETOMATCH_ASSIGN_AVAIL_MATCHES' );
							?>
						</b><br />
						<?php
						echo $this->lists['matches'];
						?>
					</td>
					<td style="text-align:center; ">
						&nbsp;&nbsp;
						<input	type="button" class="inputbox"
								onclick="handleLeftToRight();"
								value="&gt;&gt;" />
						&nbsp;&nbsp;<br />&nbsp;&nbsp;
					 	<input	type="button" class="inputbox"
					 			onclick="handleRightToLeft();"
								value="&lt;&lt;" />
						&nbsp;&nbsp;
					</td>
					<td>
						<b>
							<?php
							echo JText::_( 'COM_JOOMLEAGUE_ADMIN_TREETOMATCH_ASSIGN_NODE_MATCHES' );
							?>
						</b><br />
						<?php
						echo $this->lists['node_matches'];
						?>
					</td>
			   </tr>
			</table>
		</fieldset>
		<div class="clr"></div>

		<input type="hidden" name="matcheschanges_check"	value="0"	id="matcheschanges_check" />
		<input type="hidden" name="option"				value="com_joomleague" />
		<input type="hidden" name="cid[]"				value="<?php echo $this->nodews->id; ?>" />
		<input type="hidden" name="task"				value="treetomatch.save_matcheslist" />
	</div>
</form>