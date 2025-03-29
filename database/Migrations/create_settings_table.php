<?php

namespace AiChatbot\Database\Migrations;

use Prappo\WpEloquent\Database\Migration;

class CreateSettingsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    $this->schema->create('aicb_settings', function ($table) {
      $table->id();
      $table->string('key')->unique();
      $table->text('value');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    $this->schema->dropIfExists('aicb_settings');
  }
}
