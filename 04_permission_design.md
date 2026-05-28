# 機材管理システム 権限設計

## 前提

- WordPressのロールと独自capabilityを組み合わせて権限を制御する。
- 管理者は全機能を利用可能とする。
- 登録者 / 運営者は、機材情報と修理情報の閲覧・登録・編集を可能とする。
- 登録者 / 運営者は、自分が登録したデータに限らず全機材・全修理情報を編集可能とする。
- 物理削除は原則禁止し、状態変更または無効化で履歴を保持する。

## ロール定義

| ロール | 表示名 | 用途 |
|---|---|---|
| equipment_admin | 機材管理者 | 全機能を利用できる管理者 |
| equipment_operator | 機材登録者 | 機材・修理情報を登録、編集する利用者 |

## 機能別権限

| 機能 | 管理者 | 登録者 / 運営者 |
|---|---:|---:|
| ダッシュボード閲覧 | 可 | 可 |
| 機材一覧閲覧 | 可 | 可 |
| 機材詳細閲覧 | 可 | 可 |
| 機材登録 | 可 | 可 |
| 機材編集 | 可 | 可 |
| 機材物理削除 | 不可 | 不可 |
| 機材の廃棄・無効化 | 可 | 可 |
| 修理一覧閲覧 | 可 | 可 |
| 修理詳細閲覧 | 可 | 可 |
| 修理起票 | 可 | 可 |
| 修理編集 | 可 | 可 |
| 修理物理削除 | 不可 | 不可 |
| CSVエクスポート | 可 | 可 |
| CSVインポート | 可 | 不可 |
| マスター管理 | 可 | 不可 |
| ログ閲覧 | 可 | 不可 |
| ユーザー権限管理 | 可 | 不可 |
| バックアップ状況確認 | 可 | 不可 |

## 独自Capability案

| Capability | 内容 | 管理者 | 登録者 / 運営者 |
|---|---|---:|---:|
| equipment_view_dashboard | ダッシュボード閲覧 | 付与 | 付与 |
| equipment_view_items | 機材閲覧 | 付与 | 付与 |
| equipment_edit_items | 機材登録・編集 | 付与 | 付与 |
| equipment_view_repairs | 修理閲覧 | 付与 | 付与 |
| equipment_edit_repairs | 修理登録・編集 | 付与 | 付与 |
| equipment_export_csv | CSVエクスポート | 付与 | 付与 |
| equipment_import_csv | CSVインポート | 付与 | なし |
| equipment_manage_masters | マスター管理 | 付与 | なし |
| equipment_view_logs | ログ閲覧 | 付与 | なし |
| equipment_manage_users | ユーザー権限管理 | 付与 | なし |
| equipment_view_backup | バックアップ状況確認 | 付与 | なし |

## 管理メニュー表示制御

| メニュー | 必要Capability |
|---|---|
| ダッシュボード | equipment_view_dashboard |
| 機材一覧 | equipment_view_items |
| 機材登録 | equipment_edit_items |
| 修理一覧 | equipment_view_repairs |
| 修理起票 | equipment_edit_repairs |
| ガントチャート | equipment_view_repairs |
| CSVインポート | equipment_import_csv |
| マスター管理 | equipment_manage_masters |
| ログ | equipment_view_logs |
| ユーザー権限 | equipment_manage_users |
| バックアップ | equipment_view_backup |

## 実装クラス対応

| 領域 | クラス | 内容 |
|---|---|---|
| ロール作成 | includes/class-roles.php | ロールとcapabilityの登録 |
| 権限判定 | includes/class-permissions.php | 画面表示・処理実行時の権限確認 |
| ユーザー権限画面 | admin/class-user-page.php | 管理者によるユーザー権限管理 |
| バックアップ画面 | admin/class-backup-page.php | バックアップ状況・履歴確認 |

## 注意事項

- WordPress標準の管理者ロールにも、必要に応じて全capabilityを付与する。
- プラグイン有効化時にロールとcapabilityを作成する。
- プラグイン無効化時は、原則としてロールやデータは削除しない。
- ユーザーの誤操作防止のため、削除ボタンは設けず、廃棄・無効・キャンセルの状態管理を基本とする。
