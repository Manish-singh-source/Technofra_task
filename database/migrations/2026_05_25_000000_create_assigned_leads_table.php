<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assigned_leads', function (Blueprint $table) {
            $table->id();
            $table->string('lead_model', 50);
            $table->unsignedBigInteger('lead_id');
            $table->json('staff_ids')->nullable();
            $table->timestamps();

            $table->unique(['lead_model', 'lead_id']);
        });

        $now = now();

        $leadAssignments = DB::table('leads')
            ->select('id', 'assigned')
            ->whereNotNull('assigned')
            ->get()
            ->map(function ($row) use ($now) {
                $staffIds = json_decode((string) $row->assigned, true);
                if (! is_array($staffIds)) {
                    return null;
                }

                $staffIds = array_values(array_unique(array_map('intval', array_filter($staffIds, fn ($id) => is_numeric($id)))));
                if ($staffIds === []) {
                    return null;
                }

                return [
                    'lead_model' => 'lead',
                    'lead_id' => (int) $row->id,
                    'staff_ids' => json_encode($staffIds),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->filter()
            ->values()
            ->all();

        if ($leadAssignments !== []) {
            DB::table('assigned_leads')->insert($leadAssignments);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('assigned_leads');
    }
};

