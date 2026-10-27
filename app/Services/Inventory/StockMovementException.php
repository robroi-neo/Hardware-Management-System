<?php

namespace App\Services\Inventory;

use RuntimeException;

/**
 * Thrown inside a stock transaction to roll it back with a user-facing message.
 */
class StockMovementException extends RuntimeException
{
}
