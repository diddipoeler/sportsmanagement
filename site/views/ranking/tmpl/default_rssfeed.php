<?php
/** SportsManagement ein Programm zur Verwaltung fűr alle Sportarten
 * @version   1.0.05
 * @file      deafult_rssfeed.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage ranking
 */

defined('_JEXEC') or die('Restricted access');
?>

<div class="row" id="rssfeed">
    <?php
    $rssitems_colums = $this->overallconfig['rssitems_colums'];

    foreach ($this->rssfeeditems as $feed) {
        if ($feed != false) {
            //image handling
            $iUrl = isset($feed->image->url) ? $feed->image->url : null;
            $iTitle = isset($feed->image->title) ? $feed->image->title : null;
            ?>
            <table class="table">
            <?php
            // feed description
            if (!is_null($feed->title) && $this->overallconfig['rsstitle']) {
                ?>
                    <tr>
                        <td>
                            <div class="jefeedpro_heading_title">
            <?php if ($this->overallconfig['rsstitle_linkable']) { ?>
                                    <a href="<?php echo str_replace('&', '&amp', $feed->link); ?>" target="<?php echo $this->overallconfig['link_target'] ?>">
                                    <?php echo $feed->title; ?></a>
                                    <?php
                                    } else {
                                        echo $feed->title;
                                    }
                                    ?>	
                            </div>
                        </td>
                    </tr>
                    <?php
                }

                // feed description
                if ($this->overallconfig['rssdesc']) {
                    ?>
                    <tr>
                        <td class="jefeedpro_heading_desc"><div class="jefeedpro_heading_desc"><?php echo $feed->description; ?></div></td>
                        <?php if ($this->overallconfig['rssimage'] && $iUrl) { ?>
                            <td align="center" class="jefeedpro_heading_image"><div class="jefeedpro_heading_image"><img src="<?php echo $iUrl; ?>" alt="<?php echo @$iTitle; ?>"/></div></td>
                        <?php } ?>
                    </tr>
                    <?php
                }

                $actualItems = count($feed->items);
                $setItems = $this->overallconfig['rssitems'];

                if ($setItems > $actualItems) {
                    $totalItems = $actualItems;
                } else {
                    $totalItems = $setItems;
                }
                ?>
                <tr>
                    <td colspan="2">
                        <table class="table table-striped">
                            <?php
                            $words = $this->overallconfig['word_count'];
                            $word_tooltip = $this->overallconfig['tooltip_wordcount_desc'];

                            for ($j = 0; $j < $totalItems; $j ++) {
                                $currItem = & $feed->items[$j];
                                // item title
                                if (($j % $rssitems_colums) == 0) :
                                    if ($this->overallconfig['rssrow_alternate']) {
                                        $row = 'row' . (floor($j / $rssitems_colums) % $rssitems_colums);
                                    } else {
                                        $row = 'row0';
                                    }
                                    ?>
                                    <tr class="<?php echo $row; ?>">
                                <?php endif; ?>
                                    <td class="item" style="width:<?php echo floor(99 / $rssitems_colums) . "%"; ?>">
                                    <?php
                                    if (!is_null($currItem->get_link())) {
                                        // Get tooltip description
                                        //$des_tooltip = ($word_tooltip == 0) ? $currItem->get_description() : modJeFeedHelper::limitText($currItem->get_description(),$word_tooltip); 		
                                        $des_tooltip = $this->model->limitText($currItem->get_description(), $word_tooltip);
                                        ?>
                                            <?php
                                            if ($this->overallconfig['rss_enable_tooltip'] && (!$this->overallconfig['rssitemdesc'])) {
                                                $tooltip_content = ' class="editlinktip hasTip" title="' . $currItem->get_title() . '::' . addslashes(htmlspecialchars($des_tooltip)) . '"';
                                            } else {
                                                $tooltip_content = '';
                                            }
                                            ?>
                                            <span <?php echo $tooltip_content ?>><a href="<?php echo $currItem->get_link(); ?>" target="<?php echo $this->overallconfig['link_target'] ?>" rel="<?php echo $this->overallconfig['no_follow'] ?>" ><?php echo $currItem->get_title(); ?></a></span>
                                            <?php
                                        }
                                        // item description rssitemdesc
                                        if ($this->overallconfig['rssitemdesc']) {
                                            ?>
                                            <div style="text-align: <?php echo $this->overallconfig['rssrtl'] ? 'right' : 'left'; ?> !important">
                                            <?php echo $des_tooltip; ?>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                    </td>
                                        <?php if (($j % $rssitems_colums) == ($rssitems_colums - 1)) : ?>
                                    </tr>
                                        <?php endif; ?>
                                    <?php
                                }
                                ?>
                        </table>
                    </td>
                </tr>
            </table>
        <?php
    }
}
?>
</div>
