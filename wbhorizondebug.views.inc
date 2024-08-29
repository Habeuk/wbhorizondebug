<?php

/**
 * Implements hook_views_data_alter().
 */
function wbhorizondebug_views_data_alter(array &$data) {
  /**
   * Ajout du filtre de domain pour les produits.
   * on identifie notre filtre avec 'product_field_domain_access'.
   * la colonne est definie : 'field'
   * la requete est cree par 'id'
   */
  if (!empty($data['commerce_product_field_data'])) {
    $data['commerce_product_field_data']['product_promotion'] = [
      'title' => t('Promotion/discount (test)'),
      'group' => t('promotion (test)'),
      'filter' => [
        'field' => 'product_id',
        'id' => 'wbhorizondebug_filter_product_by_promotion',
        'title' => t(' Product has promotion (test)'),
        'help' => t(' allows you to filter products that may have a valid promotion (test)')
      ]
    ];
  }
}