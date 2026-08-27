<?php
/**
 * Shared Joomla 5/6 frontend footer.
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

$params = ComponentHelper::getParams('com_sportsmanagement');
$view = ucfirst(strtolower($this->input->getCmd('view', (string) ($this->view ?? ''))));
$helpServer = trim((string) $params->get('cfg_help_server', ''));
$showFacebookLink = (int) $params->get('show_facebook_link', 0);
$logoWidth = max(1, (int) $params->get('logo_picture_width', 100));
$version = '';

try {
    $extension = ExtensionHelper::getExtensionRecord('com_sportsmanagement', 'component');
    if ($extension && !empty($extension->manifest_cache)) {
        $manifest = new Registry();
        $manifest->loadString((string) $extension->manifest_cache, 'JSON');
        $version = (string) $manifest->get('version', '');
    }
} catch (\Throwable) {
    $version = '';
}

$helpLink = $helpServer !== '' ? $helpServer . 'SM-Frontend:' . $view : '';
?>
<div class="row">
    <div class="container text-center align-items-center justify-content-center">
        <br>
        <?php echo Text::_('COM_SPORTSMANAGEMENT_DESC'); ?>
        <br>
        <img
            src="<?php echo htmlspecialchars(Uri::root(true) . '/components/com_sportsmanagement/assets/images/fussballineuropa.png', ENT_QUOTES, 'UTF-8'); ?>"
            width="<?php echo $logoWidth; ?>"
            alt="Fussball in Europa"
        >
        <?php echo Text::_('COM_SPORTSMANAGEMENT_COPYRIGHT'); ?>: &copy;
        <a href="https://www.fussballineuropa.de" target="_blank" rel="noopener">Fussball in Europa</a>
        <br>

        <?php if ($showFacebookLink === 3) : ?>
            <img
                src="<?php echo htmlspecialchars(Uri::root(true) . '/components/com_sportsmanagement/assets/images/facebook.png', ENT_QUOTES, 'UTF-8'); ?>"
                width="<?php echo $logoWidth; ?>"
                alt="Facebook"
            >
            <a href="https://www.facebook.com/joomlasportsmanagement/" target="_blank" rel="noopener">JSM auf Facebook</a>
            <br>
        <?php endif; ?>

        <?php echo Text::_('COM_SPORTSMANAGEMENT_VERSION'); ?>:
        <?php echo htmlspecialchars($version !== '' ? $version . ' (diddipoeler)' : 'diddipoeler', ENT_QUOTES, 'UTF-8'); ?>
        <br>

        <?php if (isset($this->jsmseitenaufbau)) : ?>
            <div class="center">
                <?php echo Text::sprintf('COM_SPORTSMANAGEMENT_FOOTER_TIME', $this->jsmseitenaufbau); ?>
            </div>
        <?php endif; ?>

        <?php if ($helpLink !== '') : ?>
            <div class="center">
                <a
                    class="btn btn-secondary"
                    href="<?php echo htmlspecialchars($helpLink, ENT_QUOTES, 'UTF-8'); ?>"
                    target="_blank"
                    rel="noopener"
                >
                    <?php
                    echo HTMLHelper::image(
                        'media/com_sportsmanagement/jl_images/help.png',
                        Text::_('COM_SPORTSMANAGEMENT_HELP_LINK')
                    );
                    echo ' ' . Text::_('COM_SPORTSMANAGEMENT_HELP_LINK');
                    ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
