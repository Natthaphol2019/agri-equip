<?php
namespace App\Enums;

enum BookingStatus: string
{
    case SCHEDULED = 'scheduled';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED_PENDING = 'completed_pending_approval';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case PAUSED = 'paused';
    case CLOSED = 'closed';
}