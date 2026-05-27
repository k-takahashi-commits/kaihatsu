# 機材管理システム DBテーブル設計

## 前提

- WordPressの独自プラグインで使用する独自テーブルとして設計する。
- テーブル接頭辞はWordPressの `$wpdb->prefix` を使用する。
- 本資料では例として `wp_` 接頭辞で記載する。
- DB文字コードはWordPress標準に合わせて `utf8mb4` とする。
- CSV入出力時のみExcel利用前提でCP932に変換する。
- 機器コードは重複を許可する。
- データ識別は各テーブルの内部IDで行う。
- 物理削除は原則行わず、状態・ステータス・有効フラグで管理する。

## テーブル一覧

| No | テーブル名 | 用途 |
|---:|---|---|
| 1 | wp_equipment_items | 機材情報 |
| 2 | wp_equipment_repairs | 修理情報 |
| 3 | wp_equipment_locations | 場所マスター |
| 4 | wp_equipment_statuses | 機材状態マスター |
| 5 | wp_equipment_repair_statuses | 修理ステータスマスター |
| 6 | wp_equipment_usages | 利用用途マスター |
| 7 | wp_equipment_vendors | 修理依頼先マスター |
| 8 | wp_equipment_attachments | 添付ファイル紐付け |
| 9 | wp_equipment_logs | 操作ログ |
| 10 | wp_equipment_csv_imports | CSVインポート履歴 |
| 11 | wp_equipment_backup_logs | バックアップ履歴 |

## 1. 機材テーブル: wp_equipment_items

| カラム | 型 | NULL | 内容 |
|---|---|---:|---|
| id | BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY | 不可 | 内部ID |
| name | VARCHAR(255) | 不可 | 機器名 |
| location_id | BIGINT UNSIGNED | 不可 | 設置場所ID |
| model_number | VARCHAR(255) | 可 | 型番 |
| equipment_code | VARCHAR(255) | 可 | 機器コード、重複可 |
| installed_date | DATE | 不可 | 導入日 |
| usage_id | BIGINT UNSIGNED | 可 | 利用用途ID |
| status_id | BIGINT UNSIGNED | 不可 | 状態ID |
| note | TEXT | 可 | 備考 |
| created_by | BIGINT UNSIGNED | 不可 | 登録ユーザーID |
| updated_by | BIGINT UNSIGNED | 不可 | 更新ユーザーID |
| created_at | DATETIME | 不可 | 登録日時 |
| updated_at | DATETIME | 不可 | 更新日時 |

### インデックス

| インデックス名 | カラム |
|---|---|
| idx_location_id | location_id |
| idx_status_id | status_id |
| idx_usage_id | usage_id |
| idx_name | name |
| idx_model_number | model_number |
| idx_equipment_code | equipment_code |
| idx_installed_date | installed_date |
| idx_updated_at | updated_at |

## 2. 修理テーブル: wp_equipment_repairs

| カラム | 型 | NULL | 内容 |
|---|---|---:|---|
| id | BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY | 不可 | 修理ID |
| equipment_id | BIGINT UNSIGNED | 不可 | 対象機材ID |
| reporter_user_id | BIGINT UNSIGNED | 不可 | 記入者ユーザーID |
| trouble_location_id | BIGINT UNSIGNED | 不可 | 事故・故障場所ID |
| occurred_at | DATETIME | 不可 | 発生日時 |
| vendor_id | BIGINT UNSIGNED | 可 | 修理依頼先マスターID |
| vendor_name_free | VARCHAR(255) | 可 | 修理依頼先自由入力 |
| requested_date | DATE | 可 | 依頼日 |
| desired_completion_date | DATE | 可 | 完了希望日 |
| return_location_id | BIGINT UNSIGNED | 可 | 修理後送り先場所ID |
| return_address_free | VARCHAR(255) | 可 | 修理後送り先自由入力 |
| trouble_detail | TEXT | 不可 | 不具合状況 |
| assignee_user_id | BIGINT UNSIGNED | 可 | 担当WPユーザーID |
| assignee_name | VARCHAR(255) | 可 | 担当者名 |
| repair_cost | INT UNSIGNED | 可 | 修理費用 |
| status_id | BIGINT UNSIGNED | 不可 | 修理ステータスID |
| completed_date | DATE | 可 | 完了日 |
| note | TEXT | 可 | 備考 |
| created_by | BIGINT UNSIGNED | 不可 | 登録者 |
| updated_by | BIGINT UNSIGNED | 不可 | 更新者 |
| created_at | DATETIME | 不可 | 登録日時 |
| updated_at | DATETIME | 不可 | 更新日時 |

