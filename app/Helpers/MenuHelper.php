<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class MenuHelper
{
    /**
     * Get menu items based on user permissions
     */
    public static function getMenuItems()
    {
        $user = Auth::user();
        
        if (!$user) {
            return [];
        }

        $permissions = $user->getAllPermissions()->pluck('name')->toArray();
        
        return self::buildMenu($permissions);
    }

    /**
     * Build menu structure based on permissions
     */
    private static function buildMenu(array $permissions)
    {
        $menu = [];

        // Dashboard - always visible
        $menu[] = [
            'name' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => 'bx bx-home-alt',
            'active' => request()->routeIs('dashboard'),
        ];

        // Access Control section
        if (self::hasAnyPermission($permissions, ['view_staff', 'view_roles'])) {
            $accessControlChildren = [];
            
            if (in_array('view_staff', $permissions)) {
                $accessControlChildren[] = [
                    'name' => 'Staff',
                    'route' => 'staff',
                    'active' => request()->routeIs('staff*') || request()->routeIs('add-staff') || request()->routeIs('view-staff*'),
                ];
            }
            
            if (in_array('view_roles', $permissions)) {
                $accessControlChildren[] = [
                    'name' => 'Roles',
                    'route' => 'roles',
                    'active' => request()->routeIs('roles*') || request()->routeIs('add-role') || request()->routeIs('role.*'),
                ];
            }

            if (!empty($accessControlChildren)) {
                $menu[] = [
                    'name' => 'Access Control',
                    'icon' => 'bx bx-user-circle',
                    'children' => $accessControlChildren,
                    'active' => self::isAnyChildActive($accessControlChildren),
                ];
            }
        }

        // Renewal Master section
        if (in_array('view_renewals', $permissions)) {
            $menu[] = [
                'name' => 'Renewal Master',
                'icon' => 'bx bx-category',
                'children' => [
                    [
                        'name' => 'Client Renewal',
                        'route' => 'servies',
                        'active' => request()->routeIs('servies*'),
                    ],
                    [
                        'name' => 'Vendor Renewal',
                        'route' => 'vendor-services.index',
                        'active' => request()->routeIs('vendor-services*'),
                    ],
                    [
                        'name' => 'Client',
                        'route' => 'client',
                        'active' => request()->routeIs('client') || request()->routeIs('client.*'),
                    ],
                    [
                        'name' => 'Vendor',
                        'route' => 'vendor1',
                        'active' => request()->routeIs('vendor1*'),
                    ],
                ],
                'active' => request()->routeIs('servies*') || request()->routeIs('vendor-services*') || request()->routeIs('client*') || request()->routeIs('vendor1*'),
            ];
        }

        // Leads
        if (in_array('view_leads', $permissions)) {
            $menu[] = [
                'name' => 'Leads',
                'route' => 'leads',
                'icon' => 'bx bx-user-voice',
                'active' => request()->routeIs('leads*') || request()->routeIs('add-lead') || request()->routeIs('lead.*'),
            ];
        }

        // Projects
        if (in_array('view_projects', $permissions)) {
            $menu[] = [
                'name' => 'Projects',
                'route' => 'project',
                'icon' => 'bx bx-bar-chart',
                'active' => request()->routeIs('project*') || request()->routeIs('add-project') || request()->routeIs('edit-project*'),
            ];
        }

        // Tasks
        if (in_array('view_tasks', $permissions)) {
            $menu[] = [
                'name' => 'Tasks',
                'route' => 'task',
                'icon' => 'bx bx-task',
                'active' => request()->routeIs('task*') || request()->routeIs('add-task'),
            ];
        }

        // Raise Issue - always visible
        $menu[] = [
            'name' => 'Raise Issue',
            'route' => 'client-issue',
            'icon' => 'bx bx-error',
            'active' => request()->routeIs('client-issue*'),
        ];

        // Clients
        if (in_array('view_clients', $permissions)) {
            $menu[] = [
                'name' => 'Client',
                'route' => 'clients',
                'icon' => 'bx bx-user-check',
                'active' => request()->routeIs('clients*') || request()->routeIs('add-clients'),
            ];
        }

        // Settings
        if (self::hasAnyPermission($permissions, ['view_general_settings', 'view_company_information', 'view_email_settings'])) {
            $menu[] = [
                'name' => 'Settings',
                'route' => 'settings',
                'icon' => 'bx bx-cog',
                'active' => request()->routeIs('settings*'),
            ];
        }

        return $menu;
    }

    /**
     * Check if user has any of the given permissions
     */
    private static function hasAnyPermission(array $userPermissions, array $requiredPermissions)
    {
        return !empty(array_intersect($userPermissions, $requiredPermissions));
    }

    /**
     * Check if any child menu item is active
     */
    private static function isAnyChildActive(array $children)
    {
        foreach ($children as $child) {
            if ($child['active'] ?? false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user can access a specific feature
     */
    public static function canAccess($permission)
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }

        return $user->hasPermissionTo($permission);
    }

    /**
     * Check if user has a specific role
     */
    public static function hasRole($role)
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }

        return $user->hasRole($role);
    }

    /**
     * Get user's roles
     */
    public static function getUserRoles()
    {
        $user = Auth::user();
        
        if (!$user) {
            return [];
        }

        return $user->getRoleNames()->toArray();
    }

    /**
     * Get user's permissions
     */
    public static function getUserPermissions()
    {
        $user = Auth::user();
        
        if (!$user) {
            return [];
        }

        return $user->getAllPermissions()->pluck('name')->toArray();
    }
}
