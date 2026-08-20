<?php
/** Joomla 5/6 mass-add template for individual-sport matches. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Date\Date;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$this->getDocument()->getWebAssetManager()->registerAndUseScript(
    'com_sportsmanagement.individualsport-admin',
    Uri::root(true) . '/administrator/components/com_sportsmanagement/assets/js/individualsport-admin.js',
    [],
    ['defer' => true],
    ['core']
);

$roundDateRaw = trim((string) ($this->roundws->round_date_first ?? ''));
$roundDate = $roundDateRaw === '' ? '' : (new Date($roundDateRaw))->format('d-m-Y');
?>
<div id="editcell">
    <fieldset class="adminform">
        <legend><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_TITLE', '<i>' . $this->projectws->name . '</i>'); ?></legend>

        <form name="copyform" method="post" class="d-inline" id="copyform">
            <input type="hidden" name="match_date" value="<?php echo $this->roundws->round_date_first . ' ' . $this->projectws->start_time; ?>">
            <input type="hidden" name="round_id" value="<?php echo $this->roundws->id; ?>">
            <input type="hidden" name="project_id" value="<?php echo $this->roundws->project_id; ?>">
            <input type="hidden" name="act" value="rounds">
            <input type="hidden" name="task" value="copyfrom">
            <input type="hidden" name="addtype" value="0" id="addtype">
            <input type="hidden" name="add_match_count" value="0" id="addmatchescount">
            <?php echo HTMLHelper::_('form.token') . "\n"; ?>

            <table class="table adminlist">
                <thead>
                    <tr>
                        <th><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_MULTI'); ?></th>
                        <th><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_COPY'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="align-top" style="width:40%">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <th class="text-end align-top" scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_TYPE'); ?></th>
                                        <td class="text-start align-top"><?php echo $this->lists['createTypes']; ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <div id="massadd_standard">
                                                <table class="table table-borderless">
                                                    <tbody>
                                                        <tr>
                                                            <th class="text-end align-top" scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_NR'); ?></th>
                                                            <td class="text-start align-top">
                                                                <input type="text" name="tempaddmatchescount" id="tempaddmatchescount" value="0" size="3" class="inputbox">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="text-end align-top" scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_START_HERE'); ?></th>
                                                            <td class="text-start align-top"><?php echo $this->lists['addToRound']; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th class="text-end align-top" scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_AUTO_PUBL'); ?></th>
                                                            <td class="text-start align-top"><?php echo $this->lists['autoPublish']; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th class="text-end align-top" scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_FIRST_MATCHNR'); ?></th>
                                                            <td class="text-start align-top">
                                                                <input type="text" name="firstMatchNumber" size="4" value="">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="text-end align-top" scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_STARTTIME'); ?></th>
                                                            <td>
                                                                <?php
                                                                echo HTMLHelper::calendar(
                                                                    $roundDate,
                                                                    'match_date',
                                                                    'match_date',
                                                                    '%d-%m-%Y',
                                                                    'size="10"'
                                                                );
                                                                ?>
                                                                <input type="text" name="startTime" value="<?php echo $this->projectws->start_time; ?>" size="4" maxlength="5" class="inputbox">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-end" colspan="2">
                                                                <input
                                                                    type="submit"
                                                                    value="<?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_NEW_MATCHES'); ?>"
                                                                    data-jsm-action="add-matches"
                                                                >
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div id="massadd_type2" style="display:none"></div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>

                        <td class="align-top">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <th class="text-end align-top" scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_COPY2'); ?></th>
                                        <td class="text-start align-top"><?php echo $this->lists['project_rounds2']; ?></td>
                                    </tr>
                                    <tr>
                                        <th class="text-end align-top" scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_DEFAULT_DATE'); ?></th>
                                        <td>
                                            <?php
                                            echo HTMLHelper::calendar(
                                                $roundDate,
                                                'date',
                                                'date',
                                                '%d-%m-%Y',
                                                'size="10"'
                                            );
                                            ?>
                                            <input type="text" name="time" value="<?php echo $this->projectws->start_time; ?>" size="4" maxlength="5" class="inputbox">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-end align-top" scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_FIRST_MATCHNR'); ?></th>
                                        <td><input type="text" name="start_match_number" size="4" value=""></td>
                                    </tr>
                                    <tr>
                                        <th class="text-end align-top" scope="row">
                                            <abbr title="<?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_ACRONYM_CREATE_NEW'); ?>">
                                                <?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_CREATE_NEW'); ?>
                                            </abbr>
                                        </th>
                                        <td><input type="checkbox" name="create_new" value="1" class="inputbox" checked></td>
                                    </tr>
                                    <tr>
                                        <th class="text-end align-top" scope="row">
                                            <abbr title="<?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_ACRONYM_MIRROR'); ?>">
                                                <?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_COPY_MIRROR'); ?>
                                            </abbr>
                                        </th>
                                        <td>
                                            <select name="mirror" class="inputbox">
                                                <option value="0" selected><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_COPY_MATCHES'); ?></option>
                                                <option value="1"><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_MIRROR_HA'); ?></option>
                                            </select>
                                            <br><br>
                                            <input
                                                type="submit"
                                                value="<?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHES_MASSADD_COPY_MATCHES'); ?>"
                                                data-jsm-action="copy-matches"
                                            >
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
    </fieldset>
</div>
