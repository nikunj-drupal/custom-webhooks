<?php

/**
 * @file
 */

/**
 * Implements hook_views_data_alter().
 */
function digital_coupon_views_data_alter(array &$data) {
  $data['node']['coupon_claimed'] = [
    'title' => t('Claimed'),
    'field' => [
      'title' => t('Claimed'),
      'help' => t('Claimed Coupons.'),
      'id' => 'coupon_claimed',
    ],
  ];
  $data['node']['coupon_unclaimed'] = [
    'title' => t('Unclaimed'),
    'field' => [
      'title' => t('unclaimed Coupon'),
      'help' => t('Unclaimed Coupons.'),
      'id' => 'coupon_unclaimed',
    ],
  ];
}
