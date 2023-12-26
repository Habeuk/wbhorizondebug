<?php

namespace Drupal\wbhorizondebug\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Returns responses for wbhorizondebug routes.
 */
class WbhorizondebugController extends ControllerBase {
  
  /**
   * Permet de recuperer les produits avec un discount.
   * On va essayer d'utiliser les caches de
   */
  function getDisountProducts() {
    $this->testGetQuerieAfftectd();
    return [];
  }
  
  protected function testGetQuerieAfftectd() {
    $promotion = $this->entityTypeManager()->getStorage('commerce_promotion')->load(2);
    /** @var \Drupal\affected_by_promotion\AffectedEntitiesManager $mng */
    $mng = \Drupal::service('affected_by_promotion.affected_entities_manager');
    /** @var \Drupal\Core\Database\Query\SelectInterface $q */
    $q = $mng->getAffectedEntitiesQuery($promotion, 'commerce_product');
    dd($q);
    $products = $q->execute();
  }
  
  /**
   * Le but est de deplacer les images du champs 'field_images' au
   * 'field_gallery' dans le produit type de vetement: vetements.
   */
  public function MoveImageToGallerieField() {
    $date = new DrupalDateTime('2023-12-20');
    $query = $this->entityTypeManager()->getStorage('commerce_product')->getQuery();
    $query->condition('created', $date->getTimestamp(), '>');
    $ids = $query->execute();
    $results = [];
    if ($ids) {
      foreach ($this->entityTypeManager()->getStorage('commerce_product')->loadMultiple($ids) as $commerce_product) {
        /**
         *
         * @var \Drupal\commerce_product\Entity\Product $commerce_product
         */
        if ($commerce_product->hasField('field_images') && $commerce_product->hasField('field_gallery')) {
          $field_images = $commerce_product->get('field_images')->getValue();
          $field_gallery = $commerce_product->get('field_gallery')->getValue();
          $addNewGallerie = false;
          // dump($field_images);
          // dump($field_gallery);
          // mise à jour du champs gallery
          foreach ($field_images as $image) {
            if (!$this->checkIfFilesIsSaved($image, $field_gallery)) {
              $addNewGallerie = true;
              $field_gallery[] = [
                'target_id' => $image['target_id'],
                "display" => "1",
                "description" => $image['alt']
              ];
            }
          }
          if ($field_gallery && $addNewGallerie) {
            $commerce_product->get('field_gallery')->setValue($field_gallery);
            $commerce_product->save();
            $dateCreate = new DrupalDateTime();
            $dateCreate->setTimestamp($commerce_product->getCreatedTime());
            $date_ls = $dateCreate->format('Y-m-d');
            $results[$commerce_product->id() . ' - ' . $date_ls] = [
              'label' => $commerce_product->label(),
              'date' => $date_ls,
              'field_gallery' => $field_gallery,
              'field_images' => $field_images
            ];
          }
        }
      }
    }
    dump($results);
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!')
    ];
    
    return $build;
  }
  
  /**
   * Permet de se rassurer que le fichier n'est pas dans le champs gallerie.
   */
  protected function checkIfFilesIsSaved($image, $field_gallery) {
    foreach ($field_gallery as $gallery) {
      if ($gallery['target_id'] == $image['target_id'])
        return true;
    }
    return false;
  }
  
}
