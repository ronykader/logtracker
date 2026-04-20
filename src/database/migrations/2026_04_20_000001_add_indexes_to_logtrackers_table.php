<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToLogtrackersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('logtrackers', function (Blueprint $table) {
            // Index for fast pruning and Heatmap generation
            $table->index('log_date');
            
            // Indexes for Dashboard data filtering
            $table->index('table_name');
            $table->index('user_id');
            $table->index('log_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('logtrackers', function (Blueprint $table) {
            $table->dropIndex(['log_date']);
            $table->dropIndex(['table_name']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['log_type']);
        });
    }
}
