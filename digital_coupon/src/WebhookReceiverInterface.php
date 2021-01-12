<?php

namespace Drupal\digital_coupon;

/**
 * Webhook receivers catch incoming events and trigger an internal event.
 *
 * The internal event allows any module in the Drupal site to react to remote
 * operations.
 *
 * @package Drupal\digital_coupon
 */
interface WebhookReceiverInterface {

  /**
   * Receive a webhook.
   *
   * @param string $name
   *   The machine name of a webhook.
   *
   * @return \Drupal\digital_coupon\Webhook
   *   A webhook object.
   *
   * @throws \Drupal\digital_coupon\Exception\WebhookIncomingEndpointNotFoundException
   *   Thrown when the webhook endpoint is not found.
   */
  public function receive($name);

}
