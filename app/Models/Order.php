<?php
namespace App\Models;

class Order
{
    public const STATUS_EXPORTED = 'exported';
    public const STATUS_EXPORT_FAILED = 'export_failed';
    public const STATUS_PROCESSED = 'processed';
    public const STATUS_PENDING = 'pending';
    public const STATUS_ERROR = 'error';
    public const STATUS_API_ERROR = 'api_error';
    public const STATUS_API_FAILURE = 'api_failure';
    public const STATUS_DB_ERROR = 'db_error';
    public const STATUS_UNKNOWN_TYPE = 'unknown_type';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_IN_PROGRESS = 'in_progress';

    public int $id;
    public string $type;
    public float $amount;
    public bool $flag;
    public string $status;
    public string $priority;

    public function __construct(int $id, string $type, float $amount, bool $flag)
    {
        $this->id = $id;
        $this->type = $type;
        $this->amount = $amount;
        $this->flag = $flag;
        $this->status = '';
        $this->priority = '';
    }
}
