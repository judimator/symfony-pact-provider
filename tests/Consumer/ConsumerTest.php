<?php

declare(strict_types=1);

namespace App\Tests\Consumer;

use GuzzleHttp\Psr7\Uri;
use PhpPact\Standalone\ProviderVerifier\Model\Config\PublishOptions;
use PhpPact\Standalone\ProviderVerifier\Model\Source\Broker;
use PhpPact\Standalone\ProviderVerifier\Model\VerifierConfig;
use PhpPact\Standalone\ProviderVerifier\Verifier;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Process\Process;

class ConsumerTest extends WebTestCase
{
    private Process $process;

    protected function setUp(): void
    {
        $publicPath = __DIR__ . '/../../public';

        $this->process = new Process(['php', '-S', '127.0.0.1:7202', '-t', $publicPath]);

        $this->process->start();
        $this->process->waitUntil(function (): bool {
            $fp = @fsockopen('127.0.0.1', 7202);
            $isOpen = is_resource($fp);
            if ($isOpen) {
                fclose($fp);
            }

            return $isOpen;
        });
    }

    protected function tearDown(): void
    {
        $this->process->stop();
    }

    public function testVerifyFile(): void
    {
        $config = new VerifierConfig();
        $config->setLogLevel('debug');

        $config
            ->getProviderInfo()
            ->setName('fake-provider')
            ->setHost('localhost')
            ->setPort(7202)
            ->setScheme('http')
            ->setPath('/');

        $config->getProviderState()->setStateChangeUrl(new Uri('http://localhost:7202/states'));
        $config->getVerificationOptions()->setRequestTimeout(50000);

        $verifier = new Verifier($config);
        $verifier->addFile(__DIR__ . '/pacts/fake-consumer-fake-provider.json');

        $verifyResult = $verifier->verify();

        static::assertTrue($verifyResult);
    }

    public function testVerifyBroker(): void
    {
        $options = new PublishOptions();
        $options
            ->setProviderVersion('fake-git-sha-for-demo-456')
            ->setProviderBranch('fake-feature-branch');

        $config = new VerifierConfig();
        $config
            ->setLogLevel('debug')
            ->setPublishOptions($options);

        $config
            ->getProviderInfo()
            ->setName('fake-provider')
            ->setHost('localhost')
            ->setPort(7202)
            ->setScheme('http')
            ->setPath('/');

        $broker = new Broker();
        $broker
            ->setUrl(new Uri('http://broker'))
            ->setUsername('username')
            ->setPassword('password');

        $consumerVersionSelector = $broker->getConsumerVersionSelectors();
        $consumerVersionSelector->addSelector('{"consumer":"fake-consumer", "latest": true}');

        $config->getProviderState()->setStateChangeUrl(new Uri('http://localhost:7202/states'));

        $verifier = new Verifier($config);
        $verifier->addBroker($broker);

        $verifyResult = $verifier->verify();

        static::assertTrue($verifyResult);
    }
}
