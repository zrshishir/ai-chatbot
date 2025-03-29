<?php

namespace AiChatbot\Database\Migrations;

use Prappo\WpEloquent\Database\Migration;

class CreateChatHistoryTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    $this->schema->create('aicb_chat_history', function ($table) {
      $table->id();
      $table->unsignedBigInteger('user_id');
      $table->text('message');
      $table->enum('type', ['user', 'bot']);
      $table->json('metadata')->nullable();
      $table->timestamps();

      $table->foreign('user_id')
        ->references('ID')
        ->on($this->wpdb->users)
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
    $this->schema->dropIfExists('aicb_chat_history');
  }
}
