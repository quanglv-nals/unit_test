<?php

namespace App\Services;

use App\Models\Order;

class OrderProcessor
{
    private OrderTypeAProcessor $typeAProcessor;
    private OrderTypeBProcessor $typeBProcessor;
    private OrderTypeCProcessor $typeCProcessor;

    public function __construct(
        OrderTypeAProcessor $typeAProcessor,
        OrderTypeBProcessor $typeBProcessor,
        OrderTypeCProcessor $typeCProcessor
    ) {
        $this->typeAProcessor = $typeAProcessor;
        $this->typeBProcessor = $typeBProcessor;
        $this->typeCProcessor = $typeCProcessor;
    }

    public function process(Order $order, int $userId, callable $fileOpener): void
    {
        switch ($order->type) {
            case 'A':
                $this->typeAProcessor->process($order, $userId, $fileOpener);
                break;
            case 'B':
                $this->typeBProcessor->process($order);
                break;
            case 'C':
                $this->typeCProcessor->process($order);
                break;
            default:
                $order->status = 'unknown_type';
        }
    }
}
