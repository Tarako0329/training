<?php
declare(strict_types=1);
namespace classes\SpreadSheet;

class SpreadSheet {
    private $service;
    private $spreadsheetId;
    private $driveService;
    private $sheetName = 'トレーニング記録';

    public function __construct($client, $fileName) {
        $this->service = new \Google\Service\Sheets($client);
        $this->driveService = new \Google\Service\Drive($client);
        $this->spreadsheetId = $this->getOrCreateSpreadsheet($fileName);
    }

    // ファイル名からIDを取得、なければ作成
    private function getOrCreateSpreadsheet($fileName) {
        $query = "name = '$fileName' and mimeType = 'application/vnd.google-apps.spreadsheet' and trashed = false";
        $response = $this->driveService->files->listFiles(['q' => $query, 'fields' => 'files(id, name)']);
        
        if (count($response->files) > 0) {
            return $response->files[0]->id;
        } else {
            // 新規作成
            $spreadsheet = new \Google\Service\Sheets\Spreadsheet([
                'properties' => ['title' => $fileName]
            ]);
            $spreadsheet = $this->service->spreadsheets->create($spreadsheet);
            $this->createLogSheet($spreadsheet->spreadsheetId);
            return $spreadsheet->spreadsheetId;
        }
    }

    private function createLogSheet($id) {
        $body = new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
            'requests' => [['addSheet' => ['properties' => ['title' => $this->sheetName]]]]
        ]);
        $this->service->spreadsheets->batchUpdate($id, $body);
        // ヘッダー挿入
        $this->G_INSERT([['日付', '種目', '重量', '回数', '推定1RM', 'メモ']]);
    }

    // データ挿入（末尾追加）
    public function G_INSERT($values) {
        $body = new \Google\Service\Sheets\ValueRange(['values' => $values]);
        $params = ['valueInputOption' => 'RAW'];
        return $this->service->spreadsheets_values->append($this->spreadsheetId, $this->sheetName . '!A1', $body, $params);
    }

    // データ更新（特定行の置換など：簡易版）
    public function G_UPDATE($range, $values) {
        $body = new \Google\Service\Sheets\ValueRange(['values' => $values]);
        $params = ['valueInputOption' => 'RAW'];
        return $this->service->spreadsheets_values->update($this->spreadsheetId, $this->sheetName . '!' . $range, $body, $params);
    }

    // データ削除（特定範囲のクリア）
    public function G_DELETE($range) {
        $requestBody = new \Google\Service\Sheets\ClearValuesRequest();
        return $this->service->spreadsheets_values->clear($this->spreadsheetId, $this->sheetName . '!' . $range, $requestBody);
    }
}


?>