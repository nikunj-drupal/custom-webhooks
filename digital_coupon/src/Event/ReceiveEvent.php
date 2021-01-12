<?php

namespace Drupal\digital_coupon\Event;

use Drupal\digital_coupon\Entity\WebhookConfig;
use Drupal\digital_coupon\Webhook;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class ReceiveEvent.
 *
 * @package Drupal\digital_coupon\Event
 */
class ReceiveEvent extends Event {

  /**
   * The webhook.
   *
   * @var \Drupal\digital_coupon\Webhook
   */
  protected $webhook;

  /**
   * The webhook configuration.
   *
   * @var \Drupal\digital_coupon\Entity\WebhookConfig
   */
  protected $webhookConfig;

  /**
   * SendEvent constructor.
   *
   * @param \Drupal\digital_coupon\Entity\WebhookConfig $webhook_config
   *   A webhook configuration entity.
   * @param \Drupal\digital_coupon\Webhook $webhook
   *   A webhook.
   */
  public function __construct(
      WebhookConfig $webhook_config,
      Webhook $webhook
  ) {
    $this->webhookConfig = $webhook_config;
    $this->webhook = $webhook;
  }

  /**
   * Get the webhook.
   *
   * @return \Drupal\digital_coupon\Webhook
   *   The webhook.
   */
  public function getWebhook() {
    return $this->webhook;
  }

  /**
   * Get the webhook configuration.
   *
   * @return \Drupal\digital_coupon\Entity\WebhookConfig
   *   A webhook configuration.
   */
  public function getWebhookConfig() {
    return $this->webhookConfig;
  }

}
