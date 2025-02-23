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
        Schema::create('price_aggregate', function (Blueprint $table) {
            $table->id();
            $table->string('pair');
            $table->decimal('price', 20, 8);
            $table->decimal('change_percentage', 10, 8);
            $table->json('exchanges')->nullable();
            $table->decimal('highest', 20, 8);
            $table->decimal('lowest', 20, 8);
            $table->timestamp('timestamp');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_aggregate');
    }
};
