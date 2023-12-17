<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('announces', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('torrent_id')->index();
            $table->unsignedBigInteger('uploaded');
            $table->unsignedBigInteger('downloaded');
            $table->unsignedBigInteger('left');
            $table->unsignedBigInteger('corrupt');
            $table->unsignedSmallInteger('port');
            $table->unsignedSmallInteger('numwant');
            $table->timestamp('created_at')->useCurrent();
            $table->string('event');
            $table->string('key');

            $table->index(['user_id', 'torrent_id']);
        });

        DB::statement("ALTER TABLE announces ADD COLUMN peer_id BINARY(20) NOT NULL AFTER corrupt");
    }
};
