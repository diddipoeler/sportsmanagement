<?php 
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version   1.0.05
 * @file      default_rssfeed.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */ 


defined('_JEXEC') or die('Restricted access'); 
use Joomla\CMS\HTML\HTMLHelper;
?>

<?php
//$rssitems_colums = $this->overallconfig['rssitems_colums'] ;
$this->rssDoc = $this->rssfeeditems;
//foreach ($this->rssfeeditems as $feed) 
//{
?>
<!-- Show Feed's Description -->


            <div class="feed-description">
                <?php echo str_replace('&apos;', "'", $this->rssDoc->description); ?>
            </div>


        <!-- Show Image -->
        <?php if (isset($this->rssDoc->image, $this->rssDoc->imagetitle) ) : ?>
            <div>
                <img src="<?php echo $this->rssDoc->image; ?>" alt="<?php echo $this->rssDoc->image->decription; ?>">
            </div>
        <?php endif; ?>

<!-- Show items -->
        <?php if (!empty($this->rssDoc[0])) : ?>
            <ol>
                <?php for ($i = 0; $i < 10; $i++) : ?>
                    <?php if (empty($this->rssDoc[$i])) : ?>
                        <?php break; ?>
                    <?php endif; ?>
                    <?php $uri   = !empty($this->rssDoc[$i]->guid) || $this->rssDoc[$i]->guid !== null ? trim($this->rssDoc[$i]->guid) : trim($this->rssDoc[$i]->uri); ?>
                    <?php $uri   = strpos($uri, 'http') !== 0 ? $uri : $uri; ?>
                    <?php $text  = !empty($this->rssDoc[$i]->content) || $this->rssDoc[$i]->content !== null ? trim($this->rssDoc[$i]->content) : trim($this->rssDoc[$i]->description); ?>
                    <?php $title = trim($this->rssDoc[$i]->title); ?>
                    <li>
                        <?php if (!empty($uri)) : ?>
                            <h3 class="feed-link">
                                <a href="<?php echo htmlspecialchars($uri); ?>" target="_blank">
                                    <?php echo $title; ?>
                                </a>
                            </h3>
                        <?php else : ?>
                            <h3 class="feed-link"><?php echo $title; ?></h3>
                        <?php endif; ?>

                        <?php if (!empty($text)) : ?>
                            <div class="feed-item-description">
                                
                                    <?php $text = JFilterOutput::stripImages($text); ?>
                                
                                <?php $text = HTMLHelper::_('string.truncate', $text, 200); ?>
                                <?php echo str_replace('&apos;', "'", $text); ?>
                            </div>
                        <?php endif; ?>
                    </li>
                <?php endfor; ?>
            </ol>
        <?php endif; ?>


<?php
//} 
?>

