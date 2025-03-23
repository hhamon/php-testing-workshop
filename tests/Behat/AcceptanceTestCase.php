<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use App\Kernel;
use Behat\Hook\AfterScenario;
use Behat\Hook\BeforeScenario;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Browser\KernelBrowser;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

abstract class AcceptanceTestCase extends WebTestCase
{
    use Factories;

    use HasBrowser {
        browser as parentBrowser;
    }

    use ResetDatabase;

    protected ?KernelBrowser $browser = null;

    #[BeforeScenario]
    public function onBeforeScenario(): void
    {
    }

    #[AfterScenario]
    public function onAfterScenario(): void
    {
        self::_resetBrowserClients();
    }

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    protected function browser(array $options = [], array $server = []): KernelBrowser
    {
        $options = \array_merge(
            ['environment' => 'test', 'debug' => true],
            $options,
        );

        if (!$this->browser instanceof KernelBrowser) {
            $this->browser = $this->parentBrowser($options, $server);
        }

        return $this->browser;
    }
}
