<?php


defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
$params = $this->form->getFieldsets('params');

#echo '#<pre>'; print_r($this->rosters); echo '</pre>#';


?>
<script type="text/javascript">
<!--
var matchid = <?php echo $this->teams->id; ?>;

var projecttime=<?php echo $this->eventsprojecttime; ?>;
var baseajaxurl='<?php echo JUri::root();?>administrator/index.php?option=com_sportsmanagement&<?php echo JUtility::getToken() ?>=1';
var homeroster = new Array;


<form action="<?php echo JRoute::_('index.php?option=com_sportsmanagement&task=match.edit&tmpl=component'); ?>" id="component-form" method="post" name="adminForm" >
<?php




$i = 0;
foreach ($this->rosters['home'] as $player)
{
	$obj = new stdclass();
	$obj->value = $player->value;
	$obj->text  = sportsmanagementHelper::formatName(null, $player->firstname, $player->nickname, $player->lastname, 14) .' - ('.JText::_($player->positionname).')';
	echo 'homeroster['.($i++).']='.json_encode($obj).";\n";
}
?>
var awayroster = new Array;
<?php
$i = 0;
foreach ($this->rosters['away'] as $player)
{
	$obj = new stdclass();
	$obj->value = $player->value;
	$obj->text  = sportsmanagementHelper::formatName(null, $player->firstname, $player->nickname, $player->lastname, 14) .' - ('.JText::_($player->positionname).')';
	echo 'awayroster['.($i++).']='.json_encode($obj).";\n";
}
?>
var rosters = Array(homeroster, awayroster);
var str_delete = "<?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_DELETE'); ?>";

//-->
</script>
<div id="gamesevents">
	<fieldset>
		<div class="fltrt">
			<button id="cancel" type="button" onclick="<?php echo JRequest::getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
				<?php echo JText::_('JCANCEL');?></button>
		</div>
		<div class="configuration" >
			<?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_TITLE', $this->teams->team1, $this->teams->team2); ?>
		</div>
	</fieldset>
	
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_DESCR'); ?></legend>
			<!-- Don't remove this "<div id"ajaxresponse"></div> as it is neede for ajax changings -->
			<div id="ajaxresponse"></div>
			<table class='adminlist'>
				<thead>
					<tr>
						<th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_TEAM'); ?></th>
						<th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_PLAYER'); ?></th>
						<th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_EVENT'); ?></th>
						<th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_VALUE_SUM'); ?></th>
						<th>
							<?php
							echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_TIME');
							#echo JText::_('Hrs') . ' ' . JText::_('Mins') . ' ' . JText::_('Secs');
							?>
						</th>
						<th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_MATCH_NOTICE'); ?></th>
						<th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_EVENT_ACTION'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$k=0;
					if (isset($this->matchevents))
					{
						foreach ($this->matchevents as $event)
						{
							if ($event->event_type_id != 0) {
							?>
							<tr id="row-<?php echo $event->id; ?>" class="<?php echo "row$k"; ?>">
								<td><?php echo $event->team; ?></td>
								<td>
								<?php
								// TODO: now remove the empty nickname quotes, but that should probably be solved differently
								echo preg_replace('/\'\' /', "", $event->player1);
								?>
								</td>
								<td style='text-align:center; ' ><?php echo JText::_($event->event); ?></td>
								<td style='text-align:center; ' ><?php echo $event->event_sum; ?></td>
								<td style='text-align:center; ' ><?php echo $event->event_time; ?></td>
								<td title="" class="hasTip">
									<?php echo (strlen($event->notice) > 20) ? substr($event->notice, 0, 17).'...' : $event->notice; ?>
								</td>
								<td style='text-align:center; ' >
									<input	id="delete-<?php echo $event->id; ?>" type="button" class="inputbox button-delete"
											value="<?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_DELETE'); ?>" />
								</td>
							</tr>
							<?php
							}
						$k=1 - $k;
						}
					}
					?>
					<tr id="row-new">
						<td><?php echo $this->lists['teams']; ?></td>
						<td id="cell-player">&nbsp;</td>
						<td><?php echo $this->lists['events']; ?></td>
						<td style='text-align:center; ' ><input type="text" size="3" value="" id="event_sum" name="event_sum" class="inputbox" /></td>
						<td style='text-align:center; ' ><input type="text" size="3" value="" id="event_time" name="event_time" class="inputbox" /></td>
						<td style='text-align:center; ' ><input type="text" size="20" value="" id="notice" name="notice" class="inputbox" /></td>
                        
                        
                        
						<td style='text-align:center; ' >
							<?php echo JHtml::_('form.token'); ?>
							<input id="save-new" type="button" class="inputbox button-save" value="<?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SAVE'); ?>" />
						</td>
					</tr>
				</tbody>
			</table>
			
			<br>
		</fieldset>
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_LIVE_COMMENTARY_DESCR'); ?></legend>		
		<table class='adminlist' >
			<thead>
				<tr>
					<th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_LIVE_TYPE' ); ?></th>
					<th>
						<?php
						echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_TIME' );
						#echo JText::_( 'Hrs' ) . ' ' . JText::_( 'Mins' ) . ' ' . JText::_( 'Secs' );
						?>
					</th>
					<th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_LIVE_NOTES' ); ?></th>
					<th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_EVENT_ACTION' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				$k=0;
				if ( isset( $this->matchcommentary ) )
				{
					foreach ( $this->matchcommentary as $event )
					{
						
						?>
						<tr id="rowcomment-<?php echo $event->id; ?>" class="<?php echo "row$k"; ?>">
							<td>
								<?php 
								switch ($event->type) {
                                    case 2:
                                        echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_LIVE_TYPE_2' );
                                        break;
                                    case 1:
                                        echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_LIVE_TYPE_1' );
                                        break;
		                        } ?>
							</td>

							<td style='text-align:center; ' >
								<?php
								echo $event->event_time;
								?>
							</td>
							<td title='' class='hasTip' style="width: 500px;">
								<?php
								echo $event->notes;
								?>
							</td>
							<td style='text-align:center; ' >
								<input	id="deletecomment-<?php echo $event->id; ?>" type="button" class="inputbox button-delete-commentary"
										value="<?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_DELETE' ); ?>" />
							</td>
						</tr>
						<?php
						
					$k=1 - $k;
					}
				}
				?>
				<tr id="rowcomment-new">

					<td>
						<select name="ctype" id="ctype" class="inputbox select-commenttype">
                            <option value="1"><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_LIVE_TYPE_1' ); ?></option>
                            <option value="2"><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EE_LIVE_TYPE_2' ); ?></option>
                        </select> 
					</td>
					<td style='text-align:center; ' >
						<input type="text" size="3" value="" id="c_event_time" name="c_event_time" class="inputbox" />
					</td>
					<td style='text-align:center; ' >
						<textarea rows="2" cols="70" id="notes" name="notes" ></textarea>
					</td>
					<td style='text-align:center; ' >
						<input id="save-new-comment" type="button" class="inputbox button-save-c" value="<?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SAVE' ); ?>" />
					</td>
				</tr>
			</tbody>
		</table>
			
		</fieldset>
</div>
<div style="clear: both"></div>

<input type="hidden" name="task" value="match.edit"/>
		<input type="hidden" name="close" id="close" value="0"/>
        <input type="hidden" name="id" id="close" value="<?php echo $this->item->id; ?>"/>
		<input type="hidden" name="component" value="" />
		<?php echo JHTML::_('form.token')."\n"; ?>

</form>
