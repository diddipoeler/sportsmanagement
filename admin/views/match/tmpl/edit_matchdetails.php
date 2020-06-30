<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage match
 * @file       edit_matchdetails.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;

//$this->document->addScript( Uri::root().'/media/system/js/mootools-core-uncompressed.js');
//$this->document->addScript( Uri::root().'/media/system/js/mootools-more-uncompressed.js');
//$this->document->addScript(Uri::root() . '/administrator/components/com_sportsmanagement/assets/js/sm_functions.js');

?>
<script type="text/javascript">
    (function () {
        // altered decision fields management
        //toggle_altdecision();
//	jQuery('#jform_alt_decision0').change(toggle_altdecision);
//    jQuery('#jform_alt_decision1').change(toggle_altdecision);
    });
var playgroundpicture = new Array;
			<?php
			foreach ($this->playgrounds as $key => $value)
			{
				if (!$value->playgroundpicture)
				{
					$value->playgroundpicture = sportsmanagementHelper::getDefaultPlaceholder("playground");
				}

				echo 'playgroundpicture[' . ($key) . ']=\'' . $value->playgroundpicture . "';\n";
			}
			?>
</script>
<?php
$this->document->addStyleDeclaration(
			'
img.item {
    padding-right: 10px;
    vertical-align: middle;
}
img.car {
    height: 25px;
}'
		);

// String $opt - second parameter of formbehavior2::select2
		// for details http://ivaynberg.github.io/select2/
		$opt = ' allowClear: true,
   width: "100%",

   formatResult: function format(state)
   {
   var originalOption = state.element;
   var picture;
   picture = playgroundpicture[state.id];
   if (!state.id)
   return state.text;
   return "<img class=\'item car\' src=\'' . Uri::root() . '" + picture + "\' />" + state.text;
   },
 
   escapeMarkup: function(m) { return m; }
';
$append = '';
HTMLHelper::_('formbehavior2.select2', '.test1', $opt);

?>
<fieldset class="adminform">
    <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_MD'); ?>
    </legend>
    <table class="admintable">
		<?php
echo $this->form->renderField('cancel');	    
echo $this->form->renderField('cancel_reason');

echo HTMLHelper::_(
				'select.genericlist', $this->playgrounds, 'playground_id',
				'style="width:225px;" class="test1" size="6"' . $append, 'value', 'text', 0
			);
	    
echo $this->form->renderField('overtime');	    
/*
		foreach ($this->form->getFieldset('matchdetails') as $field):
			?>
            <tr>
                <td class="key"><?php echo $field->label; ?></td>
                <td><?php echo $field->input; ?></td>
            </tr>
		<?php endforeach; 
	    */
	    ?>
    </table>
</fieldset>

<!-- Alt decision table START -->
<fieldset class="adminform">
    <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD'); ?>
    </legend>
    <table class='admintable'>
		<?php
		echo $this->form->renderField('count_result');
		echo $this->form->renderField('alt_decision');

		echo $this->form->renderField('decision_info');
		echo $this->form->renderField('team1_result_decision');
		echo $this->form->renderField('team2_result_decision');
		echo $this->form->renderField('team_won');

		foreach ($this->form->getFieldset('matchalternativ') as $field):
			?>
            <tr>

            </tr>
		<?php endforeach; ?>


        <tr>
            <td colspan="4">
                <!--
                            <div id="alt_decision_enter" style="display:<?php echo ($this->match->alt_decision == 0) ? 'none' : 'block'; ?>">
                                <table class='adminForm' cellpadding='0' cellspacing='7' border='0'>
                                    <tr>
                                        <td class="key"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_NEW_SCORE') . ' ' . $this->match->hometeam; ?></td>
                                        <td>
                                            <input    type="text" class="inputbox" id="team1_result_decision" name="team1_result_decision"
                                                    size="4"
                                                    value="<?php if ($this->match->alt_decision == 1)
				{
					if (isset($this->match->team1_result_decision))
					{
						echo $this->match->team1_result_decision;
					}
					else
					{
						echo 'X';
					}
				} ?>" <?php if ($this->match->alt_decision == 0)
				{
					echo 'DISABLED ';
				} ?>/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="key"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_NEW_SCORE') . ' ' . $this->match->awayteam; ?></td>
                                        <td>
                                            <input    type="text" class="inputbox" id="team2_result_decision" name="team2_result_decision"
                                                    size="4" value="<?php
				if ($this->match->alt_decision == 1)
				{
					if (isset($this->match->team2_result_decision))
					{
						echo $this->match->team2_result_decision;
					}
					else
					{
						echo 'X';
					}
				} ?>" <?php
				if ($this->match->alt_decision == 0)
				{
					echo 'DISABLED ';
				} ?>/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="key"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_REASON_NEW_SCORE'); ?></td>
            <?php
				if (is_null($this->match->team1_result) or ($this->match->alt_decision == 0))
				{
					$disinfo = 'DISABLED ';
				}
				?>
                                        <td>
                                            <input    type="text" class="inputbox" id="decision_info" name="decision_info" size="30"
                                                    value="<?php if ($this->match->alt_decision == 1)
				{
					echo $this->match->decision_info;
				} ?>" <?php
				if ($this->match->alt_decision == 0)
				{
					echo 'DISABLED ';
				} ?>/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="key"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_TEAM_WON'); ?></td>
                                        <td><?php echo $this->lists['team_won']; ?></td>
                                    </tr>
                                </table>
                            </div>
                            -->
            </td>
        </tr>
    </table>
</fieldset>
