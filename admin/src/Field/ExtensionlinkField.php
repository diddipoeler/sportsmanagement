<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

final class ExtensionlinkField extends FormField
{
    protected $type = 'ExtensionLink';

    private const TYPES = [
        'forum' => ['chat.png', 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_FORUM_LABEL', 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_FORUM_DESC'],
        'demo' => ['visibility.png', 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_DEMO_LABEL', 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_DEMO_DESC'],
        'review' => ['thumb-up.png', 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_REVIEW_LABEL', 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_REVIEW_DESC'],
        'donate' => ['paypal.png', 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_DONATE_LABEL', 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_DONATE_DESC'],
        'upgrade' => ['wallet-membership.png', 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_UPGRADE_LABEL', 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_UPGRADE_DESC'],
        'doc' => ['local-library.png', 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_DOC_LABEL', 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_DOC_DESC'],
        'onlinedoc' => ['local-library.png', 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_ONLINEDOC_LABEL', 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_ONLINEDOC_DESC'],
        'report' => ['bug-report.png', 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_BUGREPORT_LABEL', 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_BUGREPORT_DESC'],
        'support' => ['lifebuoy.png', 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_SUPPORT_LABEL', 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_SUPPORT_DESC'],
        'translate' => ['translate.png', 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_TRANSLATE_LABEL', 'COM_SPORTSMANAGEMENT_EXTENSIONLINK_TRANSLATE_DESC'],
    ];

    protected function getLabel(): string
    {
        $this->loadLanguage();
        [$image, $title] = $this->metadata();
        $content = $title !== '' ? Text::_($title) : '';

        if ($image !== '') {
            $src = Uri::root() . 'administrator/components/com_sportsmanagement/assets/images/' . $image;
            $content = '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8')
                . '" alt="" style="margin-right: 5px;">'
                . '<span style="vertical-align: middle">' . $content . '</span>';
        }

        return '<div style="clear: both;"><span class="badge text-bg-info">' . $content . '</span></div>';
    }

    protected function getInput(): string
    {
        $this->loadLanguage();
        [, , $defaultDescription] = $this->metadata();
        $description = trim((string) ($this->element['description'] ?? ''));
        $description = $description !== '' ? $description : $defaultDescription;
        $link = trim((string) ($this->element['link'] ?? ''));

        if ($description === '') {
            return '';
        }

        $text = $link !== '' ? Text::sprintf($description, $link) : Text::_($description);

        return '<div style="padding-top: 5px; overflow: inherit">' . $text . '</div>';
    }

    private function metadata(): array
    {
        $type = trim((string) ($this->element['linktype'] ?? ''));

        return self::TYPES[$type] ?? ['', '', ''];
    }

    private function loadLanguage(): void
    {
        $language = Factory::getApplication()->getLanguage();
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, $language->getTag(), true);
    }
}
