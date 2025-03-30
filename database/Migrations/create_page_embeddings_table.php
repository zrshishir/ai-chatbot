<?php

namespace AiChatbot\Database\Migrations;

use AiChatbot\Interfaces\Migration;

class CreatePageEmbeddingsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    $this->schema->create('aicb_page_embeddings', function ($table) {
      $table->id();
      $table->unsignedBigInteger('post_id');
      $table->text('content');
      $table->json('embedding');
      $table->json('metadata')->nullable();
      $table->timestamps();

      $table->foreign('post_id')
        ->references('ID')
        ->on($this->wpdb->posts)
        ->onDelete('cascade');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    $this->schema->dropIfExists('aicb_page_embeddings');
  }
}
