<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_rssfeed-table.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage clubinfo
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

$app = Factory::getApplication();

?>
<div class="no-column">
    <div class="contentpaneopen">
        <div class="contentheading">
    <?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_RSS_FEEDS'); ?>
        </div>
    </div>


<div class="srfrContainer <?php echo $moduleclass_sfx; ?>">
  
    <?php if($this->feedsBlockPreText) : ?>
    <p class="srfrPreText"><?php echo $this->feedsBlockPreText; ?></p>
    <?php endif; ?>
  
    <table width="100%" class="srfrList">
      
    <?php
    $rssfeedrow = 1;
    foreach($this->rssfeedoutput as $key => $feed):
        if ($rssfeedrow %2 ) {
            echo "<tr>";
        }
   
    ?>
  
        <td class="srfrRow<?php echo $key%2; ?>">
    <?php if($this->feedItemTitle) : ?>
            <h3><a target="_blank" href="<?php echo $feed->itemLink; ?>"><?php echo $feed->itemTitle; ?></a></h3>
    <?php endif; ?>
          
    <?php if($this->feedTitle) : ?>
            <span class="srfrFeedSource">
                <a target="_blank" href="<?php echo $feed->siteURL; ?>"><?php echo $feed->feedTitle; ?></a>
            </span>
    <?php endif; ?>

    <?php if($this->feedItemDate) : ?>
            <span class="srfrFeedItemDate"><?php echo $feed->itemDate; ?></span>
    <?php endif; ?>          

    <?php if($this->feedItemDescription || $feed->feedImageSrc) : ?>
            <p>
                <?php if($feed->feedImageSrc) : ?>
                <a target="_blank" href="<?php echo $feed->itemLink; ?>">
                    <img class="srfrImage" src="<?php echo $feed->feedImageSrc; ?>" alt="<?php echo $feed->itemTitle; ?>" />
                </a>
                <?php endif; ?>
              
                <?php if($this->feedItemDescription) : ?>
                <?php echo $feed->itemDescription; ?>
                <?php endif; ?>
            </p>
    <?php endif; ?>
          
    <?php if($this->feedItemReadMore) : ?>
            <span class="srfrReadMore">
                <a target="_blank" href="<?php echo $feed->itemLink; ?>"><?php echo Text::_('MOD_JW_SRFR_READ_MORE'); ?></a>
            </span>
    <?php endif; ?>
          
            <span class="clr"></span>
        </td>
  
    <?php
    $rssfeedrow++;
    if ($rssfeedrow %2 ) {
        echo "</tr>";
    }
    endforeach;
    ?>  
  
    </table>

    <?php if($this->feedsBlockPostText) : ?>
    <p class="srfrPostText"><?php echo $this->feedsBlockPostText; ?></p>
    <?php endif; ?>
  
    <?php if($this->feedsBlockPostLink) : ?>
    <p class="srfrPostTextLink"><a href="<?php echo $this->feedsBlockPostLinkURL; ?>"><?php echo $this->feedsBlockPostLinkTitle; ?></a></p>
    <?php endif; ?>
</div>

<div class="clr"></div>
</div>
