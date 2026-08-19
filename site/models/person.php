<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\PersonModel;

if (!class_exists('sportsmanagementModelPerson', false)) {
    class_alias(PersonModel::class, 'sportsmanagementModelPerson');
}
