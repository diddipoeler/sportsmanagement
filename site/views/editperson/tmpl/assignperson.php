<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editperson
 * @file       assignperson.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    mod_sportsmanagement_calendar
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<script>
    window.SportsManagementAssignPerson = window.SportsManagementAssignPerson || {};

    window.SportsManagementAssignPerson.closeParentDialog = function () {
        if (window.parent === window) {
            return;
        }

        try {
            const frame = window.frameElement;
            const modalElement = frame ? frame.closest('.modal') : null;
            const BootstrapModal = window.parent.bootstrap?.Modal;

            if (modalElement && BootstrapModal) {
                const modal = BootstrapModal.getInstance(modalElement)
                    || BootstrapModal.getOrCreateInstance(modalElement);

                modal.hide();
                return;
            }
        } catch (error) {
            // Fall through to JoomlaDialog cross-window messaging.
        }

        window.parent.postMessage({messageType: 'joomla:cancel'}, window.location.origin);
    };

    window.SportsManagementAssignPerson.assign = function () {
        const parentForm = window.top.document.forms.adminForm;

        if (!parentForm) {
            return;
        }

        parentForm.elements.project_id.value = document.getElementById('prjid').value;
        parentForm.elements.team_id.value = document.getElementById('xtid').value;
        parentForm.elements.assignperson.value = '1';

        window.SportsManagementAssignPerson.closeParentDialog();
    };
</script>
<div>
    <form action="index.php" method="post" id="adminForm">
        <div id="editcell">
            <fieldset class="adminform">
                <legend>
                    <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSON_ASSIGN_DESCR2'); ?>
                </legend>
                <table class="adminform">
                    <tr>
                        <td>
                            <?php echo $this->lists['projects']; ?>
                        </td>
                    </tr>
                    <?php if ($this->project_id) : ?>
                        <tr>
                            <td>
                                <?php echo $this->lists['projectteams']; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="button" style="text-align:left">
                                    <input type="button" class="inputbox"
                                           onclick="window.SportsManagementAssignPerson.assign();"
                                           value="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSON_ASSIGN'); ?>"/>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </table>
            </fieldset>
        </div>
        <div style="clear"></div>
        <input type="hidden" name="option" value="com_sportsmanagement"/>
        <input type="hidden" name="view" value="person"/>
        <input type="hidden" name="task" value="person.personassign"/>
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
