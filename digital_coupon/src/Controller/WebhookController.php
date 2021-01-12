<?php

namespace Drupal\digital_coupon\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\digital_coupon\WebhooksService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Webhook.
 *
 * @package Drupal\digital_coupon\Controller
 */
class WebhookController extends ControllerBase {

  /**
   * The webhooks service.
   *
   * @var \Drupal\digital_coupon\WebhooksService
   */
  protected $webhooksService;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * WebhookController constructor.
   *
   * @param \Drupal\webhooks\WebhooksService $webhooks_service
   *   The webhooks service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger channel factory.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   */
  public function __construct(
      WebhooksService $webhooks_service,
      EntityTypeManagerInterface $entity_type_manager,
      LoggerChannelFactoryInterface $logger_factory,
      MessengerInterface $messenger
  ) {
    $this->webhooksService = $webhooks_service;
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger_factory->get('webhooks');
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('webhooks.service'),
      $container->get('entity_type.manager'),
      $container->get('logger.factory'),
      $container->get('messenger')
    );
  }

  /**
   * Access check callback.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current account.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   A successful access result.
   */
  public function access(AccountInterface $account) {
    return AccessResult::allowed();
  }

  /**
   * Toggle the active state.
   *
   * @param mixed $id
   *   The id of the entity given by route url.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function toggleActive($id) {
    $webhooks_storage = $this->entityTypeManager->getStorage('webhook_config');
    /** @var \Drupal\digital_coupon\Entity\WebhookConfig $webhook_config */
    $webhook_config = $webhooks_storage->load($id);
    $webhook_config->setStatus(!$webhook_config->status());
    $webhook_config->save();

    $this->messenger()->addStatus($this->t(
      'Webhook <a href=":url">@webhook</a> has been %status.',
      [
        ':url' => Url::fromRoute(
          'entity.webhook_config.edit_form',
          [
            'webhook_config' => $webhook_config->getId(),
          ]
        )->toString(),
        '@webhook' => $webhook_config->getLabel(),
        '%status' => $webhook_config->status() ? 'enabled' : 'disabled',
      ]
    ));
    return $this->redirect("entity.webhook_config.collection");
  }

}
