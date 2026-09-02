<?php
/**
 * Legacy compatibility bridge for the Joomla 5/6 dependent multi-select field.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\MultidependsqlField;

if (!class_exists(MultidependsqlField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/MultidependsqlField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(MultidependsqlField::class) && !class_exists('JFormFieldMultiDependSQL', false)) {
    class_alias(MultidependsqlField::class, 'JFormFieldMultiDependSQL');
}
