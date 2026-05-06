<?php

namespace App\BDUI\Services;

class SchemaService
{
    /**
     * Root schema version for client compatibility checks.
     */
    private const SCHEMA_VERSION = 1;

    /**
     * Return schema payload for a named screen.
     */
    public function getScreen(string $screen): array
    {
        return match ($screen) {
            'dashboard' => $this->dashboard(),
            default => $this->unknownScreen($screen),
        };
    }

    /**
     * Dashboard screen schema.
     */
    private function dashboard(): array
    {
        return [
            'version' => self::SCHEMA_VERSION,
            'schema_version' => self::SCHEMA_VERSION,
            'screen' => 'dashboard',
            'components' => [
                [
                    'type' => 'app_bar',
                    'title' => 'Dashboard',
                    'actions' => [
                        ['icon' => 'notifications', 'action' => 'open_notifications'],
                        ['icon' => 'settings', 'action' => 'open_settings'],
                    ],
                ],
                [
                    'type' => 'stats_grid',
                    'items' => [
                        ['label' => 'Revenue', 'value' => '₹1,24,500', 'icon' => 'trending_up', 'color' => '#16A34A'],
                        ['label' => 'Orders', 'value' => '328', 'icon' => 'shopping_bag', 'color' => '#2563EB'],
                        ['label' => 'Visitors', 'value' => '12.4k', 'icon' => 'groups', 'color' => '#7C3AED'],
                        ['label' => 'Tickets', 'value' => '18', 'icon' => 'support_agent', 'color' => '#EA580C'],
                    ],
                ],
                [
                    'type' => 'card',
                    'title' => 'Performance',
                    'subtitle' => 'Your conversion rate improved by 8.2% this week.',
                    'image_url' => 'https://picsum.photos/seed/bdui-performance/800/400',
                ],
                [
                    'type' => 'list',
                    'items' => [
                        ['title' => 'Pending Approval', 'subtitle' => '4 requests waiting', 'trailing' => 'Review'],
                        ['title' => 'Low Inventory', 'subtitle' => '9 items below threshold', 'trailing' => 'Restock'],
                        ['title' => 'Team Updates', 'subtitle' => '3 new notes posted', 'trailing' => 'Open'],
                    ],
                ],
                [
                    'type' => 'button',
                    'label' => 'Create New Order',
                    'action' => 'create_order',
                    'style' => 'primary',
                ],
            ],
            // Future optimization:
            // - Enable route caching: php artisan route:cache
            // - Add response caching for stable schemas per screen/version.
        ];
    }

    /**
     * Fallback payload for unknown screens.
     */
    private function unknownScreen(string $screen): array
    {
        return [
            'version' => self::SCHEMA_VERSION,
            'schema_version' => self::SCHEMA_VERSION,
            'screen' => $screen,
            'components' => [],
        ];
    }
}

