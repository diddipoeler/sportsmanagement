<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$logo = Uri::root() . 'administrator/components/com_sportsmanagement/assets/icons/logo_transparent.png';
?>
<div class="com-sportsmanagement-about">
  <div class="text-center mb-4">
    <img src="<?php echo htmlspecialchars($logo, ENT_QUOTES, 'UTF-8'); ?>" alt="SportsManagement" width="200" loading="lazy">
  </div>

  <h2><?php echo Text::_('COM_SPORTSMANAGEMENT_ABOUT'); ?></h2>
  <p><?php echo Text::_('COM_SPORTSMANAGEMENT_ABOUT_TEXT'); ?></p>

  <h2><?php echo Text::_('COM_SPORTSMANAGEMENT_ABOUT_DIDDIPOELER'); ?></h2>
  <table class="table table-striped">
    <tbody>
      <tr><th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_ABOUT_TEXT_DIDDIPOELER'); ?></th><td><?php echo Text::_('COM_SPORTSMANAGEMENT_ABOUT_TEXT_DESC_DIDDIPOELER'); ?></td></tr>
      <tr><th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_ABOUT_WEBSITE_DIDDIPOELER'); ?></th><td><a href="<?php echo htmlspecialchars($this->about->diddipoelerpage, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($this->about->diddipoelerpage, ENT_QUOTES, 'UTF-8'); ?></a></td></tr>
      <tr><th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_ABOUT_SUPPORT_FORUM_DIDDIPOELER'); ?></th><td><a href="<?php echo htmlspecialchars($this->about->diddipoelerforum, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">Fussballineuropa Forum</a></td></tr>
      <tr><th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_ABOUT_GITHUB_DIDDIPOELER'); ?></th><td><a href="<?php echo htmlspecialchars($this->about->github, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">GitHub SportsManagement</a></td></tr>
      <tr><th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_ABOUT_SUPPORT_EMAIL_DIDDIPOELER'); ?></th><td><a href="mailto:<?php echo htmlspecialchars($this->about->diddipoeleremail, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($this->about->diddipoeleremail, ENT_QUOTES, 'UTF-8'); ?></a></td></tr>
    </tbody>
  </table>

  <h2><?php echo Text::_('COM_SPORTSMANAGEMENT_ABOUT_DETAILS'); ?></h2>
  <table class="table table-striped">
    <tbody>
      <tr><th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_ABOUT_DESIGNER'); ?></th><td><?php echo htmlspecialchars($this->about->designer, ENT_QUOTES, 'UTF-8'); ?></td></tr>
      <tr><th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_ABOUT_DEVELOPERS'); ?></th><td><?php echo htmlspecialchars($this->about->developer, ENT_QUOTES, 'UTF-8'); ?></td></tr>
    </tbody>
  </table>

  <h2><?php echo Text::_('COM_SPORTSMANAGEMENT_ABOUT_LICENSE'); ?></h2>
  <p><?php echo Text::_('COM_SPORTSMANAGEMENT_ABOUT_LICENSE_TEXT'); ?></p>
</div>
