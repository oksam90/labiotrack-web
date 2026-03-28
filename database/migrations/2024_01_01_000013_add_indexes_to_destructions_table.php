<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destructions', function (Blueprint $table) {
            $table->index('collecte_id', 'destructions_collecte_id_index');
            $table->index('date_destruction', 'destructions_date_destruction_index');
        });
    }

    public function down(): void
    {
        Schema::table('destructions', function (Blueprint $table) {
            $table->dropIndex('destructions_collecte_id_index');
            $table->dropIndex('destructions_date_destruction_index');
        });
    }
};
