<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo 'assigned_leads total: ' . DB::table('assigned_leads')->count() . PHP_EOL;
echo 'lead_model breakdown:' . PHP_EOL;

$rows = DB::table('assigned_leads')
    ->select('lead_model', DB::raw('COUNT(*) c'))
    ->groupBy('lead_model')
    ->orderByDesc('c')
    ->get();

foreach ($rows as $r) {
    echo $r->lead_model . ':' . $r->c . PHP_EOL;
}

echo PHP_EOL . "Sample assigned_leads rows:" . PHP_EOL;
$samples = DB::table('assigned_leads')->orderByDesc('id')->limit(10)->get();
foreach ($samples as $s) {
    echo "id={$s->id} lead_model={$s->lead_model} lead_id={$s->lead_id} staff_ids={$s->staff_ids} created_at={$s->created_at}" . PHP_EOL;
}

echo PHP_EOL . "Membership check for staff_id=165:" . PHP_EOL;
$staffId = 165;
$match = DB::table('assigned_leads')
    ->where(function ($q) use ($staffId) {
        $q->whereRaw('JSON_CONTAINS(staff_ids, ?, \"$\" )', [(string) $staffId])
          ->orWhereRaw('JSON_CONTAINS(staff_ids, JSON_QUOTE(?), \"$\" )', [(string) $staffId]);
    })
    ->count();
echo "assigned_leads rows containing {$staffId}: {$match}" . PHP_EOL;
