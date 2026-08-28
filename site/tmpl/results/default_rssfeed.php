<?php
/** Native project RSS feed rendering for results. */
defined('_JEXEC') or die('Restricted access');

$feeds = is_iterable($this->rssfeeditems) ? $this->rssfeeditems : [$this->rssfeeditems];
$maxItems = max(1, (int) ($this->overallconfig['rssitems'] ?? 5));
$wordCount = max(0, (int) ($this->overallconfig['word_count'] ?? 0));
?>
<div class="results-project-rss my-3">
    <?php foreach ($feeds as $feed) : ?>
        <?php if (!$feed) { continue; } ?>
        <?php
        $title = trim((string) ($feed->title ?? ''));
        $link = trim((string) ($feed->link ?? ''));
        $description = trim((string) ($feed->description ?? ''));
        $items = array_slice((array) ($feed->items ?? []), 0, $maxItems);
        ?>
        <div class="card mb-3">
            <?php if ($title !== '' && !empty($this->overallconfig['rsstitle'])) : ?>
                <div class="card-header">
                    <?php if ($link !== '' && !empty($this->overallconfig['rsstitle_linkable'])) : ?>
                        <a href="<?php echo $this->escape($link); ?>" target="_blank" rel="noopener noreferrer">
                            <?php echo $this->escape($title); ?>
                        </a>
                    <?php else : ?>
                        <?php echo $this->escape($title); ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="card-body">
                <?php if ($description !== '' && !empty($this->overallconfig['rssdesc'])) : ?>
                    <p><?php echo $this->escape(strip_tags($description)); ?></p>
                <?php endif; ?>

                <?php if ($items !== []) : ?>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($items as $item) : ?>
                            <?php
                            $itemTitle = method_exists($item, 'get_title')
                                ? (string) $item->get_title()
                                : (string) ($item->title ?? '');
                            $itemLink = method_exists($item, 'get_link')
                                ? (string) $item->get_link()
                                : (string) ($item->link ?? '');
                            $itemDescription = method_exists($item, 'get_description')
                                ? (string) $item->get_description()
                                : (string) ($item->description ?? '');

                            if ($wordCount > 0 && $itemDescription !== '') {
                                $words = preg_split('/\s+/', trim(strip_tags($itemDescription))) ?: [];
                                $itemDescription = implode(' ', array_slice($words, 0, $wordCount));
                                if (count($words) > $wordCount) {
                                    $itemDescription .= '…';
                                }
                            }
                            ?>
                            <li class="mb-2">
                                <?php if ($itemLink !== '') : ?>
                                    <a href="<?php echo $this->escape($itemLink); ?>" target="_blank" rel="noopener noreferrer">
                                        <?php echo $this->escape($itemTitle); ?>
                                    </a>
                                <?php else : ?>
                                    <?php echo $this->escape($itemTitle); ?>
                                <?php endif; ?>
                                <?php if (!empty($this->overallconfig['rssitemdesc']) && $itemDescription !== '') : ?>
                                    <div class="small text-muted"><?php echo $this->escape(strip_tags($itemDescription)); ?></div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
