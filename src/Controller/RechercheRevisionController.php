<?php

namespace Drupal\wbhorizondebug\Controller;

use Drupal\Core\Database\Connection;
use Stephane888\DrupalUtility\HttpResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for wbhorizondebug routes.
 */
class RechercheRevisionController extends ControllerBase {
  
  /**
   * The table that stores properties, if the entity has multilingual support.
   *
   * @var string
   */
  protected $dataTable;
  
  function __construct(Connection $database) {
    $this->database = $database;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container) {
    return new static($container->get('database'));
  }
  
  function FindEntitiesWithError($type) {
    $query = $this->entityTypeManager()->getStorage($type)->getQuery();
    $query->pager(0, 10);
  }
  
  /**
   *
   * @param int $id
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  function RechercheRevision(int $id) {
    /**
     *
     * @var \Drupal\Core\Entity\Sql\SqlContentEntityStorage $storage
     */
    $storage = $this->entityTypeManager()->getStorage('paragraph');
    dump($storage->getRevisionTable());
    $paragraph = $this->entityTypeManager()->getStorage('paragraph')->load($id);
    dump($paragraph->toArray());
    dump($paragraph->createDuplicate()->toArray());
    $revisionsIds = $this->listeDesRevisions($paragraph, $storage);
    dump($revisionsIds);
    return HttpResponse::response([]);
  }
  
  /**
   * --
   */
  function listeDesRevisions($entity, SqlContentEntityStorage $storage) {
    return $this->database->query('SELECT [revision_id] FROM {' . $storage->getRevisionTable() . '} WHERE [id] = :id ORDER BY [revision_id]', [
      ':id' => $entity->id()
    ])->fetchCol();
  }
  
}

  