<?php
namespace App\Services;

use App\Contracts\DatabaseServiceInterface;
use App\Contracts\APIClient;
use App\Models\Order;
use App\Exceptions\APIException;
use App\Exceptions\DatabaseException;
use Exception;

/**
 * Service for processing orders.
 */
class OrderProcessingService
{
    /**
     * @var DatabaseServiceInterface The data service for interacting with the database.
     */
    private DatabaseServiceInterface $dataService;

    /**
     * @var APIClient The API client for external API interactions.
     */
    private APIClient $apiClient;

    /**
     * Constructor.
     *
     * @param DatabaseServiceInterface $dataService The data service instance.
     * @param APIClient $apiClient The API client instance.
     */
    public function __construct(DatabaseServiceInterface $dataService, APIClient $apiClient)
    {
        $this->dataService = $dataService;
        $this->apiClient = $apiClient;
    }

    /**
     * Processes all orders for a given user.
     *
     * @param int $userId The ID of the user.
     * @return Order[]|false The processed orders or false on failure.
     */
    public function processOrders(int $userId)
    {
        try {
            $orders = $this->dataService->getOrdersByUser($userId);

            foreach ($orders as $order) {
                $this->processOrder($order, $userId);

                if ($order->status === Order::STATUS_EXPORT_FAILED) {
                    return false;
                }

                if (!$this->updateOrderInDatabaseSafely($order)) {
                    return false;
                }
            }

            return $orders;
        } catch (DatabaseException $e) {
            // Log database-specific error
            error_log('Database Error: ' . $e->getMessage());
            return false;
        } catch (Exception $e) {
            // Log generic error
            error_log('Unexpected Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Processes a single order based on its type.
     *
     * @param Order $order The order to process.
     * @param int $userId The ID of the user.
     */
    private function processOrder(Order $order, int $userId): void
    {
        switch ($order->type) {
            case 'A':
                $this->processTypeAOrder($order, $userId, fn($filename, $mode) => fopen($filename, $mode));
                break;
            case 'B':
                $this->processTypeBOrder($order);
                break;
            case 'C':
                $this->processTypeCOrder($order);
                break;
            default:
                $order->status = Order::STATUS_UNKNOWN_TYPE;
        }

        if ($order->status !== Order::STATUS_EXPORT_FAILED) {
            $order->priority = $order->amount > 200 ? 'high' : 'low';
        }
    }

    /**
     * Processes a Type A order and exports it to a CSV file.
     *
     * @param Order $order The order to process.
     * @param int $userId The ID of the user.
     * @param callable $fileOpener A callable for opening files.
     */
    public function processTypeAOrder(Order $order, int $userId, callable $fileOpener): void
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
            $order->status = Order::STATUS_EXPORTED;
        } else {
            $order->status = Order::STATUS_EXPORT_FAILED;
        }
    }

    /**
     * Processes a Type B order by interacting with an external API.
     *
     * @param Order $order The order to process.
     */
    private function processTypeBOrder(Order $order): void
    {
        try {
            $apiResponse = $this->apiClient->callAPI($order->id);

            if ($apiResponse->status === 'success') {
                if ($apiResponse->data->amount >= 50 && $order->amount < 100) {
                    $order->status = Order::STATUS_PROCESSED;
                } elseif ($apiResponse->data->amount < 50 || $order->flag) {
                    $order->status = Order::STATUS_PENDING;
                } else {
                    $order->status = Order::STATUS_ERROR;
                }
            } else {
                $order->status = Order::STATUS_API_ERROR;
            }
        } catch (APIException $e) {
            $order->status = Order::STATUS_API_FAILURE;
        }
    }

    /**
     * Processes a Type C order based on its flag.
     *
     * @param Order $order The order to process.
     */
    private function processTypeCOrder(Order $order): void
    {
        $order->status = $order->flag ? Order::STATUS_COMPLETED : Order::STATUS_IN_PROGRESS;
    }

    /**
     * Safely updates the order in the database.
     *
     * @param Order $order The order to update.
     * @return bool True if the update was successful, false otherwise.
     */
    private function updateOrderInDatabaseSafely(Order $order): bool
    {
        try {
            if ($order->status !== Order::STATUS_EXPORT_FAILED) {
                $this->dataService->updateOrderStatus($order->id, $order->status, $order->priority);
            }
            return true;
        } catch (DatabaseException $e) {
            $order->status = Order::STATUS_DB_ERROR;
            return false;
        }
    }
}