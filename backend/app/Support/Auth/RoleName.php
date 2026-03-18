<?php

namespace App\Support\Auth;

enum RoleName: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case EDITOR = 'editor';
    case VIEWER = 'viewer';
}

