<?php

namespace App\Services;

use App\Models\Order;

class OrderTypeAProcessor
{
    public function process(Order $order, int $userId, callable $fileOpener): void
    {
        $csvFile = 'orders_type_A_' . $userId . '_' . time() . '.csv';
        $fileHandle = $fileOpener($csvFile, 'w');

        if ($fileHandle !== false) {
            fputcsv($fileHandle, ['ID', 'Type', 'Amount', 'Flag', 'Status', 'Priority']);
            fputcsv($fileHandle, [
                $order->id,
                $order->type,
                $order->amount,
                $order->flag ? 'true' : 'false',
                $order->status,
                $order->priority
            ]);

            if ($order->amount > 150) {
                fputcsv($fileHandle, ['', '', '', '', 'Note', 'High value order']);
            }

            fclose($fileHandle);
            $order->status = 'exported';
        } else {
            $order->status = 'export_failed';
        }
    }
}
