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
        Schema::create('hero_sections', function (Blueprint $table) {
            $table->id();
            $table->string("title");
            $table->string("sub_title")->nullable();
            $table->string("description")->nullable();
            $table->string("image");
            $table->longText("body")->nullable();
            $table->integer("position");
            $table->enum("status", ["Active", "Draft", "Archived", "Deleted"])->default("Draft");
            $table->integer("created_by");
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hero_sections');
    }
};
