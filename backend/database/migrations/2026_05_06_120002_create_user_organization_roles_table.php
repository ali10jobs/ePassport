<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A user can belong to multiple organizations with different roles in each.
 * Example: HSE Manager at Company B and Auditor at Company C.
 *
 * Spatie laravel-permission's teams feature uses the organization_id column to
 * scope role/permission assignments per organization.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_organization_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('role'); // platform_admin | hse_manager | safety_engineer | supervisor | consultant | client_safety_lead | auditor | worker
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'organization_id', 'role']);
            $table->index('organization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_organization_roles');
    }
};
