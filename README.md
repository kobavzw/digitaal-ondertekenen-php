# Digitaal Ondertekenen PHP

PHP client for the Vlaamse overheid **Digitaal Ondertekenen** API. It handles
the two-stage OAuth authentication flow and provides typed calls for creating a
document package, uploading a document, configuring its signing workflow, and
opening the integrated signing viewer.

> This is an unofficial client. You need credentials and a configured profile
> for the Digitaal Ondertekenen service to use it.

## Requirements

- PHP 8.1 or newer
- An ACM OAuth client ID and secret
- A Digitaal Ondertekenen client ID and secret
- A PSR-18 HTTP client and PSR-17 HTTP factories

## Installation

Install the library and an HTTP implementation with Composer. Guzzle provides
both the PSR-18 client and the PSR-17 factories required by this package:

```bash
composer require koba/digitaal-ondertekenen-php guzzlehttp/guzzle
```

If your application already supplies compatible PSR-17 and PSR-18
implementations, only install the library.

## Create a client

Keep all four credentials outside your source code, for example in environment
variables:

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Koba\DigitaalOndertekenen\ClientFactory;
use Koba\DigitaalOndertekenen\Environment;

$client = (new ClientFactory())->create(
    vlaanderenClientId: $_ENV['VLAANDEREN_CLIENT_ID'],
    vlaanderenClientSecret: $_ENV['VLAANDEREN_CLIENT_SECRET'],
    digitaalOndertekenenClientId: $_ENV['DIGITAAL_ONDERTEKENEN_CLIENT_ID'],
    digitaalOndertekenenClientSecret: $_ENV['DIGITAAL_ONDERTEKENEN_CLIENT_SECRET'],
    environment: Environment::TEST,
);
```

Use `Environment::TEST` for the test/integration environment and
`Environment::PRODUCTION` for production. Production is the default when the
`environment` argument is omitted.

HTTP requests are made when `send()` is called, not when a call object is
created.

## Complete signing flow

The following example creates a package for one external signer, uploads a PDF,
places a signature field, starts the workflow, and generates a URL for the
integrated signing viewer:

```php
<?php

use Koba\DigitaalOndertekenen\Api\CollaboratorRole;
use Koba\DigitaalOndertekenen\Api\IntegrationResponseType;
use Koba\DigitaalOndertekenen\Api\SignatureLevelOfAssurance;
use Koba\DigitaalOndertekenen\Api\WorkflowMode;

$package = $client->createPackage(
    workflowMode: WorkflowMode::ONLY_OTHERS,
    packageName: 'Agreement 2026-001',
)->send();

$pdf = file_get_contents(__DIR__ . '/agreement.pdf');

if ($pdf === false) {
    throw new RuntimeException('Could not read agreement.pdf');
}

$document = $client->uploadDocument(
    packageId: $package->package_id,
    fileName: 'agreement.pdf',
    contents: $pdf,
    source: 'My application',
)->send();

$client->addWorkflowRecipient(
    packageId: $package->package_id,
    email: 'signer@example.com',
    name: 'Example Signer',
    role: CollaboratorRole::SIGNER,
    signingOrder: 1,
)->send();

$client->addSignatureField(
    packageId: $package->package_id,
    documentId: $document->document_id,
    recipientOrder: 1,
    pageNumber: 1,
    dimensions: [
        'x' => 100.0,
        'y' => 600.0,
        'width' => 180.0,
        'height' => 60.0,
    ],
    levelOfAssurance: SignatureLevelOfAssurance::ELECTRONIC_SIGNATURE,
    fieldName: 'customer_signature',
)->send();

$client->startWorkflow($package->package_id)->send();

$signingUrl = $client->generateIntegrationUrl(
    packageId: $package->package_id,
    documentId: $document->document_id,
    language: 'nl-NL',
    userEmail: 'signer@example.com',
    callbackUrl: 'https://example.com/signing/complete',
    responseType: IntegrationResponseType::PLAIN,
    collapsePanels: true,
    lockPanels: false,
    redirectCallbackUrl: true,
)->send();

header('Location: ' . $signingUrl);
```

`recipientOrder` identifies the recipient's one-based position in the package
workflow. It is distinct from `signingOrder`, which controls the processing
stage. Signature-field coordinates and dimensions are pixels; `x` is measured
from the left and `y` from the top of the one-based page.

## Available operations

Every client method returns a call object. Invoke `send()` to execute it.

| Method | Result | Purpose |
| --- | --- | --- |
| `createPackage()` | `AddPackageResponse` | Create a draft package. |
| `uploadDocument()` | `UploadDocumentResponse` | Upload raw document bytes to a package. |
| `addWorkflowRecipient()` | `WorkflowRecipientResponse` | Add a recipient to the draft workflow. |
| `addSignatureField()` | `AddSignatureFieldResponse` | Add a visible signature field to a document. |
| `startWorkflow()` | `StartWorkflowResponse` | Share the package and start the workflow. |
| `generateIntegrationUrl()` | `string` | Generate a recipient-specific viewer URL. |

The response objects expose the API fields as public readonly properties. The
most commonly needed properties are `package_id`, `document_id`, `field_name`,
and `documents`.

### Workflow options

- `WorkflowMode`: `ME_AND_OTHERS`, `ONLY_OTHERS`, or `ONLY_ME`
- `CollaboratorRole`: `SIGNER`, `REVIEWER`, `EDITOR`, `CARBON_COPY`, or
  `INPERSON_HOST`
- `SignatureLevelOfAssurance`: `ELECTRONIC_SIGNATURE`,
  `ADVANCED_ELECTRONIC_SIGNATURE`, `HIGH_TRUST_ADVANCED`, or
  `QUALIFIED_ELECTRONIC_SIGNATURE`
- `IntegrationResponseType`: `ENCODED` or `PLAIN`

The available signature levels and collaborator roles depend on your service
plan and Digitaal Ondertekenen configuration.

## Error handling

Non-successful API responses throw `ApiException`. It exposes both the HTTP
status and the original response body:

```php
use Koba\DigitaalOndertekenen\Exception\ApiException;
use Koba\DigitaalOndertekenen\Api\WorkflowMode;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Client\ClientExceptionInterface;

try {
    $package = $client->createPackage(WorkflowMode::ONLY_OTHERS)->send();
} catch (ApiException $exception) {
    error_log(sprintf(
        'API error %d: %s',
        $exception->getStatusCode(),
        $exception->getResponseBody(),
    ));
} catch (IdentityProviderException $exception) {
    error_log('Authentication failed: ' . $exception->getMessage());
} catch (ClientExceptionInterface $exception) {
    error_log('HTTP transport failed: ' . $exception->getMessage());
}
```

Argument validation errors, such as an invalid email address, page number, or
field dimensions, throw `InvalidArgumentException` before the request is sent.

## Token caching

By default, each client keeps the Digitaal Ondertekenen access token in memory
for the lifetime of that client instance. Long-running or multi-process
applications can pass an implementation of `TokenCacheInterface` as the
`tokenCache` argument to `ClientFactory::create()`. A cache implementation must
store and return `CachedToken` values and implement `get()`, `save()`, and
`delete()`.

## Custom API HTTP implementations

The factory normally discovers installed PSR implementations automatically.
The PSR client and factories used for Digitaal Ondertekenen API calls can also
be supplied explicitly, which is useful for framework integration or testing:

```php
$factory = new ClientFactory(
    httpClient: $psr18Client,
    requestFactory: $psr17RequestFactory,
    streamFactory: $psr17StreamFactory,
);
```

## License

This library is released under the [MIT License](LICENSE).
