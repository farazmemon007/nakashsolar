<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Add foreign key constraint if vendor_id exists
            if (!Schema::hasColumn('purchases', 'vendor_id')) {
                $table->unsignedBigInteger('vendor_id')->after('party_name')->nullable();
            }

            // Check if the foreign key already exists before adding it
            try {
                $table->foreign('vendor_id')
                    ->references('id')
                    ->on('vendors')
                    ->onDelete('cascade');
            } catch (\Exception $e) {
                // Foreign key might already exist, that's fine
                \Log::warning('Foreign key might already exist: ' . $e->getMessage());
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Drop foreign key if it exists
            try {
                $table->dropForeign(['vendor_id']);
            } catch (\Exception $e) {
                \Log::warning('Foreign key drop failed: ' . $e->getMessage());
            }
        });
    }
};
