<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use JsonMapper\Handler\FactoryRegistry;
use JsonMapper\Handler\PropertyMapper;
use JsonMapper\JsonMapperBuilder;
use Koba\DigitaalOndertekenen\Auth\TokenManager;
use Koba\DigitaalOndertekenen\Cache\InMemoryTokenCache;
use Koba\DigitaalOndertekenen\Cache\TokenCacheInterface;
use Koba\DigitaalOndertekenen\Http\ApiTransport;
use Koba\DigitaalOndertekenen\Http\BearerTokenMiddleware;
use Koba\DigitaalOndertekenen\Oauth\DigitaalOndertekenen;
use Koba\DigitaalOndertekenen\Oauth\Vlaanderen;
use Psr\Http\Client\ClientInterface as Psr18ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use SensitiveParameter;

/**
 * Builds a configured Digitaal Ondertekenen client.
 *
 * The factory is the composition root for the OAuth providers, token manager,
 * authenticated transport, and PSR implementations discovered at runtime.
 */
final class ClientFactory
{
    public function __construct(
        private ?Psr18ClientInterface $httpClient = null,
        private ?RequestFactoryInterface $requestFactory = null,
        private ?StreamFactoryInterface $streamFactory = null,
    ) {
    }

    public function create(
        string $vlaanderenClientId,
        #[SensitiveParameter] string $vlaanderenClientSecret,
        string $digitaalOndertekenenClientId,
        #[SensitiveParameter] string $digitaalOndertekenenClientSecret,
        Environment $environment = Environment::PRODUCTION,
        ?TokenCacheInterface $tokenCache = null,
    ): Client {
        $tokenCache ??= new InMemoryTokenCache();

        $vlaanderenProvider = new Vlaanderen([
            'clientId' => $vlaanderenClientId,
            'clientSecret' => $vlaanderenClientSecret,
            'environment' => $environment,
        ]);

        $digitaalOndertekenenProvider = new DigitaalOndertekenen([
            'clientId' => $digitaalOndertekenenClientId,
            'clientSecret' => $digitaalOndertekenenClientSecret,
            'environment' => $environment,
        ]);

        $tokenManager = new TokenManager(
            $vlaanderenProvider,
            $digitaalOndertekenenProvider,
            $environment->getDefaultProfileName(),
            $tokenCache,
        );

        $authenticatedHttpClient = new BearerTokenMiddleware(
            $this->httpClient ?? Psr18ClientDiscovery::find(),
            $tokenManager,
        );

        $factoryRegistry = FactoryRegistry::withNativePhpClassesAdded();
        $jsonMapper = JsonMapperBuilder::new()
            ->withDocBlockAnnotationsMiddleware()
            ->withNamespaceResolverMiddleware()
            ->withObjectConstructorMiddleware($factoryRegistry)
            ->withPropertyMapper(new PropertyMapper($factoryRegistry))
            ->build();

        $transport = new ApiTransport(
            $authenticatedHttpClient,
            $this->requestFactory ?? Psr17FactoryDiscovery::findRequestFactory(),
            $this->streamFactory ?? Psr17FactoryDiscovery::findStreamFactory(),
            $environment,
            $jsonMapper,
        );

        return new Client($transport);
    }
}
