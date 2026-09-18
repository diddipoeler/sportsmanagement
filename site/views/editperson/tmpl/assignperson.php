<?php
/**
 * Native Joomla 5/6 person assignment popup.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright (C) 2013-2026 Fussball in Europa
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$assets = $this->document->getWebAssetManager();
$assets->registerAndUseScript(
    'com_sportsmanagement.site.assignperson',
    'components/com_sportsmanagement/assets/js/assignperson.js',
    ['version' => 'auto'],
    ['defer' => true],
    ['core']
);
?>
<div class="container-fluid py-3">
    <form action="index.php" method="post" id="adminForm" data-jsm-assign-person-form>
        <fieldset class="border rounded p-3">
            <legend class="float-none w-auto px-2 h5">
                <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSON_ASSIGN_DESCR2'); ?>
            </legend>

            <div class="mb-3">
                <?php echo $this->lists['projects']; ?>
            </div>

            <?php if ($this->project_id) : ?>
                <div class="mb-3">
                    <?php echo $this->lists['projectteams']; ?>
                </div>

                <button type="button" class="btn btn-primary" data-jsm-assign-person>
                    <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSON_ASSIGN'); ?>
                </button>
            <?php endif; ?>
        </fieldset>

        <input type="hidden" name="option" value="com_sportsmanagement">
        <input type="hidden" name="view" value="person">
        <input type="hidden" name="task" value="person.personassign">
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
