<?php
/**
 * Native Joomla 5/6 extension-link field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
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

    /**
     * Render the configured extension-support link label and icon.
     *
     * Resolves only known link types and escapes both the translated label
     * and the static icon URL before inserting administrator badge markup.
     *
     * @return string Extension link label, optionally with a known icon.
     */
    protected function getLabel(): string
    {
        $this->loadLanguage();
        [$image, $title] = $this->metadata();
        $content = $title !== ''
            ? htmlspecialchars(Text::_($title), ENT_QUOTES, 'UTF-8')
            : '';

        if ($image !== '') {
            $src = Uri::root() . 'administrator/components/com_sportsmanagement/assets/images/' . $image;
            $content = '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8')
                . '" alt="" style="margin-right: 5px;">'
                . '<span style="vertical-align: middle">' . $content . '</span>';
        }

        return '<div style="clear: both;"><span class="badge text-bg-info">' . $content . '</span></div>';
    }

    /**
     * Render the translated description for a configured extension link.
     *
     * Selects the link-type default text when no custom description exists,
     * substitutes a validated URL and escapes resulting translated text.
     *
     * @return string Translated link description, or empty text if unset.
     */
    protected function getInput(): string
    {
        $this->loadLanguage();
        [, , $defaultDescription] = $this->metadata();
        $description = trim((string) ($this->element['description'] ?? ''));
        $description = $description !== '' ? $description : $defaultDescription;
        $link = self::safeLink(trim((string) ($this->element['link'] ?? '')));

        if ($description === '') {
            return '';
        }

        $text = $link !== '' ? Text::sprintf($description, $link) : Text::_($description);

        return '<div style="padding-top: 5px; overflow: inherit">'
            . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</div>';
    }

    /**
     * Read the icon and translation keys for a known extension link type.
     *
     * Unknown types return empty descriptors rather than arbitrary file paths.
     *
     * @return array{0:string,1:string,2:string} Icon, title key and description key.
     */
    private function metadata(): array
    {
        $type = trim((string) ($this->element['linktype'] ?? ''));

        return self::TYPES[$type] ?? ['', '', ''];
    }

    /**
     * Accept only ordinary HTTP(S) or relative administrator link values.
     *
     * Prevents dangerous schemes, network-path references, control bytes and
     * percent-encoded traversal from reaching translated link descriptions.
     *
     * @param string $url Configured destination from extension field XML.
     * @return string Safe link, or an empty value for invalid destinations.
     */
    private static function safeLink(string $url): string
    {
        if ($url === '' || strlen($url) > 2048 || str_contains($url, chr(92))
            || str_starts_with($url, '//')
            || preg_match('/[\\x00-\\x1F\\x7F]/', $url) === 1
            || preg_match('~(?:^|/)\\.{1,2}(?:/|$)~', $url) === 1) {
            return '';
        }

        // Decode layered URL escapes before accepting a local path. Browsers
        // and proxies can normalize %2e%2e or %2f after HTML rendering.
        $decoded = $url;

        for ($depth = 0; $depth < 3; ++$depth) {
            if (preg_match('~(?:^|/)\\.{1,2}(?:/|$)~', $decoded) === 1
                || str_starts_with($decoded, '//')
                || str_contains($decoded, chr(92))
                || preg_match('/[\\x00-\\x1F\\x7F]/', $decoded) === 1) {
                return '';
            }

            $next = rawurldecode($decoded);

            if ($next === $decoded) {
                break;
            }

            $decoded = $next;
        }

        if (preg_match('~(?:^|/)\\.{1,2}(?:/|$)~', $decoded) === 1
            || str_starts_with($decoded, '//')
            || str_contains($decoded, chr(92))
            || preg_match('/[\\x00-\\x1F\\x7F]/', $decoded) === 1) {
            return '';
        }

        if (preg_match('~^[a-z][a-z0-9+.-]*:~i', $url) === 1) {
            return preg_match('~^https?://~i', $url) === 1
                && filter_var($url, FILTER_VALIDATE_URL) !== false
                ? $url
                : '';
        }

        return $url;
    }

    /**
     * Load SportsManagement translations from the administrator language service.
     *
     * @return void
     */
    private function loadLanguage(): void
    {
        $language = SportsManagementAdministratorApplicationResolver::resolve()->getLanguage();
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, $language->getTag(), true);
    }
}
