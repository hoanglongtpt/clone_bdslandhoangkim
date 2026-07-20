<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 100)->nullable()->unique()->after('name');
            $table->string('avatar_path')->nullable()->after('email');
            $table->date('birthday')->nullable()->after('avatar_path');
            $table->string('gender', 20)->nullable()->after('birthday');
            $table->string('phone1', 30)->nullable()->after('gender');
            $table->string('phone2', 30)->nullable()->after('phone1');
            $table->string('zalo')->nullable()->after('phone2');
            $table->string('skype')->nullable()->after('zalo');
            $table->string('facebook')->nullable()->after('skype');
            $table->string('address')->nullable()->after('facebook');
            $table->text('bio')->nullable()->after('address');
        });

        Schema::table('notes', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('author');
            $table->index(['user_id', 'note_date'], 'notes_user_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropIndex('notes_user_date_index');
            $table->dropColumn('user_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_username_unique');
            $table->dropColumn([
                'username', 'avatar_path', 'birthday', 'gender', 'phone1', 'phone2',
                'zalo', 'skype', 'facebook', 'address', 'bio',
            ]);
        });
    }
};
