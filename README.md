# 勤怠管理アプリ

## プロジェクトの概要
このプロジェクトは、Laravelを使用した勤怠管理アプリです。<br />
Dockerを利用した環境構築が可能で、ユーザー登録、出退勤・休憩時間の打刻、
勤怠の閲覧や修正、管理者による修正承認機能を実装しています。


## 環境構築
### Dockerビルド
  1. リポジトリをクローン
``` bash
git clone git@github.com:yun-0312/flea-market.git
```
 2. Dockerコンテナをビルドして起動
``` bash
docker-compose up -d --build
```

### Laravel環境構築
  1. PHPコンテナに入る
``` bash
docker-compose exec php bash
```
  2. 必要な依存関係をインストール
``` bash
composer install
```
  3. 「.env.example」ファイルを 「.env」ファイルに命名を変更。または、新しく.envファイルを作成   
  4. .envファイルの環境変数を以下に修正
``` text
    #　データベース設定
     DB_CONNECTION=mysql
     DB_HOST=mysql
     DB_PORT=3306
     DB_DATABASE=laravel_db
     DB_USERNAME=laravel_user
     DB_PASSWORD=laravel_pass

    # MailHog設定
      MAIL_MAILER=smtp
      MAIL_HOST=mailhog
      MAIL_PORT=1025
      MAIL_USERNAME=null
      MAIL_PASSWORD=null
      MAIL_ENCRYPTION=null
      MAIL_FROM_ADDRESS="noreply@example.com"
      MAIL_FROM_NAME="${APP_NAME}"
```
  5.アプリケーションキーの作成
``` bash
php artisan key:generate
```
  6.マイグレーションの実行
``` bash
php artisan migrate
```
  7.シーディングの実行
``` bash
php artisan db:seed
```

## サンプルユーザーアカウント（動作確認用）
UsersTableSeederで登録されるメール認証済みのテストユーザーです。<br />

### サンプルユーザー（一般）
・ログインURL：http://localhost/login

・鈴木　一郎<br />
Email: user1@test.com<br />
Password: testtest<br />
・田中　花子<br />
Email: user2@test.com<br />
Password: testtest<br />
・木村　次郎<br />
Email: user3@test.com<br />
Password: testtest<br />
・竹田　直美<br />
Email: user4@test.com<br />
Password: testtest<br />
・加藤　三郎<br />
Email: user5@test.com<br />
Password: testtest<br />

### サンプルユーザー（管理者）
・管理者ログインURL：http://localhost/admin/login

・管理者<br />
Email: admin@test.com<br />
Password: testtest<br />


## 使用技術
  <img src="https://img.shields.io/badge/-PHP-777BB4.svg?logo=php&style=plastic"> <img src="https://img.shields.io/badge/-Laravel-E74430.svg?logo=laravel&style=plastic"> <img src="https://img.shields.io/badge/-Composer-885630.svg?logo=composer&style=plastic"> <img src="https://img.shields.io/badge/-Mysql-4479A1.svg?logo=mysql&style=plastic"> <img src="https://img.shields.io/badge/-Nginx-269539.svg?logo=nginx&style=plastic"> <img src="https://img.shields.io/badge/-Docker-1488C6.svg?logo=docker&style=plastic"> <img src="https://img.shields.io/badge/-Stripe-008CDD.svg?logo=stripe&style=plastic"> <br />
  ・php 8.4.12<br />
  ・Laravel 8.83.29<br />
  ・composer 2.8.12<br />
  ・MySQL 8.0.43<br />
  ・nginx 1.21.1<br />
  ・Docker 28.4.0<br />
  ・MailHog（ローカル環境でのメール送信確認）<br />
  ・Stripe（オンライン決済機能）<br />

## テスト
このプロジェクトでは、Laravelのテスト機能を用いてユニットテストおよび機能テストを実装しています。<br />
主に以下の機能に対してテストを行っています。<br />

  1.ユーザー登録認証機能（一般ユーザー）<br />
  2.ログイン認証機能（一般ユーザー）<br />
  3.ログイン認証機能（管理者）<br />
  4.日時取得機能<br />
  5.ステータス確認機能<br />
  6.出勤登録機能<br />
  7.休憩登録機能<br />
  8.退勤登録機能<br />
  9.勤怠一覧情報取得機能（一般ユーザー）<br />
  10.勤怠詳細情報取得機能（一般ユーザー）<br />
  11.勤怠詳細上方修正機能（一般ユーザー）<br />
  12.勤怠一覧情報取得機能（管理者）<br />
  13.勤怠詳細情報取得・修正機能（管理者）<br />
  14.ユーザー情報取得機能（管理者）<br />
  15.勤怠情報修正機能（管理者）<br />
  16.メール認証機能<br />

### テスト環境の設定
テスト実行時には、専用のテスト用データベースを使用します。<br />
 1. MySQLコンテナに入る
``` bash
docker-compose exec mysql bash
```
 2. rootユーザ（管理者)でログインする
``` bash
mysql -u root -p
```
※パスワードは、docker-compose.ymlファイルのMYSQL_ROOT_PASSWORD:に設定されている「root」を入力する。<br />

 3. MySQLログイン後、テスト用データベースを作成
``` bash
CREATE DATABASE demo_test;
```
### テスト環境のアプリケーションキーについて

`.env.testing` にはテスト用のアプリケーションキーが含まれています。
そのまま使用しても問題ありませんが、必要に応じてPHPコンテナ内で以下のコマンドで再生成してください。

``` bash
php artisan key:generate --env=testing
```
### テストの実行方法
このプロジェクトでは、`RefreshDatabase` と `Factory` を使用しており、テスト実行時に自動でマイグレーションとテストデータの生成が行われます。<br />
以下のコマンドでテストを実行してください。

``` bash
docker-compose exec php bash
```
``` bash
php artisan test
```

## ER図
<img width="1191" height="842" alt="Image" src="https://github.com/user-attachments/assets/c80d748e-6b15-426a-942c-12a7881bce53" />

## URL
・開発環境：http://localhost/<br />
・phpMyAdmin：http://localhost:8080/<br />
・MailHog：http://localhost:8025

