<?php 
/**
* @copyright	Copyright (C) 2007-2012 JoomLeague.net. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

JHTML::_( 'behavior.tooltip' );
jimport('joomla.html.pane');
//$uri =& JFactory::getURI();
$edit = JRequest::getVar( 'edit', true );

// Set toolbar items for the page
JToolBarHelper::save();

if ( !$edit )
{
	JToolBarHelper::title( JText::_( 'JL_ADMIN_PTMPL_ADD_TITLE' ) );
	JToolBarHelper::divider();
	JToolBarHelper::cancel();
}
else
{
	JToolBarHelper::title( JText::_( 'JL_ADMIN_PTMPL_EDIT_TITLE' ) );

	// for existing items the button is renamed `close` and the apply button is showed
	JToolBarHelper::apply();
	JToolBarHelper::divider();
	JToolBarHelper::cancel( 'cancel', 'JL_GLOBAL_CLOSE' );
}
JToolBarHelper::divider();
JLToolBarHelper::onlinehelp();
$pane =& JPane::getInstance('tabs'); 
$i = 1;
?>
<form method="post" name="adminForm" id="adminForm">
	<div class="col50">
		<fieldset class="adminform">
			<legend>
				<?php
					echo JText::sprintf(	'JL_ADMIN_PTMPL_TITLE',
											'<i>' . $this->params->name . '</i>',
											'<i>' . $this->predictionGame->name . '</i>' );
				?>
			</legend>
			<fieldset class="adminform">
				<?php
				echo JText::_( $this->params->description );
				?>
			</fieldset>
	<?php echo $pane->startPane( 'pane' ); ?>

	  <?php foreach ($this->params->getGroups() as $key => $groups): ?>
		  <?php if (strtolower($key) != '_default'): ?>
			  <?php echo $pane->startPanel( JText::_( $key ), 'panel'.$i++ ); ?>
					<?php echo $this->params->render('params', $key); ?>
				<?php $pane->endPanel(); ?>
		  <?php endif; ?> 
	  <?php endforeach; ?>
	  
	<?php echo $pane->endPane(); ?>
<?php /* ?>
			<div>&nbsp;</div>
			<table class="admintable">
				<tr>
					<td nowrap='nowrap' class='key' style='text-align:right;'>
						<label for="name">
							<?php
							echo JText::_( 'Prediction Name' );
							?>
						</label>
					</td>
					<td>
						<input  class="text_area" type="text" name="name" id="title" size="80" maxlength="75"
								value="<?php echo $this->prediction->name; ?>" />
					</td>
				</tr>
				<tr>
					<td nowrap='nowrap' class='key' style='text-align:right;'>
						<label for="admins">
							<?php
							echo JText::_( 'Administrator(s)' );
							echo '<br />';
							echo JText::sprintf( '%1$s admin(s) assigned', count( $this->pred_admins ) );
							?>
						</label>
					</td>
					<td>
						<?php
						echo $this->lists['jl_users'];
						?>
					</td>
				</tr>
				<tr>
					<td nowrap='nowrap' class='key' style='text-align:right;'>
		 				<label for="league">
							<?php
							echo JText::_( 'Assigned project(s)' );
							echo '<br />';
							echo JText::sprintf( '%1$s project(s) assigned', count( $this->pred_projects ) );
							?>
						</label>
					</td>
					<td>
						<?php
						echo $this->lists['projects'];
						?>
					</td>
				</tr>
				<tr>
					<td nowrap='nowrap' class='key' style='text-align:right;'>
		 				<label for="auto_activate_user">
							<?php
							echo JText::_( 'Automatically activate user?' );
							?>
						</label>
					</td>
					<td>
						<?php
						echo $this->lists['auto_activate_user'];
						?>
					</td>
				</tr>
				<tr>
					<td nowrap='nowrap' class='key' style='text-align:right;'>
		 				<label for="auto_activate_user">
							<?php
							echo JText::_( 'Tipp only favourite teams?' );
							?>
						</label>
					</td>
					<td>
						<?php
						echo $this->lists['only_favteams'];
						?>
					</td>
				</tr>
				<tr>
					<td class='key' style='text-align:right;'>
		 				<label for="auto_activate_user">
							<?php
							echo JText::_( 'Allow Tipp-Admins to enter predictions for other users?' );
							?>
						</label>
					</td>
					<td>
						<?php
						echo $this->lists['only_favteams'];
						?>
					</td>
				</tr>
			</table>
<?php */ ?>
		</fieldset>

		<div class='clr'></div>
		<input type='hidden' name='option'		value='com_joomleague' />
		<input type='hidden' name='controller'	value='predictiontemplate' />
		<input type='hidden' name='task'		value='' />
		<input type='hidden' name='user_id'		value='<?php echo $this->user->id; ?>' />
		<input type='hidden' name='cid[]'		value='<?php echo $this->predictionTemplate->id; ?>' />
		<input type='hidden' name='title'		value='<?php echo $this->params->name; ?>' />
		<input type='hidden' name='template'	value='<?php echo $this->predictionTemplate->template; ?>' />
	</div>
<?php echo JHTML::_( 'form.token' ); ?>
</form>