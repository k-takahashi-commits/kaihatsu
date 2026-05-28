# 機材管理システム 開発スコープ・概算工数

## 前提

- WordPress専用環境を新規構築する。
- 機材管理システムは独自プラグインとして実装する。
- データは独自DBテーブルで管理する。
- 管理機材は約5,000件を想定する。
- 利用者は約50名、管理者は約10名を想定する。
- 管理画面は日本語のみ対応する。
- スマートフォン、タブレットからの入力も考慮する。

## 開発スコープ

| 区分 | 内容 |
|---|---|
| WordPress環境構築 | 専用WordPress構築、初期設定、基本セキュリティ設定 |
| プラグイン基盤 | 独自プラグイン作成、DB作成、ロール・capability作成 |
| 機材管理 | 一覧、検索、登録、編集、詳細、添付ファイル管理 |
| 修理管理 | 一覧、検索、起票、編集、詳細、ステータス管理、添付ファイル管理 |
| マスター管理 | 場所、状態、用途、修理依頼先、修理ステータスの管理 |
| CSV機能 | インポート、確認プレビュー、エラー表示、エクスポート |
| 添付ファイル | WordPressメディア連携、5MB・3ファイル制限 |
| ダッシュボード | 集計、グラフ、未完了一覧、期限接近一覧 |
| ガントチャート | 月単位表示、検索条件連動 |
| PDF出力 | ガントチャートPDF出力、日本語フォント対応 |
| ログ機能 | 操作ログ記録、ログ検索、ログ詳細表示 |
| 権限管理 | 管理者・登録者ロール制御、メニュー表示制御 |
| バックアップ | 毎日バックアップ、30日保持の運用設計 |
| テスト | 単体、結合、権限、CSV、レスポンシブ、表示確認 |
| 運用資料 | 操作手順、CSVフォーマット、バックアップ・復元手順 |

## プラグインディレクトリ構成

```text
equipment-management/
├── equipment-management.php
├── uninstall.php
├── readme.txt
├── includes/
│   ├── class-plugin.php
│   ├── class-activator.php
│   ├── class-deactivator.php
│   ├── class-db.php
│   ├── class-roles.php
│   ├── class-logger.php
│   ├── class-permissions.php
│   ├── class-csv-importer.php
│   ├── class-csv-exporter.php
│   ├── class-attachments.php
│   ├── class-pdf.php
│   └── helpers.php
├── admin/
│   ├── class-admin-menu.php
│   ├── class-dashboard-page.php
│   ├── class-equipment-page.php
│   ├── class-repair-page.php
│   ├── class-master-page.php
│   ├── class-csv-page.php
│   ├── class-log-page.php
│   ├── class-gantt-page.php
│   ├── class-backup-page.php
│   ├── class-user-page.php
│   └── views/
│       ├── dashboard.php
│       ├── equipment-list.php
│       ├── equipment-form.php
│       ├── equipment-detail.php
│       ├── repair-list.php
│       ├── repair-form.php
│       ├── repair-detail.php
│       ├── master-list.php
│       ├── csv-import.php
│       ├── logs.php
│       ├── gantt.php
│       ├── backup.php
│       └── users.php
├── assets/
│   ├── css/
│   │   └── admin.css
│   └── js/
│       ├── admin.js
│       ├── equipment.js
│       ├── repair.js
│       ├── dashboard.js
│       └── gantt.js
├── templates/
│   └── pdf/
│       └── gantt-pdf.php
├── vendor/
│   └── .gitkeep
└── languages/
    └── .gitkeep
```

## 主要クラス責務

| クラス | 責務 |
|---|---|
| includes/class-plugin.php | プラグイン全体の初期化、フック登録 |
| includes/class-activator.php | 有効化時のDB作成、初期データ投入、ロール作成 |
| includes/class-deactivator.php | 無効化時の処理。データ削除は行わない |
| includes/class-db.php | テーブル名管理、DB作成SQL、共通DB処理 |
| includes/class-roles.php | ロールとcapability管理 |
| includes/class-permissions.php | 権限判定 |
| includes/class-logger.php | 操作ログ保存 |
| includes/class-csv-importer.php | CSV新規登録インポート処理 |
| includes/class-csv-exporter.php | CSVエクスポート処理 |
| includes/class-attachments.php | 添付ファイル上限、紐付け、削除制御 |
| includes/class-pdf.php | mPDFによるPDF生成 |
| admin/class-admin-menu.php | 管理メニュー登録 |
| admin/class-*-page.php | 各管理画面のリクエスト処理、ビュー呼び出し |

## 概算工数

| 工程 | 目安 |
|---|---:|
| 詳細設計 | 5〜8人日 |
| DB・プラグイン基盤 | 5〜8人日 |
| 機材管理 | 8〜12人日 |
| 修理管理 | 10〜15人日 |
| マスター管理 | 4〜6人日 |
| CSV機能 | 6〜10人日 |
| 添付ファイル | 3〜5人日 |
| ダッシュボード | 6〜10人日 |
| ガントチャート・PDF | 8〜12人日 |
| ログ機能 | 4〜7人日 |
| 権限管理 | 3〜5人日 |
| テスト・調整 | 8〜12人日 |
| 運用資料作成 | 3〜5人日 |

