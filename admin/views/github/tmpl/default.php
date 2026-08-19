<?php
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$templatesToLoad = ['footer', 'listheader'];
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<div class="mb-3">
    <a class="btn btn-primary" href="<?php echo Route::_('index.php?option=com_sportsmanagement&view=github&tmpl=component&layout=addissue'); ?>">
        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_ADD_ISSUE'); ?>
    </a>
</div>

<table class="table">
    <tbody>
    <?php foreach ((array) $this->commitlist as $commit) : ?>
        <?php
        $details = isset($commit->commit) && is_object($commit->commit) ? $commit->commit : null;
        $author = $details && isset($details->author) && is_object($details->author) ? $details->author : null;
        $account = isset($commit->author) && is_object($commit->author) ? $commit->author : null;
        $date = $author ? (string) ($author->date ?? '') : '';
        $name = $author ? (string) ($author->name ?? '') : '';
        $message = $details ? (string) ($details->message ?? '') : '';
        $htmlUrl = (string) ($commit->html_url ?? '');
        $avatarUrl = $account ? (string) ($account->avatar_url ?? '') : '';
        ?>
        <tr>
            <td class="text-nowrap">
                <?php echo $date !== '' ? HTMLHelper::_('date', $date, 'd.m.Y H:i:s') : ''; ?>
            </td>
            <td><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></td>
            <td>
                <?php if (str_starts_with($htmlUrl, 'https://github.com/')) : ?>
                    <a
                        class="btn btn-sm btn-info"
                        href="<?php echo htmlspecialchars($htmlUrl, ENT_QUOTES, 'UTF-8'); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <span class="octicon octicon-mark-github"></span>
                        <?php echo nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')); ?>
                    </a>
                <?php else : ?>
                    <?php echo nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')); ?>
                <?php endif; ?>
            </td>
            <td>
                <?php if (str_starts_with($avatarUrl, 'https://')) : ?>
                    <img
                        src="<?php echo htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8'); ?>"
                        alt="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
                        width="20"
                        height="20"
                        loading="lazy"
                    >
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
