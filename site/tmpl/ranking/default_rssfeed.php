<?php
/** Native project RSS feed rendering for ranking. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;

$feeds = is_array($this->rssfeeditems) ? $this->rssfeeditems : [];
$limit = max(1, (int) ($this->overallconfig['rssitems'] ?? 5));
$wordCount = max(0, (int) ($this->overallconfig['word_count'] ?? 0));

if ($feeds === []) {
    return;
}
?>
<section class="ranking-rss my-4">
    <?php foreach ($feeds as $feed) : ?>
        <?php
        if (!$feed) {
            continue;
        }
        $title = trim((string) ($feed->title ?? ''));
        $link = trim((string) ($feed->link ?? $feed->uri ?? ''));
        $description = trim((string) ($feed->description ?? ''));
        $items = [];
        foreach ($feed as $item) {
            $items[] = $item;
            if (count($items) >= $limit) {
                break;
            }
        }
        ?>
        <div class="card mb-3">
            <?php if ($title !== '' && !empty($this->overallconfig['rsstitle'])) : ?>
                <div class="card-header">
                    <?php if ($link !== '' && !empty($this->overallconfig['rsstitle_linkable'])) : ?>
                        <a href="<?php echo $this->escape($link); ?>" target="_blank" rel="noopener noreferrer"><?php echo $this->escape($title); ?></a>
                    <?php else : ?>
                        <?php echo $this->escape($title); ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="card-body">
                <?php if ($description !== '' && !empty($this->overallconfig['rssdesc'])) : ?>
                    <p><?php echo $this->escape(trim(strip_tags($description))); ?></p>
                <?php endif; ?>

                <?php if ($items !== []) : ?>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($items as $item) : ?>
                            <?php
                            $itemTitle = trim((string) ($item->title ?? ''));
                            $itemLink = trim((string) ($item->uri ?? $item->link ?? ''));
                            $itemDescription = trim((string) ($item->content ?? $item->description ?? ''));
                            $plain = trim(strip_tags($itemDescription));
                            if ($wordCount > 0 && $plain !== '') {
                                $words = preg_split('/\s+/', $plain) ?: [];
                                $plain = implode(' ', array_slice($words, 0, $wordCount));
                                if (count($words) > $wordCount) {
                                    $plain .= '…';
                                }
                            }
                            ?>
                            <li class="mb-2">
                                <?php if ($itemLink !== '') : ?>
                                    <a href="<?php echo $this->escape($itemLink); ?>" target="_blank" rel="noopener noreferrer"><?php echo $this->escape($itemTitle); ?></a>
                                <?php else : ?>
                                    <?php echo $this->escape($itemTitle); ?>
                                <?php endif; ?>
                                <?php if (!empty($this->overallconfig['rssitemdesc']) && $plain !== '') : ?>
                                    <div class="small text-muted"><?php echo $this->escape(HTMLHelper::_('string.truncate', $plain, 400)); ?></div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</section>
