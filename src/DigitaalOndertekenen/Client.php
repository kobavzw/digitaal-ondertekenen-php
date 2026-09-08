<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen;

use Koba\DigitaalOndertekenen\Api\CollaboratorRole;
use Koba\DigitaalOndertekenen\Api\IntegrationResponseType;
use Koba\DigitaalOndertekenen\Api\SignatureLevelOfAssurance;
use Koba\DigitaalOndertekenen\Api\WorkflowMode;
use Koba\DigitaalOndertekenen\Call\AddSignatureFieldCall;
use Koba\DigitaalOndertekenen\Call\AddWorkflowRecipientCall;
use Koba\DigitaalOndertekenen\Call\CreatePackageCall;
use Koba\DigitaalOndertekenen\Call\GenerateIntegrationUrlCall;
use Koba\DigitaalOndertekenen\Call\StartWorkflowCall;
use Koba\DigitaalOndertekenen\Call\UploadDocumentCall;
use Koba\DigitaalOndertekenen\Http\ApiTransport;

final class Client
{
    public function __construct(private ApiTransport $transport)
    {
    }

    /**
     * Creates a call that creates an empty document package, which can
     * subsequently receive documents and workflow recipients.
     *
     * @param WorkflowMode $workflowMode Determines whether the package owner,
     *     other recipients, or both participate in the workflow.
     * @param string|null $packageName Name shown for the package. SigningHub
     *     uses its default package name when this is omitted.
     * @param string|null $folderName Existing custom or shared folder in which
     *     to create the package. SigningHub uses its default folder when this
     *     is omitted.
     */
    public function createPackage(
        WorkflowMode $workflowMode,
        ?string $packageName = null,
        ?string $folderName = null,
    ): CreatePackageCall {
        return new CreatePackageCall($this->transport, $workflowMode, $packageName, $folderName);
    }

    /**
     * Creates a call that uploads a document as raw bytes into an existing
     * document package.
     *
     * SigningHub expects the document bytes as the request body. The file
     * name and conversion settings are sent as request headers.
     *
     * @param int $packageId ID returned by createPackage().
     * @param string $fileName File name including its extension.
     * @param string $contents Raw document bytes, not a path or Base64 value.
     * @param bool|null $convertDocument Whether SigningHub should convert the
     *     document to PDF. False is only supported for retaining Word and XML
     *     documents; null omits the setting.
     * @param string|null $source Informational label identifying where the
     *     document originated, for example the name of the calling application.
     */
    public function uploadDocument(
        int $packageId,
        string $fileName,
        string $contents,
        ?bool $convertDocument = true,
        ?string $source = null,
    ): UploadDocumentCall {
        return new UploadDocumentCall(
            $this->transport,
            $packageId,
            $fileName,
            $contents,
            $convertDocument,
            $source,
        );
    }

    /**
     * Adds an email recipient to a draft package's workflow.
     *
     * Adding a recipient does not start the workflow; call startWorkflow()
     * after all documents, recipients, and fields have been configured.
     *
     * @param int $packageId ID of the draft package.
     * @param string $email Email address of the recipient.
     * @param string $name Display name of the recipient.
     * @param CollaboratorRole $role Action the recipient performs in the workflow.
     * @param bool $emailNotification Whether SigningHub may send workflow email
     *     notifications to the recipient, subject to the configured notification
     *     settings.
     * @param int|null $signingOrder One-based processing stage. This is required
     *     for custom workflows; recipients with the same value process in parallel.
     */
    public function addWorkflowRecipient(
        int $packageId,
        string $email,
        string $name,
        CollaboratorRole $role = CollaboratorRole::SIGNER,
        bool $emailNotification = true,
        ?int $signingOrder = null,
    ): AddWorkflowRecipientCall {
        return new AddWorkflowRecipientCall(
            $this->transport,
            $packageId,
            $email,
            $name,
            $role,
            $emailNotification,
            $signingOrder,
        );
    }

    /**
     * Shares a prepared package and starts its signing workflow.
     *
     * @param int $packageId ID of the prepared draft package.
     */
    public function startWorkflow(int $packageId): StartWorkflowCall
    {
        return new StartWorkflowCall($this->transport, $packageId);
    }

    /**
     * Generates an encrypted URL through which a workflow recipient can
     * access and sign a shared document.
     *
     * The recipient email is required by the API for guest users.
     * Nullable integration options are omitted when null, allowing SigningHub
     * to apply its configured integration settings.
     *
     * @param int $packageId ID of the shared package.
     * @param int $documentId ID of the document to open in the viewer.
     * @param string $language Locale in which to display the viewer, for example
     *     "nl-NL" or "en-US".
     * @param string|null $userEmail Email address of the recipient processing
     *     the document. This is required for guest users.
     * @param string|null $callbackUrl URL to which control returns after the
     *     recipient leaves the viewer. The configured integration callback is
     *     used when this is omitted.
     * @param IntegrationResponseType|null $responseType Whether the status
     *     parameters appended to the callback URL are encoded or plain.
     * @param bool|null $collapsePanels Whether to initially hide the document
     *     and recipient panels in the embedded viewer.
     * @param bool|null $lockPanels Whether to prevent the viewer panels from
     *     being opened or changed.
     * @param bool|null $redirectCallbackUrl Whether to redirect automatically
     *     after the recipient completes the required action. When false, the
     *     recipient remains in the viewer until they close it.
     */
    public function generateIntegrationUrl(
        int $packageId,
        int $documentId,
        string $language = 'nl-NL',
        ?string $userEmail = null,
        ?string $callbackUrl = null,
        ?IntegrationResponseType $responseType = null,
        ?bool $collapsePanels = null,
        ?bool $lockPanels = null,
        ?bool $redirectCallbackUrl = null,
    ): GenerateIntegrationUrlCall {
        return new GenerateIntegrationUrlCall(
            $this->transport,
            $packageId,
            $documentId,
            $language,
            $userEmail,
            $callbackUrl,
            $responseType,
            $collapsePanels,
            $lockPanels,
            $redirectCallbackUrl,
        );
    }

    /**
     * Adds a visible digital-signature field assigned to a workflow recipient.
     *
     * @param int $packageId ID of the draft package containing the document.
     * @param int $documentId ID of the document receiving the field.
     * @param int $recipientOrder One-based position of the recipient in the
     *     workflow, not the recipient's signing order.
     * @param int $pageNumber One-based page number on which to place the field.
     * @param array{x: float, y: float, width: float, height: float} $dimensions
     *     Field position and size in pixels; x is measured from the left and y
     *     from the top of the page.
     * @param SignatureLevelOfAssurance $levelOfAssurance Required signature
     *     class. Its availability depends on the service plan, user role, and
     *     configured signing providers.
     * @param string|null $fieldName Unique field identifier within the document.
     *     SigningHub generates one when this is omitted or empty.
     */
    public function addSignatureField(
        int $packageId,
        int $documentId,
        int $recipientOrder,
        int $pageNumber,
        array $dimensions,
        SignatureLevelOfAssurance $levelOfAssurance,
        ?string $fieldName = null,
    ): AddSignatureFieldCall {
        return new AddSignatureFieldCall(
            $this->transport,
            $packageId,
            $documentId,
            $recipientOrder,
            $pageNumber,
            $dimensions,
            $levelOfAssurance,
            $fieldName,
        );
    }
}
