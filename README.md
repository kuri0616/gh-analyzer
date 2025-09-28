# GitHub Analyzer

GitHub分析のためのCLIアプリケーションです。GitHubのAPIを使用してリポジトリやユーザーの詳細な分析を行います。

## 特徴

- 📦 **リポジトリ分析**: 詳細なリポジトリ情報の表示
- 👤 **ユーザー分析**: GitHubユーザーのプロフィール情報
- 🔍 **包括的分析**: Issues、PRs、コントリビューター、言語などの詳細分析
- 🐳 **Docker対応**: Docker環境での開発・実行をサポート
- 🎯 **シンプル設計**: 軽量で使いやすいCLIインターフェース

## インストール

### 要件

- PHP 8.2以上
- または Docker

### 1. リポジトリのクローン

```bash
git clone https://github.com/kuri0616/gh-analyzer.git
cd gh-analyzer
```

### 2. 依存関係のインストール（オプション）

```bash
composer install
```

### 3. 実行権限の付与

```bash
chmod +x gh-analyzer
```

## 設定

### GitHub API トークン（推奨）

GitHubのAPIレート制限を回避するため、トークンの設定を推奨します：

1. [GitHub Settings > Personal Access Tokens](https://github.com/settings/tokens) でトークンを作成
2. `.env`ファイルを作成（`.env.example`をコピー）
3. `GITHUB_TOKEN`にトークンを設定

```bash
cp .env.example .env
# .envファイルを編集してGITHUB_TOKENを設定
```

## 使用方法

### 基本的な使用方法

```bash
# ヘルプの表示
./gh-analyzer --help

# バージョン情報
./gh-analyzer --version
```

### リポジトリ情報の取得

```bash
# 基本的なリポジトリ情報
./gh-analyzer repo owner/repository-name

# 例
./gh-analyzer repo octocat/Hello-World
```

### ユーザー情報の取得

```bash
# ユーザー情報
./gh-analyzer user username

# リポジトリ一覧も表示
./gh-analyzer user username --repos

# 例
./gh-analyzer user octocat
```

### 詳細分析

```bash
# 基本分析
./gh-analyzer analyze owner/repository-name

# Issues分析を含む
./gh-analyzer analyze owner/repository-name --issues

# Pull Requests分析を含む
./gh-analyzer analyze owner/repository-name --prs

# コントリビューター分析を含む
./gh-analyzer analyze owner/repository-name --contributors

# 言語分析を含む
./gh-analyzer analyze owner/repository-name --languages

# コミット履歴分析を含む
./gh-analyzer analyze owner/repository-name --commits

# すべての分析を実行
./gh-analyzer analyze owner/repository-name --issues --prs --contributors --languages --commits
```

## Docker での使用

### Docker Compose（推奨）

```bash
# アプリケーションの実行
docker-compose run --rm gh-analyzer repo octocat/Hello-World

# 開発環境（シェルアクセス）
docker-compose run --rm gh-analyzer-dev
```

### 直接 Docker を使用

```bash
# イメージのビルド
docker build -t gh-analyzer .

# 実行
docker run --rm -e GITHUB_TOKEN="your-token" gh-analyzer repo octocat/Hello-World
```

## 開発

### ディレクトリ構造

```
gh-analyzer/
├── app/
│   ├── Commands/           # CLIコマンドクラス
│   ├── Console/           # コンソールアプリケーション
│   └── Services/          # GitHub APIサービス
├── bootstrap/             # ブートストラップファイル
├── config/               # 設定ファイル
├── docker-compose.yml    # Docker Compose設定
├── Dockerfile           # Docker設定
├── gh-analyzer          # メインCLI実行ファイル
└── README.md
```

### 新しいコマンドの追加

1. `app/Commands/` に新しいコマンドクラスを作成
2. `BaseCommand` を継承
3. `app/Console/Application.php` にコマンドを登録

### コントリビューション

1. このリポジトリをフォーク
2. 機能ブランチを作成 (`git checkout -b feature/amazing-feature`)
3. 変更をコミット (`git commit -m 'Add amazing feature'`)
4. ブランチにプッシュ (`git push origin feature/amazing-feature`)
5. Pull Requestを作成

## ライセンス

このプロジェクトはMITライセンスの下で公開されています。詳細は[LICENSE](LICENSE)ファイルを参照してください。

## 例

### リポジトリ分析の例

```bash
./gh-analyzer analyze laravel/laravel --issues --prs --contributors
```

出力例：
```
🔍 Repository Analysis
======================

| Metric           | Value      | Status |
|------------------|------------|--------|
| Repository Name  | laravel/laravel | ✓    |
| Stars           | 75,000     | 🌟     |
| Forks           | 24,000     | 🍴     |
| Open Issues     | 15         | ✓      |
| Has License     | Yes        | ✓      |
| Health Score    | 95%        | 🟢     |

📋 Issues Analysis
==================
Total Issues: 500
Open Issues: 15
Closed Issues: 485
Closure Rate: 97.0%
```

## トラブルシューティング

### API レート制限

GitHub APIは認証なしの場合、1時間あたり60リクエストの制限があります。`GITHUB_TOKEN`を設定することで5000リクエスト/時間まで拡張されます。

### 権限エラー

実行権限を付与してください：
```bash
chmod +x gh-analyzer
```

### Docker 関連

Docker環境でネットワークエラーが発生する場合：
```bash
docker-compose down
docker-compose up --build
```