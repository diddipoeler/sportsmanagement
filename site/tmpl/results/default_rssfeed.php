<?php
/** Native project RSS feed rendering for results. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\HTML\HTMLHelper;

$feed = $this->rssfeeditems;
if (!$feed || !is_countable($feed)) {
    return;
}

$maxItems = max(1, (int) ($this->overallconfig['rssitems'] ?? 5));
$wordCount = max(0, (int) ($this->overallconfig['word_count'] ?? 0));
$validHttpUrl = static function (mixed $value): string {
    $url = trim((string) $value);
    if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
        return '';
    }

    return in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true)
        ? $url
        : '';
};
$title = trim((string) ($feed->title ?? ''));
$link = $validHttpUrl($feed->link ?? $feed->uri ?? '');
$description = trim((string) ($feed->description ?? ''));
$items = [];
foreach ($feed as $item) {
    if ($item) {
        $items[] = $item;
        if (count($items) >= $maxItems) {
            break;
        }
    }
}
?>
<div class="results-project-rss my-3">
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
                        $itemTitle = trim((string) ($item->title ?? ''));
                        $itemLink = $validHttpUrl($item->uri ?? $item->guid ?? '');
                        $itemDescription = trim((string) ($item->content ?? $item->description ?? ''));
                        $itemDescription = OutputFilter::stripImages($itemDescription);
                        $itemDescription = trim(strip_tags($itemDescription));

                        if ($wordCount > 0 && $itemDescription !== '') {
                            $words = preg_split('/\s+/', $itemDescription) ?: [];
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
                                <div class="small text-muted">
                                    <?php echo $this->escape(HTMLHelper::_('string.truncate', $itemDescription, 400)); ?>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
