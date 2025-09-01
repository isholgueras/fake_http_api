<?php

declare(strict_types=1);

namespace Drupal\Tests\fake_http_api\Functional;

use Drupal\Core\Url;
use Drupal\Tests\ApiRequestTrait;
use Drupal\Tests\BrowserTestBase;

/**
 * Basic browser test scaffold for the fake_http_api module.
 *
 * @group fake_http_api
 */
final class FakeHttpApiBrowserTest extends BrowserTestBase {

  use ApiRequestTrait;

  /**
   * {@inheritdoc}
   *
   * Enable only the module under test for now.
   */
  protected static $modules = [
    'fake_http_api',
  ];

  /**
   * {@inheritdoc}
   *
   * Use a minimal core theme to avoid unrelated dependencies.
   */
  protected $defaultTheme = 'stark';

  /**
   * Placeholder smoke test.
   */
  public function testSimpleGetRequests(): void {
    $account = $this->drupalCreateUser(['access content']);
    $this->drupalLogin($account);
    $this->drupalGet('/fake-http-api/200/json');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseContains('{"it":"works"}');
    $this->drupalGet('/fake-http-api/200/yaml');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseContains('{"it":"works"}');
    $this->drupalGet('/fake-http-api/500');
    $this->assertSession()->statusCodeEquals(500);
    $this->assertSession()->responseContains(
      '{"error":"something went wrong"}'
    );
  }

  public function testSimplePostRequest(): void {
    $account = $this->drupalCreateUser(['access content']);
    $this->drupalLogin($account);
    $this->makeApiRequest('POST', Url::fromUserInput('/fake-http-api/create-user'), [
      'json' => [
        'name' => 'John Doe',
        'email' => 'john@example.com',
      ],
      'headers' => [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
      ],
    ]);

    $this->assertSession()->statusCodeEquals(201);
    $this->assertSession()->responseContains('{"id": "u_123"}');
  }

}
