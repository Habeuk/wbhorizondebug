<?php

namespace Drupal\wbhorizondebug\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\system\Entity\Menu;
use Drupal\Core\Url;

/**
 * Returns responses for wbhorizondebug routes.
 */
class WbhorizondebugController extends ControllerBase {
  
  /**
   * Certains blocs ont une visibite defini su un domaine X mais utilise un
   * theme Y.
   * Cela est une erreur de configuration.
   *
   * @param string $action
   */
  function update_block_theme_visibility($action = 'see') {
    $blocks = \Drupal\block\Entity\Block::loadMultiple();
    $ids = [];
    $k = 0;
    foreach ($blocks as $block) {
      $theme = $block->get('theme');
      $visibility = $block->get('visibility');
      if (!empty($visibility['domain'])) {
        if (!in_array($theme, $visibility['domain']['domains'])) {
          $ids[] = $block->id();
          if ($action == 'see')
            $this->messenger()->addStatus("Le block : " . $block->id() . "(" . $block->label() . ") a pour theme : " . $theme . " et pour visibilité : " . implode("; ", $visibility['domain']['domains']));
          $k++;
        }
      }
    }
    $this->messenger()->addStatus($k . " blocks corrompus sur " . count($blocks));
    if ($action == 'delete' && $ids) {
      $this->runBatch($ids);
      $urlRedisrect = Url::fromRoute('wbhorizondebug.update_block_theme_visibility', [
        'action' => 'see'
      ]);
      return batch_process($urlRedisrect);
    }
    return [];
  }
  
  protected function runBatch(array $ids) {
    $batch = [
      'title' => "Delete blocs encours.",
      'init_message' => "Import des données encours ...",
      'finished' => self::class . '::import_run_all',
      'operations' => []
    ];
    foreach ($ids as $id) {
      $batch['operations'][] = [
        self::class . '::_batch_delete_block',
        [
          $id
        ]
      ];
    }
    
    batch_set($batch);
  }
  
  /**
   *
   * @param string $id
   * @param array $context
   */
  public static function _batch_delete_block($id, &$context) {
    $block = \Drupal\block\Entity\Block::load($id);
    if ($block) {
      $block->delete();
      $context['message'] = 'Suppresion du bloc : ' . $id;
    }
    else {
      $context['message'] = 'Le bloc : ' . $id . " n'existe plus ";
    }
  }
  
  /**
   *
   * @param string $success
   * @param string $results
   * @param string $operations
   */
  static public function import_run_all($success, $results, $operations) {
    if ($success)
      \Drupal::messenger()->addStatus("Suppresion de tous les blocs deffectueux");
    else
      \Drupal::messenger()->addError("Erreur de suppression");
  }
  
  /**
   * permet de deplacer third_party_settings.lesroidelareno.domain_id vers
   * third_party_settings.wb_horizon_public.domain_id
   * car le module lesroidelareno n'est pas un module client
   *
   * @return array
   */
  function MoveLesroidelarenoWb_horizon_publicDomain_id() {
    $Menus = \Drupal\system\Entity\Menu::loadMultiple();
    $k = 0;
    foreach ($Menus as $menu) {
      $domain_id = $menu->getThirdPartySetting('lesroidelareno', 'domain_id');
      if ($domain_id) {
        $k++;
        $menu->setThirdPartySetting('wb_horizon_public', 'domain_id', $domain_id);
        $menu->unsetThirdPartySetting('lesroidelareno', 'domain_id');
        $menu->save();
      }
    }
    $this->messenger()->addMessage("Mise à jour de menus : " . $k);
    return [];
  }
  
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
    $date = new DrupalDateTime('2023-12-01');
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
