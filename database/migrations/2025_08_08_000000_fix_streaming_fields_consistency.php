<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to ensure all streaming-related fields are consistent.
     */
    public function up(): void
    {
        Schema::table('cctvs', function (Blueprint $table) {
            // Ensure location field exists (mentioned in CctvController but not in original migration)
            if (!Schema::hasColumn('cctvs', 'location')) {
                $table->string('location')->nullable()->after('name');
            }

            

            // Ensure slug field exists (used in routes but not in original migration)
            if (!Schema::hasColumn('cctvs', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }

            // Ensure last_seen field exists (used in model but not in original migration)
            if (!Schema::hasColumn('cctvs', 'last_seen')) {
                $table->timestamp('last_seen')->nullable()->after('is_streaming');
            }

            // Ensure playlist_url field exists and is consistent
            if (!Schema::hasColumn('cctvs', 'playlist_url')) {
                $table->text('playlist_url')->nullable()->after('stream_url');
            } else {
                // If it exists but is string, change to text
                $table->text('playlist_url')->nullable()->change();
            }

            // Ensure stream_url is text type for longer URLs
            if (Schema::hasColumn('cctvs', 'stream_url')) {
                $table->text('stream_url')->nullable()->change();
            }

            // Add index for better performance on common queries
            $table->index(['status', 'is_streaming']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cctvs', function (Blueprint $table) {
            // Remove indexes
            $table->dropIndex(['status', 'is_streaming']);
            $table->dropIndex(['slug']);

            // Note: We don't drop columns in down() to avoid data loss
            // The original down() methods from other migrations will handle column removal
        });
    }
};
