<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class UsersAddGoogleToken extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("ALTER TABLE `users` ADD `google_refresh_token` VARCHAR(255) NULL AFTER `user_type`");
    }

    public function down(): void
    {
        // ここに元に戻すSQLを書く
    }
}