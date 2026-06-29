<?php

namespace App\Enums;

enum RoleEnum : string
{
    case Superadmin    = 'superadmin';
    case Director      = 'director';
    case Admin         = 'admin';
    case TeamLeader    = 'team_leader';
    case LeadQualifier = 'lead_qualifier';
    case Advisor       = 'advisor';
    case Engineering   = 'engineering';
    case Finance       = 'finance';
}
