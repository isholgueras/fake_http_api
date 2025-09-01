<?php

namespace Drupal\fake_http_api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class FakeRequestMatcher {

  public function __construct(private ResourceFixtureDiscoverer $resourceFixtureDiscoverer) {}

  public function match(Request $request): Response {
    $resource = $request->get('resource');
    \assert(is_string($resource));
    $fakeResources = $this->resourceFixtureDiscoverer->discover();
    foreach ($fakeResources as $fakeResource) {
      if ($request->getMethod() !== $fakeResource['fixture']['method']) {
        continue;
      }
      if ($resource !== $fakeResource['fixture']['request']['uri']) {
        continue;
      }
      $response = new Response(
        $fakeResource['fixture']['response']['body'],
        $fakeResource['fixture']['response']['status'],
        $fakeResource['fixture']['response']['headers'] ?? []
      );
      break;
    }
    return $response ?? $this->unexistingResource($resource);
  }

  private function unexistingResource(string $resource): Response {
    return new Response(
      sprintf('Fixture for resource "%s" does not exist.', $resource),
      404
    );
  }

}
