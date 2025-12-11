<?php

namespace Drupal\site_status_json\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class SiteStatusJsonController extends ControllerBase {

  /**
   *
   */
  public function status(Request $request): JsonResponse {
    $system_manager = \Drupal::service('system.manager');
    $checks = $system_manager->listRequirements();

    $details = [];
    foreach ($checks as $name => $item) {
      $details[$name] = $item['severity'] ?? 0;
    }

    $status = 'ok';
    foreach ($details as $v) {
      if ($v < 0) {
        $status = 'issues found; please check';
        break;
      }
    }

    $versions = [];
    $versions['drupal'] = \Drupal::VERSION;
    $versions['php'] = phpversion();
    $versions['webserver'] = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';

    $db = \Drupal::database();
    $query = $db->query('SELECT VERSION()');
    $versions['db'] = $query->fetchField();

    $release_file = DRUPAL_ROOT . '/../release.txt';
    $release = file_exists($release_file) ? file_get_contents($release_file) : FALSE;

    $response = [
      'status' => $status,
      'generated' => date('Y-m-d H:i:s'),
      'details' => $details,
      'versions' => $versions,
      'release' => $release,
    ];

    return new JsonResponse($response);
  }

}
