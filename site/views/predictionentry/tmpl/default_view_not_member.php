<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_view_not_member.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage predictionentry
 */

defined('_JEXEC') or die(Text::_('Restricted access'));
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
$visible = 'text';
}
else
{
$visible = 'hidden';
}

?><p><?php
	echo Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_NOT_MEMBER_INFO_01');
	?></p><p><?php
	echo Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_NOT_MEMBER_INFO_02');
	?></p><p><?php
	echo Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_NOT_MEMBER_INFO_03',$this->config['ownername'],'<b>'.$this->websiteName.'</b>');
	?></p><p>&nbsp;</p><p><?php
	if ($this->isNotApprovedMember==1)
	{
		echo '<span class="button">'.Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_NOT_MEMBER_INFO_04').'</span>';
		?></p><p><?php
		echo Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_NOT_MEMBER_INFO_05');
		?></p><?php
	}
	else
	{
		echo Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_NOT_MEMBER_INFO_06');
		?>
    </p>
    <?php
			// <form name='predictionRegisterForm' id='predictionRegisterForm' method='post' >
      // <form name="adminForm" id="adminForm" method="post" action="index.php"> 
      ?>
      
      <form name='predictionRegisterForm' id='predictionRegisterForm' method='post' action="<?php echo Route::_('index.php?option=com_sportsmanagement&task=predictionentry.register'); ?>" >
			<input type='submit' name='register' value='<?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_NOT_MEMBER_INFO_07') ; ?>' class='button' />
			<input type='<?php echo $visible; ?>' name='prediction_id' value='<?php echo $this->predictionGame->id; ?>' />
			<input type='<?php echo $visible; ?>' name='user_id' value='<?php echo $this->actJoomlaUser->id; ?>' />
			<input type='<?php echo $visible; ?>' name='approved' value='<?php echo ( $this->predictionGame->auto_approve ) ? '1' : '0'; ?>' />
			<input type='<?php echo $visible; ?>' name='task' value='predictionentry.register' />
			<input type='<?php echo $visible; ?>' name='option'	value='com_sportsmanagement' />
			
			<?php echo HTMLHelper::_('form.token'); ?>
		</form><?php
	}
	?></p><br />