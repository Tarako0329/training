<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class UsersAddSpData extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("ALTER TABLE `users` ADD `spsfilename` VARCHAR(100) NULL COMMENT 'スプレッドシートファイル名' AFTER `google_refresh_token`, ADD `mokuhyou` TEXT NULL COMMENT 'トレーニング目標' AFTER `spsfilename`;");
    }

    public function down(): void
    {
        // ここに元に戻すSQLを書く
    }
}