<?php

namespace App\Enums;

enum TransactionStatus: string {
    case NOT_PROCESSED = 'not_processed';
    case PROCESSED = 'processed")';
}
