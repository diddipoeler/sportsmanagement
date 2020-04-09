<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage projectteam
 * @file       edit_3.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$templatesToLoad = array('footer', 'fieldsets');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);


$params = $this->form->getFieldsets('params');

// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();

?>
    <!-- import the functions to move the events between selection lists	-->
<?php


?>
    <form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=' . $this->view . '&layout=edit&id=' . (int) $this->item->id); ?>"
          method="post" id="adminForm" name="adminForm" class="form-validate">

        <div class="form-horizontal">
			<?php

			if ($this->change_training_date)
			{
				echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'training'));
			}
			else
			{
				echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'details'));
			}

			?>

			<?PHP
			foreach ($fieldsets as $fieldset)
			{
				echo HTMLHelper::_('bootstrap.addTab', 'myTab', $fieldset->name, Text::_($fieldset->label, true));

				switch ($fieldset->name)
				{
					case 'details':
						?>
                        <div class="row-fluid">
                            <div class="span9">
                                <div class="row-fluid form-horizontal-desktop">
                                    <div class="span6">
										<?PHP
										foreach ($this->form->getFieldset($fieldset->name) as $field)
										{
											?>
                                            <div class="control-group">
                                                <div class="control-label">
													<?php echo $field->label; ?>
                                                </div>
                                                <div class="controls">
													<?php echo $field->input; ?>
                                                </div>
                                            </div>
											<?php
										}
										?>
                                    </div>
                                </div>
                            </div>
                        </div>
						<?PHP
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
        <div class="clr"></div>
        <div>
            <input type="hidden" name="pid" value="<?php echo $this->item->project_id; ?>"/>
            <input type="hidden" name="project_id" value="<?php echo $this->item->project_id; ?>"/>
            <input type="hidden" name="season_id" value="<?php echo $this->season_id; ?>"/>
            <input type="hidden" name="task" value="projectteam.edit"/>
        </div>
		<?php echo HTMLHelper::_('form.token'); ?>
    </form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
