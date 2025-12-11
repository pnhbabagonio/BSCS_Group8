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
        Schema::table('support_tickets', function (Blueprint $table) {
            // Make user_id nullable to support public contacts
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // Add fields for public contact information
            $table->string('contact_name')->nullable()->after('user_id');
            $table->string('contact_email')->nullable()->after('contact_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->dropColumn(['contact_name', 'contact_email']);
        });
    }
};
