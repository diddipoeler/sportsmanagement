<?php
/** SportsManagement club RSS feed output for Joomla 5/6. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\HTML\HTMLHelper;

$feed = $this->rssfeeditems;

if (!$feed || !is_countable($feed)) {
    return;
}

$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$validHttpUrl = static function (mixed $value): string {
    $url = trim((string) $value);

    if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
        return '';
    }

    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

    return in_array($scheme, ['http', 'https'], true) ? $url : '';
};

$description = trim((string) ($feed->description ?? ''));
$imageUrl = $validHttpUrl($feed->image ?? '');
$imageTitle = trim((string) ($feed->imagetitle ?? $feed->title ?? ''));
?>
<?php if ($description !== '') : ?>
    <div class="feed-description">
        <?php echo $escape(strip_tags(str_replace('&apos;', "'", $description))); ?>
    </div>
<?php endif; ?>

<?php if ($imageUrl !== '') : ?>
    <div class="feed-image">
        <img src="<?php echo $escape($imageUrl); ?>" alt="<?php echo $escape($imageTitle); ?>" loading="lazy">
    </div>
<?php endif; ?>

<?php if (count($feed) > 0) : ?>
    <ol class="feed-items">
        <?php foreach ($feed as $item) : ?>
            <?php
            if (!$item) {
                continue;
            }

            $uri = $validHttpUrl($item->guid ?? $item->uri ?? '');
            if ($uri === '') {
                $uri = $validHttpUrl($item->uri ?? '');
            }

            $title = trim((string) ($item->title ?? ''));
            $text = trim((string) ($item->content ?? $item->description ?? ''));
            $text = OutputFilter::stripImages($text);
            $text = trim(strip_tags($text));
            $text = HTMLHelper::_('string.truncate', $text, 200);
            ?>
            <li>
                <?php if ($title !== '') : ?>
                    <?php if ($uri !== '') : ?>
                        <h3 class="feed-link">
                            <a href="<?php echo $escape($uri); ?>" target="_blank" rel="noopener noreferrer">
                                <?php echo $escape($title); ?>
                            </a>
                        </h3>
                    <?php else : ?>
                        <h3 class="feed-link"><?php echo $escape($title); ?></h3>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($text !== '') : ?>
                    <div class="feed-item-description"><?php echo $escape(str_replace('&apos;', "'", $text)); ?></div>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
<?php endif; ?>
