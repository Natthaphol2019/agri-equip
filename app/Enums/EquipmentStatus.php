<?php
namespace App\Enums;

enum EquipmentStatus: string
{
    case AVAILABLE = 'available';
    case BOOKED = 'booked';
    case IN_USE = 'in_use';
    case MAINTENANCE = 'maintenance';
    case BREAKDOWN = 'breakdown';
}