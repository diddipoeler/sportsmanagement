<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage predictionproject
 * @file       edit_3.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

$templatesToLoad = array('footer', 'fieldsets');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);


$params = $this->form->getFieldsets('params');

// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();


?>
<script type="text/javascript">
    /*
	function change_published () {
	  if (document.adminForm.published0.checked == true) {
		var deaktiviert=true;
	  } else {
		var deaktiviert=false;
	  }
	  document.adminForm.mode.disabled=deaktiviert;
	  document.adminForm.overview.disabled=deaktiviert;
	  document.adminForm.joker0.disabled=deaktiviert;
	  document.adminForm.joker1.disabled=deaktiviert;
	  document.adminForm.joker_limit_select0.disabled=deaktiviert;
	  document.adminForm.joker_limit_select1.disabled=deaktiviert;
	  document.adminForm.champ0.disabled=deaktiviert;
	  document.adminForm.champ1.disabled=deaktiviert;
	
	  document.adminForm.points_correct_result.disabled=deaktiviert;
		document.adminForm.points_correct_result_joker.disabled=deaktiviert;
	  document.adminForm.points_correct_diff.disabled=deaktiviert;
		document.adminForm.points_correct_diff_joker.disabled=deaktiviert;
	  document.adminForm.points_correct_draw.disabled=deaktiviert;
		document.adminForm.points_correct_draw_joker.disabled=deaktiviert;
	  document.adminForm.points_correct_tendence.disabled=deaktiviert;
		document.adminForm.points_correct_tendence_joker.disabled=deaktiviert;
	  document.adminForm.points_tipp.disabled=deaktiviert;
		document.adminForm.points_tipp_joker.disabled=deaktiviert;
	
		document.adminForm.joker_limit.disabled=deaktiviert;
	
		document.adminForm.points_tipp_champ.disabled=deaktiviert;
	
	  if (deaktiviert == false){
	//  change_joker();
	  change_jokerlimit();
	 // change_champ();
	}
	}
	*/
    //function change_joker () {
    //  if (document.adminForm.joker0.checked == true) {
    //    var deaktiviert=true;
    //  } else {
    //    var deaktiviert=false;
    //  }
    //  alert(deaktiviert);
    //  document.adminForm.points_correct_result_joker.disabled=deaktiviert;
    //  document.adminForm.points_correct_diff_joker.disabled=deaktiviert;
    //  document.adminForm.points_correct_draw_joker.disabled=deaktiviert;
    //  document.adminForm.points_correct_tendence_joker.disabled=deaktiviert;
    //  document.adminForm.points_tipp_joker.disabled=deaktiviert;
    //}

    function change_jokerlimit() {
        if (document.adminForm.joker_limit_select0.checked == true) {
            var deaktiviert = true;
        } else {
            var deaktiviert = false;
        }
        document.adminForm.joker_limit.disabled = deaktiviert;
    }

    //function change_champ () {
    //  if (document.adminForm.champ0.checked == true) {
    //    var deaktiviert=true;
    //  } else {
    //    var deaktiviert=false;
    //  }
    //  document.adminForm.points_tipp_champ.disabled=deaktiviert;
    //  document.adminForm.league_champ.disabled=deaktiviert;
    //}

</script>

<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=' . $this->view . '&layout=edit&id=' . (int) $this->item->id . '&project_id=' . (int) $this->item->project_id); ?>"
      method="post" name="adminForm" id="adminForm" class="form-validate">

    <fieldset>
        <div class="fltrt">
            <button type="button" onclick="Joomla.submitform('predictionproject.store', this.form)">
				<?php echo Text::_('JSAVE'); ?></button>
            <!--
			<button id="cancel" type="button" onclick="<?php echo Factory::getApplication()->input->getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : ''; ?>  window.parent.SqueezeBox.close();">
				<?php echo Text::_('JCANCEL'); ?></button>
			-->
        </div>
    </fieldset>

    <div class="form-horizontal">
		<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?PHP
		foreach ($fieldsets as $fieldset)
		{
			echo HTMLHelper::_('bootstrap.addTab', 'myTab', $fieldset->name, Text::_($fieldset->label, true));

			switch ($fieldset->name)
			{
				case 'details':
					echo $this->form->renderFieldset('details');
					break;
				case 'predchamp':
					echo $this->form->renderFieldset('predchamp');
					break;
				case 'predfinal4':
					echo $this->form->renderFieldset('predfinal4');
					break;
				case 'predjoker':
					echo $this->form->renderFieldset('predjoker');
					break;

				default:
					$this->fieldset = $fieldset->name;
					echo $this->loadTemplate('fieldsets');
					break;
			}

			echo HTMLHelper::_('bootstrap.endTab');
		}

		?>

		<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
    </div>

    <div>
        <input type='hidden' name='id' value='<?php echo $this->item->id; ?>'/>
        <input type='hidden' name='task' value='predictionproject.edit'/>
        <input type='hidden' name='psapply' value='1'/>
    </div>
	<?php
	echo HTMLHelper::_('form.token');

	?>
</form>
<script type="text/javascript">
    //change_published();
</script>

	<?PHP
	echo $this->loadTemplate('footer');
	?>

