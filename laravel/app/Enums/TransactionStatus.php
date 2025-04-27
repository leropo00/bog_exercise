<?php

namespace App\Enums;

enum TransactionStatus: string {
    case ERROR_PROCESSING = 'error';
    case PROCESSED = 'processed';
}
