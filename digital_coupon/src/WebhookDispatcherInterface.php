<?php

namespace Drupal\digital_coupon;

use Drupal\digital_coupon\Entity\WebhookConfig;

/**
 * Webhook dispatchers control triggering outbound webhook events.
 *
 * @package Drupal\digital_coupon
 */
interface WebhookDispatcherInterface {

  /**
   * Load multiple WebhookConfigs by event.
   *
   * @param string $event
   *   An event string in the form of entity:entity_type:action,
   *   e.g. 'entity:user:create', 'entity:user:update' or 'entity:user:delete'.
   * @param string $type
   *   A type string, e.g. 'outgoing' or 'incoming'.
   *
   * @return \Drupal\digital_coupon\Entity\WebhookConfigInterface[]
   *   An array of WebhookConfig entities.
   */
  public function loadMultipleByEvent($event, $type = 'outgoing');

  /**
   * Trigger all webhook subscriptions associated with the given event.
   *
   * @param \Drupal\digital_coupon\Webhook $webhook
   *   The webhook object.
   * @param string $event
   *   Identifier of a particular webhook event, e.g. entity:node:create,
   *   entity:user:update or entity:taxonomy_term:delete.
   */
  public function triggerEvent(Webhook $webhook, $event);

  /**
   * Send a webhook.
   *
   * @param \Drupal\digital_coupon\Entity\WebhookConfig $webhook_config
   *   A webhook config entity.
   * @param \Drupal\digital_coupon\Webhook $webhook
   *   A webhook object.
   */
  public function send(WebhookConfig $webhook_config, Webhook $webhook);

}
