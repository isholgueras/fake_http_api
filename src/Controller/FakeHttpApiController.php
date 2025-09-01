<?php

declare(strict_types=1);

namespace Drupal\fake_http_api\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\fake_http_api\FakeRequestMatcher;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FakeHttpApiController implements ContainerInjectionInterface {

  public function __construct(private readonly FakeRequestMatcher $fakeRequestMatcher) {}

  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get(FakeRequestMatcher::class)
    );
  }

  public function proxy(Request $request): Response {
    return $this->fakeRequestMatcher->match($request);
  }

}
