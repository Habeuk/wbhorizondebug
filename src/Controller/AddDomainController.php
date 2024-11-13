<?php
declare(strict_types = 1);

namespace Drupal\wbhorizondebug\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\lesroidelareno\lesroidelareno;
use Drupal\domain_access\DomainAccessManagerInterface;

/**
 * Returns responses for wbhorizondebug routes.
 */
final class AddDomainController extends ControllerBase {
  
  /**
   * Builds the response.
   */
  public function __invoke($entity_type_id, $entity_id): array {
    if (!empty($entity_id) && !empty($entity_type_id)) {
      $storage = $this->entityTypeManager()->getStorage($entity_type_id)->load($entity_id);
      if (!$storage)
        $this->messenger()->addError("L'entite $entity_type_id n'existe pas");
      if ($storage) {
        $entity = $storage->load($entity_id);
        if (!$entity) {
          $this->messenger()->addError("L'entite $entity_type_id ne contient pas d'id : $entity_id ");
        }
        if ($entity) {
          /**
           *
           * @var \Drupal\webform\Entity\Webform $entity
           */
          $domain_sting = $entity->getThirdPartySetting('webform_domain_access', DomainAccessManagerInterface::DOMAIN_ACCESS_FIELD);
          if (empty($domain_sting)) {
            $entity->setThirdPartySetting('webform_domain_access', DomainAccessManagerInterface::DOMAIN_ACCESS_FIELD, lesroidelareno::getCurrentDomainId());
            $entity->save();
            $this->messenger()->addStatus("Le domaine '" . lesroidelareno::getCurrentDomainId() . "' a été ajouté sur l'entite $entity_id ");
          }
        }
      }
    }
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!')
    ];
    return $build;
  }
}
