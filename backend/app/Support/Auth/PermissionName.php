<?php

namespace App\Support\Auth;

enum PermissionName: string
{
    case DASHBOARD_VIEW = 'dashboard.view';

    case USERS_VIEW = 'users.view';
    case USERS_CREATE = 'users.create';
    case USERS_UPDATE = 'users.update';
    case USERS_DELETE = 'users.delete';
    case USERS_MANAGE = 'users.manage';

    case CONTENT_VIEW = 'content.view';
    case CONTENT_CREATE = 'content.create';
    case CONTENT_UPDATE = 'content.update';
    case CONTENT_DELETE = 'content.delete';
    case CONTENT_MANAGE = 'content.manage';

    case ROLES_VIEW = 'roles.view';
    case ROLES_CREATE = 'roles.create';
    case ROLES_UPDATE = 'roles.update';
    case ROLES_DELETE = 'roles.delete';
    case ROLES_MANAGE = 'roles.manage';

    case PERMISSIONS_VIEW = 'permissions.view';
    case PERMISSIONS_MANAGE = 'permissions.manage';
}

