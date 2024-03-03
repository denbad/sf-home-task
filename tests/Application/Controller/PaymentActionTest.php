<?php

declare(strict_types=1);

namespace Tests\Application\Controller;

use Application\Controller\PaymentAction;
use Application\Controller\PaymentValidator;
use Application\Handler\ConductPaymentHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Tests\Application\EventBusSpy;
use Tests\Application\Handler\ConductPaymentHandlerFake;
use function PHPUnit\Framework\assertThat;
use function PHPUnit\Framework\assertTrue;
use function PHPUnit\Framework\equalTo;

final class PaymentActionTest extends TestCase
{
    /** @var array<int, string> */
    public array $spy;

    private ConductPaymentHandler $handler;
    private MessageBusInterface $eventBus;
    private PaymentAction $action;

    protected function setUp(): void
    {
        $this->spy = [];
        $this->handler = $this->createHandler();
        $this->eventBus = $this->createEventBus();
        $this->action = $this->createAction($this->handler, $this->eventBus);
    }

    public function testItReturnsBadRequestResponseWithValidationErrors(): void
    {
        $request = $this->givenInvalidRequest();
        $response = ($this->action)($request);

        $this->assertStatusCode($response, Response::HTTP_BAD_REQUEST);
        $this->assertResponseKeyExists($response, 'errors');
    }

    public function testItReturnsBadRequestResponseGivenLoanIsMissing(): void
    {
        $request = $this->givenValidRequest(description: 'LN00000001');
        $response = ($this->action)($request);

        $this->assertStatusCode($response, Response::HTTP_BAD_REQUEST);
        $this->assertResponseKeyExists($response, 'error');
        $this->assertFailedEventDispatched();
    }

    public function testItReturnsConflictResponseGivenLoanAlreadyPaidOff(): void
    {
        $request = $this->givenValidRequest(description: 'LN00000002');
        $response = ($this->action)($request);

        $this->assertStatusCode($response, Response::HTTP_CONFLICT);
        $this->assertResponseKeyExists($response, 'error');
        $this->assertFailedEventDispatched();
    }

    public function testItReturnsConflictResponseGivenLoanAlreadyConducted(): void
    {
        $request = $this->givenValidRequest(description: 'LN00000003');
        $response = ($this->action)($request);

        $this->assertStatusCode($response, Response::HTTP_CONFLICT);
        $this->assertResponseKeyExists($response, 'error');
        $this->assertFailedEventDispatched();
    }

    public function testItReturnsServerResponseGivenGeneralServerError(): void
    {
        $request = $this->givenValidRequest(description: 'LN00000004');
        $response = ($this->action)($request);

        $this->assertStatusCode($response, Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertResponseKeyExists($response, 'error');
        $this->assertFailedEventDispatched();
    }

    public function testItReturnsOkGivenNoErrors(): void
    {
        $request = $this->givenValidRequest();
        $response = ($this->action)($request);

        $this->assertStatusCode($response, Response::HTTP_OK);
    }

    private function assertStatusCode(Response $response, int $expectedStatusCode): void
    {
        $actualStatusCode = $response->getStatusCode();
        assertThat($actualStatusCode, equalTo($expectedStatusCode));
    }

    private function assertResponseKeyExists(Response $response, string $contextKey): void
    {
        $content = json_decode($response->getContent(), associative: true);
        assertTrue(isset($content[$contextKey]));
    }

    private function assertFailedEventDispatched(): void
    {
        assertThat($this->spy, equalTo(['paymentfailed']));
    }

    private function givenInvalidRequest(): Request
    {
        return new Request();
    }

    private function givenValidRequest(string $description = 'LN00000000'): Request
    {
        return new Request(query: [
            'firstname' => 'James',
            'lastname' => 'Bond',
            'paymentDate' => '2022-12-12T15:19:21+00:00',
            'amount' => '99.99',
            'description' => $description,
            'refId' => '130f8a89-51c9-47d0-a6ef-1aea54924d3b',
        ]);
    }

    private function createHandler(): ConductPaymentHandler
    {
        return new ConductPaymentHandlerFake();
    }

    private function createEventBus(): MessageBusInterface
    {
        return new EventBusSpy($this->spy);
    }

    private function createAction(ConductPaymentHandler $handler, MessageBusInterface $eventBus): PaymentAction
    {
        return new PaymentAction($handler, paymentValidator: new PaymentValidator(), eventBus: $eventBus);
    }
}
