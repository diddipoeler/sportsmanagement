<?php
/**
 * SportsManagement hit list layout for Joomla 5/6.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

$assets = $this->getDocument()->getWebAssetManager();
$assets->useScript('keepalive');
$assets->registerAndUseScript(
    'com_sportsmanagement.site.hitlist',
    'components/com_sportsmanagement/assets/js/hitlist.js',
    ['version' => 'auto'],
    ['defer' => true],
    ['core']
);

$templatesToLoad = ['globalviews'];
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
$uri = Uri::getInstance();
?>
<div class="row">
    <form
        name="adminForm"
        id="adminForm"
        action="<?php echo htmlspecialchars($uri->toString(), ENT_QUOTES, 'UTF-8'); ?>"
        method="post"
        data-jsm-hitlist-form
    >
        <?php
        echo $this->loadTemplate('items');
        echo $this->loadTemplate('jsminfo');
        ?>
    </form>
</div>
