# gh-analyzer

GitHub repository analyzer CLI application built with Laravel Zero.

## 概要

指定した期間・ユーザー・リポジトリに対する「受けたレビューコメント一覧」を取得するCLIツールです。PR作成日時期間でPRを絞り込み、対象PRに付いたコードレビューコメント(pull request review comments)とIssueコメント(PR会話)を取得し、CLI表示およびMarkdown出力(オプション)を行います。

## インストール

### 前提条件

- PHP 8.2 以上
- Composer
- GitHub Personal Access Token

### セットアップ

1. リポジトリをクローン:
```bash
git clone https://github.com/kuri0616/gh-analyzer.git
cd gh-analyzer
```

2. 依存関係をインストール:
```bash
composer install
```

3. 実行可能にする:
```bash
chmod +x application
```

## 使い方

### 基本的な使用法

```bash
./application analyze:received-comments \
  --user=your-username \
  --repo=owner/repository \
  --from=2023-01-01 \
  --to=2023-01-31 \
  --token=your_github_token
```

### 環境変数でトークンを設定

```bash
export GITHUB_TOKEN=your_github_token
./application analyze:received-comments \
  --user=your-username \
  --repo=owner/repository \
  --from=2023-01-01 \
  --to=2023-01-31
```

### Markdownファイル出力

```bash
./application analyze:received-comments \
  --user=your-username \
  --repo=owner/repository \
  --from=2023-01-01 \
  --to=2023-01-31 \
  --markdown=report.md
```

## オプション一覧

| オプション | 必須 | デフォルト | 説明 |
|-----------|------|------------|------|
| `--user` | ✅ | - | 分析対象のGitHubユーザー名 |
| `--repo` | ✅ | - | リポジトリ (owner/repo形式) |
| `--from` | ✅ | - | 開始日 (YYYY-MM-DD形式) |
| `--to` | ✅ | - | 終了日 (YYYY-MM-DD形式) |
| `--token` | ⚠️ | - | GitHub Personal Access Token (環境変数GITHUB_TOKENでも設定可) |
| `--markdown` | - | - | Markdown出力ファイルのパス |
| `--max-comments` | - | 500 | 取得する最大コメント数 |
| `--types` | - | review_inline,issue | 取得するコメントタイプ (カンマ区切り) |
| `--verbose` | - | false | 詳細出力モード |

### コメントタイプ

- `review_inline`: コードレビューの行ごとコメント
- `issue`: PRの会話コメント

## 出力例

### CLI出力
```
=== Received Review Comments Analysis ===
Total PRs: 2
Total Comments: 5

PR #123: Add new feature
Author: user123 | Created: 2023-01-15 10:30:00
URL: https://github.com/owner/repo/pull/123
Comments:
  [Review Comment] reviewer1 at 2023-01-15 11:00:00: この部分の実装について質問があります...
  [Issue Comment] reviewer2 at 2023-01-15 14:30:00: 全体的に良い実装ですね！
```

### Markdown出力
マークdown形式でテーブル表示され、ファイルとして保存できます。

## 制限事項

- コメント本文は200文字でトリムされます
- Pull Request Reviews のサマリーコメントは除外されます
- PR作成日を基準とした期間フィルタのみサポート
- コメント総数が最大コメント数を超える場合、警告が表示されます

## テスト実行

```bash
composer test
# または
./vendor/bin/phpunit
```

## 終了コード

| コード | 意味 |
|--------|------|
| 0 | 正常終了 |
| 2 | 入力パラメータエラー |
| 5 | GitHubトークン未指定 |
| 6 | 実行時エラー |

## 今後の拡張予定

- コメント日時基準でのフィルタ機能
- 本文長の調整オプション
- 集計・分析機能
- GraphQL APIサポート
- キャッシュ機能
- エラーリトライ機能
- 多言語対応

## ライセンス

MIT License