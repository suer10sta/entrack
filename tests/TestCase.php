<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guardAgainstNonTestingDatabase();
    }

    /**
     * Fail-safe: destructive testing traits (RefreshDatabase, ...) must NEVER
     * run against the development or production database.
     *
     * This runs before every test. If .env.testing is missing, broken or
     * misconfigured, the suite aborts with a clear exception instead of
     * wiping the wrong database.
     */
    protected function guardAgainstNonTestingDatabase(): void
    {
        if (! $this->app->environment('testing')) {
            throw new RuntimeException(
                'Refusing to run tests: APP_ENV is ['.$this->app->environment().'], expected [testing].'
            );
        }

        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");

        $isInMemorySqlite = $database === ':memory:';
        $isDedicatedTestingDb = is_string($database) && preg_match('/(_test|_testing)$/', $database) === 1;

        if (! $isInMemorySqlite && ! $isDedicatedTestingDb) {
            throw new RuntimeException(
                "Refusing to run tests: database [{$database}] on connection [{$connection}] does not look like a dedicated testing database (expected a name ending in _test/_testing, or :memory:)."
            );
        }

        $devDatabase = $this->developmentDatabaseName();
        if ($devDatabase !== null && $database === $devDatabase) {
            throw new RuntimeException(
                "Refusing to run tests: database [{$database}] is the development database declared in .env."
            );
        }
    }

    /**
     * Read the development database name straight from the base .env file
     * (NOT .env.testing), so a missing/broken .env.testing can never
     * silently redirect tests at the dev database.
     */
    protected function developmentDatabaseName(): ?string
    {
        $envPath = $this->app->basePath('.env');

        if (! is_file($envPath) || ! is_readable($envPath)) {
            return null;
        }

        $contents = file_get_contents($envPath);

        if ($contents === false || preg_match('/^DB_DATABASE=(.*)$/m', $contents, $m) !== 1) {
            return null;
        }

        $value = trim(trim($m[1]), '"\'');

        return $value === '' ? null : $value;
    }
}
