<?php
/** SportsManagement club plan date navigation for Joomla 5/6. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\DatePresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$dateformat = '%d-%m-%Y';
$componentParams = ComponentHelper::getParams('com_sportsmanagement');
$pictureServer = \defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER')
    ? (string) COM_SPORTSMANAGEMENT_PICTURE_SERVER
    : Uri::root();
$useExternalPictureServer = $pictureServer !== ''
    && rtrim($pictureServer, '/') !== rtrim(Uri::root(), '/');
$pictureUrl = static function (string $picture) use ($pictureServer): string {
    $picture = trim($picture);

    if ($picture === '') {
        return '';
    }

    return preg_match('#^https?://#i', $picture)
        ? $picture
        : rtrim($pictureServer, '/') . '/' . ltrim($picture, '/');
};
?>
<div class="<?php echo $this->escape($this->divclassrow); ?>" id="clubplandatenav">
    <form name="adminForm" id="adminForm" method="post">
        <table class="table">
            <tr>
                <td>
                    <?php
                    echo HTMLHelper::_(
                        'select.genericlist',
                        $this->lists['fromteamart'],
                        'teamartsel',
                        'class="inputbox" size="1" onchange="hideclubplandate();"',
                        'value',
                        'text',
                        $this->teamartsel
                    );
                    ?>
                </td>
                <td>
                    <?php
                    echo HTMLHelper::_(
                        'select.genericlist',
                        $this->lists['fromteamseasons'],
                        'teamseasonssel',
                        'class="inputbox" size="1" onchange="hideclubplandate();"',
                        'value',
                        'text',
                        $this->teamseasonssel
                    );
                    ?>
                </td>
            </tr>
            <tr>
                <td>
                    <?php
                    echo HTMLHelper::calendar(
                        DatePresentationHelper::convert($this->startdate, 1),
                        'startdate',
                        'startdate',
                        $dateformat
                    );
                    ?>
                </td>
                <td>
                    <?php
                    echo HTMLHelper::calendar(
                        DatePresentationHelper::convert($this->enddate, 1),
                        'enddate',
                        'enddate',
                        $dateformat
                    );
                    ?>
                </td>
                <td>
                    <?php
                    echo HTMLHelper::_(
                        'select.genericlist',
                        $this->lists['type'],
                        'type',
                        'class="inputbox" size="1"',
                        'value',
                        'text',
                        $this->type
                    );
                    ?>
                </td>
                <td>
                    <input type="submit"
                           class="<?php echo $this->escape((string) ($this->config['button_style'] ?? 'btn')); ?>"
                           name="reload View"
                           value="<?php echo $this->escape(Text::_('COM_SPORTSMANAGEMENT_GLOBAL_FILTER')); ?>">
                </td>
                <td>
                    <?php
                    if ($this->club) {
                        $picture = trim((string) ($this->club->logo_big ?? ''));
                        if (
                            $picture === ''
                            || (
                                !$useExternalPictureServer
                                && !preg_match('#^https?://#i', $picture)
                                && !is_file(JPATH_SITE . '/' . ltrim($picture, '/'))
                            )
                        ) {
                            $picture = trim((string) $componentParams->get('ph_logo_big', ''));
                        }

                        echo ModalImageHelper::render(
                            'clplan' . (int) $this->club->id,
                            $pictureUrl($picture),
                            (string) $this->club->name,
                            50,
                            '',
                            $this->modalwidth,
                            $this->modalheight,
                            (int) ($this->overallconfig['use_jquery_modal'] ?? 0)
                        );
                    }
                    ?>
                </td>
            </tr>
        </table>
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
