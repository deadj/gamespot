<?php

namespace App\Http\Controllers;

use App\Application\Payment\DTO\PaymentDTO;
use App\Application\Payment\UseCase\HandlePaymentWebhookUseCase;
use App\Domain\Order\Exception\OrderNotFoundException;
use App\Domain\Order\Exception\OrderPartiallyDeliveredException;
use App\Domain\Payment\Enum\PaymentStatus;
use App\Domain\Payment\Exception\PaymentAlreadyProcessedException;
use App\Domain\Payment\Exception\PaymentAmountException;
use App\Http\Requests\PaymentWebhookRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class PaymentWebhookController extends Controller
{
    public function __invoke(
        PaymentWebhookRequest $request,
        HandlePaymentWebhookUseCase $useCase,
    ): JsonResponse
    {
        try {
            $dto = new PaymentDTO(
                eventId: $request->event_id,
                orderPublicId: $request->order_id,
                status: PaymentStatus::from($request->status),
                amount: (float) $request->amount,
                currency: $request->currency,
                eventCreatedAt: Carbon::parse($request->created_at),
            );

            $useCase->execute($dto);

            return response()->json([], 200);
        } catch (PaymentAlreadyProcessedException $e) {
            return response()->json(['message' => $e->getMessage()], 200);
        } catch (OrderPartiallyDeliveredException $e) {
            return response()->json(['message' => $e->getMessage()], 200);
        } catch (OrderNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (PaymentAmountException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
