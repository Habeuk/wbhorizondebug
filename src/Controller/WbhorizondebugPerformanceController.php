<?php

namespace Drupal\wbhorizondebug\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\monitoring_drupal\Services\TimerMonitoring;
use Stephane888\DrupalUtility\HttpResponse;
use Drupal\Core\DrupalKernel;
use Drupal\Core\Cache\Cache;

/**
 * Returns responses for wbhorizondebug routes.
 */
class WbhorizondebugPerformanceController extends ControllerBase {
  protected static $key = 'drupal_flush_all_caches__';
  
  /**
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  function Performance() {
    $result = [];
    $code = 200;
    TimerMonitoring::start(self::$key);
    $this->test__drupal_flush_all_caches($result);
    $result['end'] = TimerMonitoring::stop(self::$key);
    return HttpResponse::response($result, $code);
  }
  
  /**
   * test de fonctionnement de la fonction : drupal_flush_all_caches
   *
   * @see \drupal_flush_all_caches()
   */
  protected function test__drupal_flush_all_caches(array &$result, $kernel = null) {
    // This is executed based on old/previously known information if $kernel is
    // not passed in, which is sufficient, since new extensions cannot have any
    // primed caches yet.
    $module_handler = \Drupal::moduleHandler();
    // Flush all persistent caches.
    $module_handler->invokeAll('cache_flush');
    /**
     * Dans Drupal 9, “cache bins” fait référence à un mécanisme utilisé pour
     * stocker et gérer les données mises en cache.
     * La mise en cache est une technique utilisée par les systèmes de gestion
     * de contenu comme Drupal pour améliorer les performances des sites Web en
     * stockant les données fréquemment utilisées en mémoire ou en stockage afin
     * qu'elles puissent être récupérées plus rapidement en cas de besoin.
     * On a 'cache_entity' qui sy
     * retrouve, de plus on a un max row 5000
     * line.
     *
     * @see https://www.tothenew.com/blog/supercharge-your-drupal-9-website-with-custom-cache-bins/
     * @see https://www.drupal.org/project/pcb
     *
     *
     * @var array $cacheBins
     */
    $cacheBins = Cache::getBins();
    foreach (Cache::getBins() as $cache_backend) {
      /**
       *
       * @see \Drupal\Core\Cache\NullBackend
       * @see \Drupal\Core\Cache\ChainedFastBackend
       * @see \Drupal\Core\Cache\DatabaseBackend
       * @var \Drupal\Core\Cache\MemoryBackend $cache_backend
       */
      
      $cache_backend->deleteAll();
    }
    $result['delete_cache_bins'] = TimerMonitoring::read(self::$key);
    // Flush asset file caches.
    // \Drupal::service('asset.css.collection_optimizer')->deleteAll();
    // \Drupal::service('asset.js.collection_optimizer')->deleteAll();
    // _drupal_flush_css_js();
    // // Reset all static caches.
    // drupal_static_reset();
    // $result['delete_cache_styles'] = TimerMonitoring::read(self::$key);
    
    // // Wipe the Twig PHP Storage cache.
    // \Drupal::service('twig')->invalidate();
    
    // $result['delete_twig'] = TimerMonitoring::read(self::$key);
    
    // // Rebuild profile, profile, theme_engine and theme data.
    // \Drupal::service('extension.list.profile')->reset();
    // \Drupal::service('extension.list.theme_engine')->reset();
    // \Drupal::service('theme_handler')->refreshInfo();
    // // In case the active theme gets requested later in the same request we
    // need
    // // to reset the theme manager.
    // \Drupal::theme()->resetActiveTheme();
    
    // $result['delete_themes'] = TimerMonitoring::read(self::$key);
  }
  
}