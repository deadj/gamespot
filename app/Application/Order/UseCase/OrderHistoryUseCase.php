<?php

namespace App\Application\Order\UseCase;

use App\Domain\Money\Enum\MoneyMoveType;
use App\Domain\Money\Repository\MoneyMoveRepositoryInterface;
use App\Domain\Order\DTO\OrderHistoryRequestDTO;
use App\Domain\Order\DTO\OrderHistoryResponseDTO;
use App\Domain\Order\Repository\OrderHistoryRepositoryInterface;
use App\Infrastructure\Models\Order;
use App\Infrastructure\Models\OrderItem;
use Illuminate\Database\Eloquent\Collection;

class OrderHistoryUseCase
{
    public function __construct(
        protected OrderHistoryRepositoryInterface $historyRepository,
        protected MoneyMoveRepositoryInterface $moneyMoveRepository,
    ) {}

    public function execute(OrderHistoryRequestDTO $dto): OrderHistoryResponseDTO
    {
        $moneyMoves = $this->moneyMoveRepository->getForHistory($dto);

        $history = $this->getHistory($dto, $moneyMoves);
        $orderState = $this->getOrderState($history);
        $orderItemStates = $this->getOrderItemsState($history);
        $financialReport = $this->getFinancialReport($moneyMoves);
        
        return new OrderHistoryResponseDTO(
            orderState: $orderState,
            orderItemsState: $orderItemStates,
            history: $history,
            financialReport: $financialReport,
        );
    }

    protected function getHistory(OrderHistoryRequestDTO $dto, Collection $moneyMoves): Collection
    {
        $history = $this->historyRepository->getHistory($dto);
        $historyWithMoneyMoves = $history->concat($moneyMoves)->sortBy('created_at');

        return $historyWithMoneyMoves;
    }

    protected function getOrderState(Collection $history): array
    {
        $lastOrderState = $history->where('target_type', Order::morphClass())->sortByDesc('id')->first();
        $orderState = [
            'public_id' => $lastOrderState->target->public_id,
            'status' => $lastOrderState->new_status,
            'last_update' => $lastOrderState->created_at,
        ];        

        return $orderState;
    }

    protected function getOrderItemsState(Collection $history): array
    {
        $lastOrderItemsStates = $history->where('target_type', OrderItem::morphClass())
            ->groupBy('target_id')
            ->map(fn ($group) => $group->last());

        $orderItemStates = [];
        foreach ($lastOrderItemsStates as $state) {
            $orderItemStates[] = [
                'public_id' => $state->target->public_id,
                'status' => $state->new_status,
                'last_update' => $state->created_at,
            ];
        }           
        
        return $orderItemStates;
    }

    protected function getFinancialReport(Collection $moneyMoves): array
    {
        $data = [
            'received' => $moneyMoves->where('type', MoneyMoveType::Received)->sum('amount'),
            'issued' => $moneyMoves->where('type', MoneyMoveType::Issued)->sum('amount'),
            'refund' => $moneyMoves->where('type', MoneyMoveType::Refund)->sum('amount'),
            'refund_cancelled' => $moneyMoves->where('type', MoneyMoveType::RefundCancelled)->sum('amount'),
        ];

        $data['balance'] = $data['received'] + $data['refund_cancelled'] - $data['issued'] - $data['refund'];

        return $data;
    } 
}