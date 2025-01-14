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
        Schema::create('posts', function (Blueprint $table) {
            $table->uuid('id')->unique()->index();
            $table->string("title");
            $table->string("sub_title")->nullable();
            $table->string("description");
            $table->string("short_description")->nullable();
            $table->string("slug");
            $table->string("main_image");
            $table->unsignedBigInteger('category_id')->nullable();
            $table->longText("body");
            $table->enum("status", ["Active", "Draft", "Archived", "Deleted"])->default("Draft");
            $table->bigInteger("views")->nullable();
            $table->bigInteger("created_by");
            $table->bigInteger("updated_by")->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('category_id', 'categories_id_foreign')->references('id')
                ->on('categories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
