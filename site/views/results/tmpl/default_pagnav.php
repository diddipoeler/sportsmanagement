<?php
/**
 * SportsManagement matchday page navigation.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\RoundPaginationHelper;
use Joomla\CMS\Factory;

if (!class_exists(RoundPaginationHelper::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Helper/RoundPaginationHelper.php';
}
?>
<!-- matchdays pageNav -->
<br/>
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="defaultpagenav">
    <table class="table">
        <tr>
            <td>
                <?php
                if (!empty($this->rounds)) {
                    $pageNavigation = "<div class='pagenav'>";
                    $pageNavigation .= RoundPaginationHelper::pagenav(
                        $this->project,
                        Factory::getApplication()->getInput()->getInt('cfg_which_database', 0),
                        Factory::getApplication()->getInput()->getInt('s', 0)
                    );
                    $pageNavigation .= '</div>';
                    echo $pageNavigation;
                }
                ?>
            </td>
        </tr>
    </table>
    <!-- matchdays pageNav END -->
</div>
