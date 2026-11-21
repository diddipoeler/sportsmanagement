<?php
/**
 * Native Joomla 5/6 administrator SportsManagement GitHub list layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
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
        // Commit API records are external data. Never coerce arrays or
        // objects into strings when older or malformed responses arrive.
        if (!is_object($commit)) {
            continue;
        }

        $details = isset($commit->commit) && is_object($commit->commit) ? $commit->commit : null;
        $author = $details && isset($details->author) && is_object($details->author) ? $details->author : null;
        $account = isset($commit->author) && is_object($commit->author) ? $commit->author : null;
        $date = $author && is_string($author->date ?? null) ? $author->date : '';
        $name = $author && is_string($author->name ?? null) ? $author->name : '';
        $message = $details && is_string($details->message ?? null) ? $details->message : '';
        $htmlUrl = is_string($commit->html_url ?? null) ? $commit->html_url : '';
        $avatarUrl = $account && is_string($account->avatar_url ?? null) ? $account->avatar_url : '';

        // Only direct GitHub commit links and GitHub's avatar CDN are
        // rendered as clickable external resources. Reject injected
        // fragments, URL credentials, unsupported hosts and control bytes.
        $safeCommitUrl = strlen($htmlUrl) <= 2048
            && preg_match('~^https://github\\.com/[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+/commit/[a-f0-9]{40}$~iD', $htmlUrl) === 1;
        $safeAvatarUrl = strlen($avatarUrl) <= 2048
            && preg_match('~^https://avatars\\.githubusercontent\\.com/u/[1-9][0-9]*(?:\\?v=[0-9]+)?$~iD', $avatarUrl) === 1;

        // GitHub commit dates use UTC ISO 8601. strtotime() also accepts
        // relative words and normalises impossible dates; reject those
        // instead of formatting a different timestamp in the admin list.
        $safeDate = false;
        if (strlen($date) === 20 && preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}Z$/D', $date) === 1) {
            $parsedDate = \\DateTimeImmutable::createFromFormat('!Y-m-d\\TH:i:s\\Z', $date, new \\DateTimeZone('UTC'));
            $safeDate = $parsedDate !== false
                && $parsedDate->format('Y-m-d\\TH:i:s\\Z') === $date;
        }
        ?>
        <tr>
            <td class="text-nowrap">
                <?php echo $safeDate
                    ? htmlspecialchars((string) HTMLHelper::_('date', $date, 'd.m.Y H:i:s'), ENT_QUOTES, 'UTF-8')
                    : ''; ?>
            </td>
            <td><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></td>
            <td>
                <?php if ($safeCommitUrl) : ?>
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
                <?php if ($safeAvatarUrl) : ?>
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
