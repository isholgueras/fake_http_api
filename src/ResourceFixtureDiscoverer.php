<?php

declare(strict_types=1);

namespace Drupal\fake_http_api;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Discovers fake HTTP API fixtures shipped by enabled modules.
 *
 * Convention:
 *   Per enabled module, any files under:
 *     tests/fixtures/fake_http_api/
 *   are considered fixtures. Supported file types: .json, .yaml, .yml
 *
 * This class only discovers and parses fixtures. It does not enforce any
 * pairing/matching conventions between requests and responses — that is the
 * responsibility of a higher-level component.
 */
final class ResourceFixtureDiscoverer {

  /**
   * Relative path inside a module where fixtures may live.
   */
  private const FIXTURE_RELATIVE_DIR = 'tests/fixtures/fake_http_api';

  public function __construct(
    private readonly ModuleHandlerInterface $moduleHandler,
  ) {}

  public function discover(): array {
    $results = [];

    // getModuleList() only includes enabled modules.
    foreach ($this->moduleHandler->getModuleList() as $extension) {
      $modulePath = $extension->getPath();
      if ($modulePath === NULL || $modulePath === '') {
        continue;
      }

      $fixturesDir = $modulePath . '/' . self::FIXTURE_RELATIVE_DIR;
      if (!is_dir($fixturesDir)) {
        continue;
      }

      $files = $this->collectFixtureFiles($fixturesDir);

      // Skip modules that have the directory but no supported files.
      if ($files === []) {
        continue;
      }
      foreach ($files as $file) {
        $results[] = $file;
      }
    }

    return $results;
  }

  private function collectFixtureFiles(string $fixturesDir): array {
    $out = [];

    $iterator = new \RecursiveIteratorIterator(
      new \RecursiveDirectoryIterator(
        $fixturesDir,
        \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS
      ),
      \RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($iterator as $fileInfo) {
      if (!$fileInfo instanceof \SplFileInfo || !$fileInfo->isFile()) {
        continue;
      }

      $ext = strtolower($fileInfo->getExtension());
      if (!in_array($ext, ['json', 'yml', 'yaml'], TRUE)) {
        continue;
      }

      $path = $fileInfo->getPathname();
      $raw = @file_get_contents($path);
      if ($raw === FALSE) {
        // Could not read — skip.
        continue;
      }

      $format = $ext === 'json' ? 'json' : 'yaml';
      $parsed = NULL;

      if ($format === 'json') {
        $parsed = json_decode($raw, TRUE);
        if (!is_string($parsed['response']['body'])) {
          $parsed['response']['body'] = json_encode($parsed['response']['body']);
        }
        if (json_last_error() !== JSON_ERROR_NONE) {
          $parsed = NULL;
        }

      }
      else {
        $parsed = Yaml::parse($raw);
      }

      $out[] = [
        'path' => $path,
        'filename' => $fileInfo->getBasename(),
        'fixture' => $parsed,
      ];
    }

    // Provide stable order: depth-first path order.
    usort($out, static function (array $a, array $b): int {
      return strcmp($a['path'], $b['path']);
    });

    return $out;
  }

}
