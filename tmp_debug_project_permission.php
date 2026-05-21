<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = 'shindeshubham7792@gmail.com';
$user = App\Models\User::where('email', $email)->first();

if (! $user) {
    echo "USER_NOT_FOUND\n";
    exit(0);
}

echo "USER_ID={$user->id}\n";
echo "RAW_ROLE=" . ($user->getRawOriginal('role') ?? 'NULL') . "\n";
echo "ACCESSOR_ROLE=" . ($user->role ?? 'NULL') . "\n";
echo "ROLES=" . $user->roles()->pluck('name')->implode(',') . "\n";
echo "PERMS_VIA_ROLES=" . $user->getPermissionsViaRoles()->pluck('name')->sort()->implode(',') . "\n";
echo "HAS_view_projects_default=" . ($user->hasPermissionTo('view_projects') ? 'yes' : 'no') . "\n";
echo "HAS_view_projects_web=" . ($user->hasPermissionTo('view_projects', 'web') ? 'yes' : 'no') . "\n";
echo "CAN_view_projects=" . ($user->can('view_projects') ? 'yes' : 'no') . "\n";
