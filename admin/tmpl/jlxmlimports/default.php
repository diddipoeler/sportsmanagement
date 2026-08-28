<?php
/** Native Joomla 5/6 XML import landing layout. */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<div id="editcell" class="row">
    <?php if (!empty($this->sidebar)) : ?>
        <div id="j-sidebar-container" class="col-md-2">
            <?php echo $this->sidebar; ?>
        </div>
        <div class="col-md-8">
    <?php else : ?>
        <div class="col-md-10">
    <?php endif; ?>
        <div id="dashboard-iconss" class="dashboard-icons">
            <form enctype="multipart/form-data"
                  action="<?php echo $escape($this->request_url); ?>"
                  method="post"
                  id="adminForm"
                  name="adminForm">
                <table class="adminlist">
                    <thead>
                    <tr>
                        <th><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TABLE_TITLE_1', $this->upload_maxsize); ?></th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <td>
                            <p><strong><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_EXTENSION_INFO'); ?></strong></p>
                            <p><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_HINT1'); ?></p>
                        </td>
                    </tr>
                    </tfoot>
                    <tbody>
                    <tr>
                        <td>
                            <fieldset class="text-center">
                                <input class="input_box" id="import_package" name="import_package" type="file" size="57">
                                <input class="button" type="submit"
                                       value="<?php echo $escape(Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_UPLOAD_BUTTON')); ?>">
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <fieldset class="text-center">
                                <legend><?php echo Text::_('1. Èlanska liga MNZ Maribor'); ?></legend>
                                <input class="input_box" type="checkbox" id="importelanska" name="importelanska">
                                <?php echo Text::_('1. Èlanska liga MNZ Maribor'); ?>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo $this->countries; ?>
                            &nbsp;<?php echo $this->countryFlag; ?>
                            &nbsp;(<?php echo $escape($this->country); ?>)
                        </td>
                    </tr>
                    <tr><td><?php echo $this->agegroup; ?></td></tr>
                    <tr><td><?php echo $this->seasons; ?></td></tr>
                    <tr>
                        <td style="background-color:#EEEEEE">
                            <select name="copyTemplate" id="copyTemplate" hidden>
                                <option value="0" selected>
                                    <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TEMPLATES_USEOWN'); ?>
                                </option>
                                <?php foreach ((array) $this->templates as $row) : ?>
                                    <?php $templateId = (int) ($row->value ?? $row->id ?? 0); ?>
                                    <?php $templateName = (string) ($row->text ?? $row->name ?? ''); ?>
                                    <option value="<?php echo $templateId; ?>">
                                        <?php echo $escape($templateName); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <input type="hidden" name="sent" value="1">
                <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $escape($this->config->get('upload_maxsize')); ?>">
                <input type="hidden" name="filter_season" value="<?php echo (int) $this->filter_season; ?>">
                <input type="hidden" name="task" value="jlxmlimport.save">
                <?php echo HTMLHelper::_('form.token'); ?>
            </form>
        </div>
        <?php echo $this->loadTemplate('footer'); ?>
    </div>
</div>
