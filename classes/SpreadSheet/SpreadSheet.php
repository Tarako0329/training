<?php
declare(strict_types=1);
namespace classes\SpreadSheet;

class SpreadSheet {
	private $service;
	private $spreadsheetId;
	private $driveService;
	public readonly bool $is_new_file;
	//private $sheetName = 'トレーニング記録';

	public function __construct($client, $fileName) {
		$this->service = new \Google\Service\Sheets($client);
		$this->driveService = new \Google\Service\Drive($client);
		$this->getOrCreateSpreadsheet($fileName);
	}

		// ファイル名からIDを取得、なければ作成
	private function getOrCreateSpreadsheet($fileName) {
		$query = "name = '$fileName' and mimeType = 'application/vnd.google-apps.spreadsheet' and trashed = false";
		$response = $this->driveService->files->listFiles(['q' => $query, 'fields' => 'files(id, name)']);
		$spreadsheetId = "";
		
		if (count($response->files) > 0) {
			$spreadsheetId = $response->files[0]->id;
			$this->is_new_file = false;
			log_writer2("既存のスプレッドシートを使用",$spreadsheetId,"lv3");
		} else {
			// 新規作成
			$spreadsheet = new \Google\Service\Sheets\Spreadsheet([
				'properties' => ['title' => $fileName]
			]);
			$spreadsheet = $this->service->spreadsheets->create($spreadsheet);
			$spreadsheetId = $spreadsheet->spreadsheetId;
			$this->is_new_file = true;
		}
		$this->spreadsheetId = $spreadsheetId;
	}

	/**
 	* ファイル名を指定して名前を変更する
 	* @param string $sheetName
 	* @param string $sheetName_new
 	* @return string 'success' | 'warning' (重複のため作成スキップ) | 'error'
 	*/
	public function RENAME_SHEET($sheetName, $sheetName_new):string {
		try {
			// 1. スプレッドシートの全シート情報を取得してIDを探す
			$spreadsheet = $this->service->spreadsheets->get($this->spreadsheetId);
			$sheets = $spreadsheet->getSheets();
			$targetSheetId = null;

			foreach ($sheets as $sheet) {
				if ($sheet->getProperties()->getTitle() === $sheetName) {
					$targetSheetId = $sheet->getProperties()->getSheetId();
					break;
				}
			}

			// 2. 見つからない場合はwarningを返す
			if ($targetSheetId === null) {
				return 'warning';
			}

			// 3. 名前変更リクエストを実行
			$body = new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
				'requests' => [
					['updateSheetProperties' => [
						'properties' => [
							'sheetId' => $targetSheetId,
							'title' => $sheetName_new
						],
						'fields' => 'title'
					]]
				]
			]);

			$this->service->spreadsheets->batchUpdate($this->spreadsheetId, $body);
			return 'success';

		} catch (\Exception $e) {
			return 'error';
		}
	}


	/**
 	* シート名を指定してシートを作成する
 	* @param string $sheetName
 	* @return string 'success' | 'warning' (重複のため作成スキップ) | 'error'
 	*/
	public function createLogSheet($sheetName):string {
		$body = new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
			'requests' => [['addSheet' => ['properties' => ['title' => $sheetName]]]]
		]);
		
		try {
        $this->service->spreadsheets->batchUpdate($this->spreadsheetId, $body);
        return 'success';
    } catch (\Google\Service\Exception $e) {
        $error = json_decode($e->getMessage(), true);
        
        // エラーメッセージの中に "already exists" が含まれているか確認
        if (isset($error['error']['message']) && str_contains($error['error']['message'], 'already exists')) {
            return 'warning'; // 同名のシートが既に存在する場合
        }
        
        // それ以外のGoogle APIエラー
        return 'error';
    } catch (\Exception $e) {
        // ネットワークエラーなど、その他の例外
				log_writer2("シート追加作成でエラー",$e,"lv0");
        return 'error';
    }
	}	

	// データ挿入（末尾追加）
	public function G_INSERT($values,$sheetName) {
		log_writer2("G_INSERT",$values,"lv3");
		$body = new \Google\Service\Sheets\ValueRange(['values' => $values]);
		$params = ['valueInputOption' => 'RAW'];
		return $this->service->spreadsheets_values->append($this->spreadsheetId, $sheetName . '!A1', $body, $params);
	}

// SEQを利用して更新
	public function G_UPDATE($seq, $newValues, $sheetName) {
		$rowNumber = $this->findRowBySeq($seq, $sheetName);
		if (!$rowNumber) return false; // 見つからない場合

		$range = $sheetName . "!A" . $rowNumber;
		$body = new \Google\Service\Sheets\ValueRange(['values' => [$newValues]]);
		$params = ['valueInputOption' => 'RAW'];
			
		return $this->service->spreadsheets_values->update(
			$this->spreadsheetId, 
			$range, 
			$body, 
			$params
		);
	}

	// SEQを利用して削除（行をクリア）
	public function G_DELETE($seq, $sheetName) {
		$rowNumber = $this->findRowBySeq($seq, $sheetName);
		if (!$rowNumber) return false;

		$range = $sheetName . "!A" . $rowNumber . ":Z" . $rowNumber;
		$requestBody = new \Google\Service\Sheets\ClearValuesRequest();
		return $this->service->spreadsheets_values->clear(
			$this->spreadsheetId, 
			$range, 
			$requestBody
		);
	}

	/**
 	* シート名を指定してシートを削除する
 	* @param string $sheetName
 	* @return string 'success' | 'warning' (見つからない) | 'error'
 	*/
	public function DELETE_SHEET($sheetName) {
    try {
        // 1. スプレッドシートの全シート情報を取得してIDを探す
        $spreadsheet = $this->service->spreadsheets->get($this->spreadsheetId);
        $sheets = $spreadsheet->getSheets();
        $targetSheetId = null;

        foreach ($sheets as $sheet) {
            if ($sheet->getProperties()->getTitle() === $sheetName) {
                $targetSheetId = $sheet->getProperties()->getSheetId();
                break;
            }
        }

        // 2. 見つからない場合はwarningを返す
        if ($targetSheetId === null) {
            return 'warning';
        }

        // 3. 削除リクエストを実行
        $body = new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
            'requests' => [
                ['deleteSheet' => ['sheetId' => $targetSheetId]]
            ]
        ]);

        $this->service->spreadsheets->batchUpdate($this->spreadsheetId, $body);
        return 'success';

    } catch (\Exception $e) {
        return 'error';
    }
	}

	// A列からSEQを探し、行番号（1始まり）を返す
	private function findRowBySeq($seq, $sheetName) {
		$range = $sheetName . '!A:A'; // A列全体を取得
		$response = $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
		$values = $response->getValues();

		if (!$values) return null;

		foreach ($values as $index => $row) {
			if (isset($row[0]) && $row[0] == $seq) {
				return $index + 1; // 行番号はインデックス+1
			}
		}
		return null;
	}}


?>