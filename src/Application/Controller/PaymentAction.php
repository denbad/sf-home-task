<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Event\PaymentFailed;
use Application\Handler\ConductPayment;
use Application\Handler\ConductPaymentHandler;
use Application\Handler\LoanMissing;
use Application\Handler\LoanStateForbidden;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class PaymentAction
{
    public function __construct(
        private ConductPaymentHandler $paymentHandler,
        private PaymentValidator $paymentValidator,
        private MessageBusInterface $eventBus,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        [$data, $errors] = $this->handleRequest($request);

        if (count($errors) > 0) {
            return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $command = new ConductPayment(
            firstName: $data['firstname'],
            lastName: $data['lastname'],
            conductedOn: $data['paymentDate'],
            amount: $data['amount'],
            loanNumber: $data['description'],
            reference: $data['refId'],
        );

        try {
            ($this->paymentHandler)($command);
            [$status, $error] = [Response::HTTP_OK, null];
        } catch (LoanMissing $e) {
            [$status, $error] = [Response::HTTP_BAD_REQUEST, $e->getMessage()];
        } catch (LoanStateForbidden $e) {
            [$status, $error] = [Response::HTTP_CONFLICT, $e->getMessage()];
        } catch (\Throwable $e) {
            [$status, $error] = [Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage()];
        }

        if ($error === null) {
            return new JsonResponse('ok');
        }

        $event = new PaymentFailed(url: $request->getUri(), data: $data, errors: $errors);
        $this->eventBus->dispatch($event);

        return new JsonResponse(['error' => $error], $status);
    }

    /**
     * @param Request $request
     *
     * @return array{array<string, string>, array<string, string>}
     */
    private function handleRequest(Request $request): array
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            $data = $request->request->all();
        } else {
            $data = $request->query->all();
        }

        $errors = $this->paymentValidator->validate($data);

        return [$data, $errors];
    }
}
