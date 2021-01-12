<?php

namespace Drupal\digital_coupon\Plugin\QueueWorker;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\digital_coupon\Entity\WebhookConfig;
use Drupal\digital_coupon\Event\ReceiveEvent;
use Drupal\digital_coupon\Event\WebhookEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A Webhook dispatcher that dispatches webhooks on CRON run.
 *
 * @QueueWorker(
 *   id = "webhooks_dispatcher",
 *   title = @Translation("Webhooks Dispatcher"),
 *   cron = {"time" = 60}
 * )
 */
class WebhookDispatcher extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The event dispatcher.
   *
   * @var \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher
   */
  protected $eventDispatcher;

  /**
   * WebhookDispatcherBase constructor.
   *
   * @param \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher $event_dispatcher
   */
  public function __construct(ContainerAwareEventDispatcher $event_dispatcher) {
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * {@inheritdoc }.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('event_dispatcher')
    );
  }

  /**
   * {@inheritdoc }.
   */
  public function processItem($data) {
    /** @var \Drupal\digital_coupon\Entity\WebhookConfig $webhook_config */
    $webhook_config = WebhookConfig::load($data['id']);
    if ($webhook_config) {
      $this->eventDispatcher->dispatch(
        WebhookEvents::RECEIVE,
        new ReceiveEvent($webhook_config, $data['webhook'])
      );
    }
  }

}
