<?php
declare(strict_types=1);

class Database {
    // 接続情報をプロパティとして保持（config.phpから読み込む想定）
    private string $host;
    private string $db;
    private string $user;
    private string $pass;
    private string $charset = 'utf8';
    private ?PDO $pdo = null;
    private string $log = "";

    public function __construct() {
        // 本来は config.php の定数などを使用します
        $this->host = DB_HOST;
        $this->db   = DB_NAME;
        $this->user = USER_NAME;
        $this->pass = PASSWORD;
        //newしたphpファイル名を$fileに取得
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $file = isset($backtrace[0]['file']) ? basename($backtrace[0]['file']) : 'unknown';
        $this->log = "-- Source: {$file} " . date('Y-m-d H:i:s') . "\n";
        
    }
    public function __destruct() {
      // スクリプトが終わる時に自動でこれが呼ばれる
      //echo "ログアウト処理や一時的なメモリの解放を行います。";
    }
    /**
     * PDOインスタンスを取得する（シングルトンパターンに近い形）
     */
    public function connect(): PDO {
        if ($this->pdo !== null) {
            return $this->pdo;
        }

        $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // エラー時に例外を投げる
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // デフォルトを連想配列に
            PDO::ATTR_EMULATE_PREPARES   => false,                  // 静的プレパレードステートメントを使用
            PDO::MYSQL_ATTR_MULTI_STATEMENTS => false               // マルチステートメントを使用しない
        ];

        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
            return $this->pdo;
        } catch (PDOException $e) {
            // プロの現場ではログに出力し、ユーザーには詳細を見せない
            error_log($e->getMessage());
            throw new Exception("データベース接続に失敗しました。");
        }
    }

    public function SELECT(string $sql,array $params=[]):array{
      $stmt = $this->connect()->prepare($sql);
      $stmt -> execute($params);
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function INSERT(string $table,array $data=[]):bool{
      $columns = "`".implode('`, `', array_keys($data))."`";  //項目名をカンマ区切りで取得
      $placeholders = ':' . implode(', :', array_keys($data));

      $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
      log_writer2("\$sql",$sql,"lv3");
      //$this->logに実行できるSQL文を書き込む
      $log = $sql;
      foreach ($data as $key => $value) {
        $log = str_replace(":".$key, (is_string($value) ? "'$value'" : (string)$value), $log);
      }
      $this->log .= $log.";\n";

      $stmt = $this->connect()->prepare($sql);
      return $stmt -> execute($data);
    }

    public function UP_DEL_EXEC(string $sql,array $data=[]):bool{
      $log = $sql;
      foreach ($data as $key => $value) {
        $log = str_replace($key, (is_string($value) ? "'$value'" : (string)$value), $log);
      }
      $this->log .= $log.";\n";

      $stmt = $this->connect()->prepare($sql);
      return $stmt -> execute($data);
    }

    public function begin_tran():void{
      $this->log .= "start transaction;\n";
      $this->connect()->beginTransaction();
    }
    public function commit_tran():void{
      $this->log .= "commit;\n";
      $this->connect()->commit();
      $this->error_log();
    }
    public function rollback_tran():void{
      $this->log .= "rollback;\n";
      $this->connect()->rollback();
      $this->error_log();
    }

    private function error_log():void{
      //sqllog/日付.sql ファイルに$msgを追記
      $dir = 'sqllog';
      if (!is_dir($dir)) {
          mkdir($dir, 0777, true);
      }
      $filename = $dir . '/' . date('Ymd') . '.sql';
      file_put_contents($filename, $this->log . PHP_EOL, FILE_APPEND);
    }
    

}
?>