### インデックス

| インデックス名 | カラム |
|---|---|
| idx_equipment_id | equipment_id |
| idx_trouble_location_id | trouble_location_id |
| idx_status_id | status_id |
| idx_occurred_at | occurred_at |
| idx_requested_date | requested_date |
| idx_desired_completion_date | desired_completion_date |
| idx_completed_date | completed_date |
| idx_repair_cost | repair_cost |
| idx_updated_at | updated_at |

## 3. 場所マスター: wp_equipment_locations

| カラム | 型 | NULL | 内容 |
|---|---|---:|---|
| id | BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY | 不可 | 場所ID |
| name | VARCHAR(255) | 不可 | 場所名 |
| display_order | INT | 不可 | 表示順 |
| is_active | TINYINT(1) | 不可 | 有効フラグ |
| created_at | DATETIME | 不可 | 登録日時 |
| updated_at | DATETIME | 不可 | 更新日時 |

### インデックス

| インデックス名 | カラム |
|---|---|
| idx_name | name |
| idx_is_active | is_active |
| idx_display_order | display_order |

## 4. 機材状態マスター: wp_equipment_statuses

| カラム | 型 | NULL | 内容 |
|---|---|---:|---|
| id | BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY | 不可 | 状態ID |
| name | VARCHAR(100) | 不可 | 状態名 |
| display_order | INT | 不可 | 表示順 |
| is_active | TINYINT(1) | 不可 | 有効フラグ |
| created_at | DATETIME | 不可 | 登録日時 |
| updated_at | DATETIME | 不可 | 更新日時 |

### 初期値

| 状態名 | 備考 |
|---|---|
| 使用中 | 通常利用中 |
| 保管中 | 保管状態 |
| 修理中 | 修理対応中 |
| 故障中 | 故障中 |
| 廃棄 | 廃棄済み |
| 無効 | 利用対象外 |

## 5. 修理ステータスマスター: wp_equipment_repair_statuses

| カラム | 型 | NULL | 内容 |
|---|---|---:|---|
| id | BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY | 不可 | 修理ステータスID |
| name | VARCHAR(100) | 不可 | ステータス名 |
| display_order | INT | 不可 | 表示順 |
| is_closed | TINYINT(1) | 不可 | 完了扱いフラグ |
| is_active | TINYINT(1) | 不可 | 有効フラグ |
| created_at | DATETIME | 不可 | 登録日時 |
| updated_at | DATETIME | 不可 | 更新日時 |

### 初期値

| ステータス名 | 完了扱い |
|---|---:|
| 未対応 | 0 |
| 確認中 | 0 |
| 修理依頼済み | 0 |
| 修理中 | 0 |
| 完了 | 1 |
| キャンセル | 1 |

## 6. 利用用途マスター: wp_equipment_usages

| カラム | 型 | NULL | 内容 |
|---|---|---:|---|
| id | BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY | 不可 | 用途ID |
| name | VARCHAR(255) | 不可 | 用途名 |
| display_order | INT | 不可 | 表示順 |
| is_active | TINYINT(1) | 不可 | 有効フラグ |
| created_at | DATETIME | 不可 | 登録日時 |
| updated_at | DATETIME | 不可 | 更新日時 |

## 7. 修理依頼先マスター: wp_equipment_vendors

| カラム | 型 | NULL | 内容 |
|---|---|---:|---|
| id | BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY | 不可 | 修理依頼先ID |
| name | VARCHAR(255) | 不可 | 修理依頼先名 |
| phone | VARCHAR(50) | 可 | 電話番号 |
| email | VARCHAR(255) | 可 | メールアドレス |
| address | TEXT | 可 | 住所 |
| display_order | INT | 不可 | 表示順 |
| is_active | TINYINT(1) | 不可 | 有効フラグ |
| created_at | DATETIME | 不可 | 登録日時 |
| updated_at | DATETIME | 不可 | 更新日時 |

## 8. 添付ファイル紐付け: wp_equipment_attachments

| カラム | 型 | NULL | 内容 |
|---|---|---:|---|
| id | BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY | 不可 | ID |
| target_type | VARCHAR(50) | 不可 | equipment または repair |
| target_id | BIGINT UNSIGNED | 不可 | 対象ID |
| attachment_id | BIGINT UNSIGNED | 不可 | WordPressメディアID |
| file_name | VARCHAR(255) | 不可 | ファイル名 |
| file_size | INT UNSIGNED | 不可 | ファイルサイズ |
| created_by | BIGINT UNSIGNED | 不可 | 登録者 |
| created_at | DATETIME | 不可 | 登録日時 |