## 合計目安

| 範囲 | 工数 |
|---|---:|
| 最小構成 | 約73人日 |
| 標準構成 | 約90人日 |
| 余裕を見た構成 | 約115人日 |

## 工数が増減する要素

### 増加要素

- 管理画面UIを独自デザインで作り込む場合
- ガントチャートの操作性を高める場合
- PDFレイアウトを帳票レベルで厳密に整える場合
- CSV取込時に既存データ更新や差分更新を高度に行う場合
- 添付ファイルのプレビューや権限制御を細かく行う場合
- バックアップ復元機能まで管理画面に実装する場合

### 減少要素

- WordPress管理画面標準UIに寄せる場合
- グラフやガントチャートを既存ライブラリ標準表示に寄せる場合
- CSV更新仕様を新規登録中心に限定する場合
- CSVインポートを新規登録のみとし、既存更新・差分更新を行わない場合
- PDF出力を簡易帳票として扱う場合

## 推奨フェーズ分け

## 採用候補ライブラリ

### 推奨構成

| 用途 | 推奨ライブラリ | 採用理由 |
|---|---|---|
| ガントチャート表示 | Frappe Gantt | オープンソースで軽量。月単位の修理予定表示に向いており、WordPress管理画面に組み込みやすい。 |
| PDF出力 | mPDF | PHP側でHTMLからPDF生成でき、日本語フォント埋め込みに対応しやすい。 |
| ダッシュボードグラフ | Chart.js | ステータス別件数、月別件数、費用集計などの一般的なグラフ表示に十分。 |
| 管理画面UI | WordPress標準UI + 独自JavaScript | WordPress管理画面との統一感を保ち、保守性を高めやすい。 |

### 推奨理由

- ガントチャート表示はFrappe Ganttで行い、PDF出力はmPDFで帳票用HTMLを再構成して生成する。
- 画面表示用ガントとPDF出力用レイアウトを分けることで、日本語PDFの文字化けやレイアウト崩れを抑えやすい。
- mPDFでは日本語フォントを設定し、PDF内に必要な文字を埋め込む方針とする。
- Chart.jsはダッシュボードの棒グラフ、折れ線グラフ、円グラフに利用する。

### 代替候補

| 用途 | ライブラリ | 備考 |
|---|---|---|
| 高機能ガントチャート | DHTMLX Gantt | PDF/PNG/Excel出力などの機能が豊富。ただし商用利用時のライセンス確認が必要。 |
| PDF出力 | dompdf | HTMLからPDF生成可能。ただし日本語フォントや複雑なレイアウトではmPDFを優先する。 |
| ブラウザ側PDF出力 | jsPDF / html2canvas | 画面キャプチャ型の出力に向くが、日本語や改ページ制御、帳票品質の観点で優先度は低い。 |

### 採用方針

初期実装では以下を採用候補の第一案とする。

```text
ガントチャート表示: Frappe Gantt
PDF出力: mPDF
ダッシュボードグラフ: Chart.js
```

### フェーズ1: 基盤・機材管理

- WordPress専用環境構築
- 独自プラグイン基盤
- DB作成
- 権限設定
- 機材管理
- マスター管理

### フェーズ2: 修理管理・添付

- 修理管理
- 添付ファイル管理
- 修理履歴表示
- 操作ログの基本記録

### フェーズ3: CSV・ダッシュボード

- CSVインポート
- CSVエクスポート
- ダッシュボード
- 集計表示

### フェーズ4: ガントチャート・PDF・運用

- ガントチャート
- PDF出力
- ログ検索
- バックアップ運用設計
- テスト
- 操作資料作成

## 納品物

| 納品物 | 内容 |
|---|---|
| WordPress環境 | 専用管理システム環境 |
| 独自プラグイン | 機材管理システム本体 |
| DB定義 | 独自テーブル作成SQLまたはマイグレーション処理 |
| 操作マニュアル | 管理者向け、登録者向け |
| CSVフォーマット | 機材、修理、各種マスター |
| テスト結果 | 主要機能、権限、CSV、添付、PDF |
| バックアップ手順 | バックアップ・復元方法 |

## ネクストアクション

1. 画面一覧、画面項目、DB設計、権限設計、CSV設計、ログ仕様、ダッシュボード仕様をレビューする。
2. CSVインポートは新規登録のみとして確定済み。既存更新・差分更新は実装対象外とする。
3. ガントチャートとPDF出力に使うライブラリ候補を選定する。
4. WordPressプラグインのディレクトリ構成を決める。
5. DB作成処理、ロール作成処理、管理メニュー作成処理から実装を開始する。
6. まずはフェーズ1として、機材管理とマスター管理を動く状態にする。
