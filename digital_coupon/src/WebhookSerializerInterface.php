<?php

namespace Drupal\digital_coupon;

use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

/**
 * Webhook receivers catch incoming events and trigger an internal event.
 *
 * The internal event allows any module in the Drupal site to react to remote
 * operations.
 *
 * @package Drupal\digital_coupon
 */
interface WebhookSerializerInterface extends EncoderInterface, DecoderInterface {
}