### 制約

- `target_type` と `target_id` の組み合わせごとに最大3件まで登録可能。
- 1ファイル最大5MBまで登録可能。
- 添付ファイルの追加・削除は操作ログに記録する。

### インデックス

| インデックス名 | カラム |
|---|---|
| idx_target | target_type, target_id |
| idx_attachment_id | attachment_id |

## 9. 操作ログ: wp_equipment_logs

| カラム | 型 | NULL | 内容 |
|---|---|---:|---|
| id | BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY | 不可 | ログID |
| user_id | BIGINT UNSIGNED | 可 | 操作者 |
| action | VARCHAR(100) | 不可 | 操作種別 |
| target_type | VARCHAR(100) | 不可 | 対象種別 |
| target_id | BIGINT UNSIGNED | 可 | 対象ID |
| before_data | LONGTEXT | 可 | 変更前JSON |
| after_data | LONGTEXT | 可 | 変更後JSON |
| ip_address | VARCHAR(45) | 可 | IPアドレス |
| user_agent | TEXT | 可 | ユーザーエージェント |
| created_at | DATETIME | 不可 | 操作日時 |

### インデックス

| インデックス名 | カラム |
|---|---|
| idx_user_id | user_id |
| idx_action | action |
| idx_target | target_type, target_id |
| idx_created_at | created_at |

### 保存期間

- 10年間保存する。
- 保存期間内のログは管理画面から検索・閲覧できるようにする。

## 10. CSVインポート履歴: wp_equipment_csv_imports

| カラム | 型 | NULL | 内容 |
|---|---|---:|---|
| id | BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY | 不可 | インポートID |
| import_type | VARCHAR(50) | 不可 | equipment / repair / master |
| file_name | VARCHAR(255) | 不可 | CSVファイル名 |
| total_count | INT | 不可 | 総行数 |
| success_count | INT | 不可 | 成功件数 |
| error_count | INT | 不可 | エラー件数 |
| error_detail | LONGTEXT | 可 | エラー詳細JSON |
| created_by | BIGINT UNSIGNED | 不可 | 実行者 |
| created_at | DATETIME | 不可 | 実行日時 |

## 11. バックアップ履歴: wp_equipment_backup_logs

| カラム | 型 | NULL | 内容 |
|---|---|---:|---|
| id | BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY | 不可 | バックアップID |
| backup_type | VARCHAR(50) | 不可 | db / files / full |
| file_path | TEXT | 不可 | 保存先 |
| file_size | BIGINT UNSIGNED | 可 | サイズ |
| status | VARCHAR(50) | 不可 | success / failed |
| message | TEXT | 可 | メッセージ |
| created_at | DATETIME | 不可 | 実行日時 |

### 保存期間

- バックアップファイルは30日保持する。
- DB、WordPressファイル、アップロードファイルを対象とする。

## リレーション概要

| 親テーブル | 子テーブル | 関係 |
|---|---|---|
| wp_equipment_locations | wp_equipment_items | 1対多 |
| wp_equipment_statuses | wp_equipment_items | 1対多 |
| wp_equipment_usages | wp_equipment_items | 1対多 |
| wp_equipment_items | wp_equipment_repairs | 1対多 |
| wp_equipment_locations | wp_equipment_repairs | 1対多 |
| wp_equipment_repair_statuses | wp_equipment_repairs | 1対多 |
| wp_equipment_vendors | wp_equipment_repairs | 1対多 |
| wp_equipment_items / wp_equipment_repairs | wp_equipment_attachments | 1対多 |
| WordPress users | wp_equipment_items / wp_equipment_repairs / wp_equipment_logs | 1対多 |

## 実装メモ

- 外部キー制約はWordPressの運用互換性を考慮し、DB制約ではなくアプリケーション側で整合性を担保する。
- マスター削除は物理削除ではなく `is_active = 0` とする。
- 修理依頼先は `vendor_id` と `vendor_name_free` を併用し、マスターにない依頼先も入力可能にする。
- 機材の廃棄・無効化は `status_id` によって管理する。
- ガントチャートは `requested_date`、`desired_completion_date`、`completed_date` を使用して月単位で表示する。
- 修理費用は税込・税別区分を持たず、金額のみ保存する。
- CSV取込・出力処理、添付ファイル制御、PDF生成は、それぞれ専用クラスに分離して実装する。
