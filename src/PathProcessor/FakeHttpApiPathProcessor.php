<?php

declare(strict_types=1);

namespace Drupal\fake_http_api\PathProcessor;

use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a path processor to rewrite URLs.
 *
 * As the route system does not allow arbitrary number of parameters convert
 * the resource path to a query parameter on the request.
 */
class FakeHttpApiPathProcessor implements InboundPathProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function processInbound($path, Request $request) {
    if (str_starts_with($path, '/fake-http-api') && !$request->query->has('resource')) {
      $resource = preg_replace('|^\/fake-http-api\/|', '', $path);
      $request->query->set('resource', $resource);
      return '/fake-http-api';
    }
    return $path;
  }

}
