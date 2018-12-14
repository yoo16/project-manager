<< comaneci >>

- 環境設定
- 作成方法
- ブラウザ確認方法 
- パブリッシュ 

■環境設定
- ドキュメントルートのURL設定（OSX）
  /Users/telepath/Sites/をドキュメントルートとし、Webからアクセスできるように設定する

  http://localhost/telepath/プロジェクト名/
  ※comaneciではhttp://localhost/~telepath/は利用しない

  1) Webルートから/Users/telepath/Sites/telepathシンボリックリンク作成する

    cd /Library/WebServer/Documents/
    sudo ln -s /Users/telepath/Sites/ telepath

- php.ini設定
  1) php.ini.defaultをphp.iniへコピーする

    cp /etc/php.ini.default /etc/php.ini
    sudo vi /etc/php.ini

  2) comaneciが動作するよう、以下の設定をする

    short_open_tag = Off
    asp_tags = On

- httpd.conf設定
  1) .htaccessを有効にする
    sudo vi /etc/httpd/httpd.conf

      <directory Library/WebServer/Documents>
      ・・・略・・・
         AllowOveride All

- PC環境設定ファイル
  1) app/settings/に自分のPC用設定ファイルを作成する

    - ファイル名：ホスト名.php 
    MacG5.local.php
    ※OSXの場合、システム環境設定＞共有＞コンピュータ名で設定を確認する 

    - 記述内容 

      <?php
      error_reporting(E_ALL ^ E_NOTICE);
      // Basic
      ini_set('include_path', get_include_path()
         .PATH_SEPARATOR.'/usr/share/php');
      define('PROJECT_DIR', BASE_DIR);
      define('BASE_URL', 'http://localhost/telepath/プロジェクト名/');
      define('TMP_DIR', BASE_DIR . 'tmp/');
      ?>

    ※BASE_DIRにプロジェクトのURLを記述

  - 動作確認
     ブラウザで「http://localhost/telepath/プロジェクト名/」にアクセスし、
     ページが表示されるのを確認する
    この時プロジェクトルートに「HOSTNAME」ファイルが自動作成されるが、 
    「cannot find setting」とエラー表示される場合は、以下を確認

    + PCのホスト名とホスト名.phpの名前が一致しているか
    + HOSTNAMEの内容とホスト名.phpの名前が一致しているか？ 
    + HOSTNAMEを消して再度確認してみる

■作成方法
  1) 静的なコンテンツの場合はrootをベースに作成する

    - ページ制御プログラム 
    app/controllers/RootController.php

    - 共通ページ 
    app/view/layout/root.phtml

    - 各パーツHTML(xxxx.phtml)の格納ディレクトリ
    app/view/root/

  1) コントローラーに各パーツHTMLの設定追加

  app/controllers/RootController.php 

    var $static_pages = array('index', 'page1', 'page2', 'page3');

  ※この場合index.phtml、page1.phtml、page2.phtml、page2.phtmlが利用可能となる

  2) 共通レイアウトHTML(root.phtml)

  app/view/layout/root.phtml

    <head>
      <base href="<?=$this->base?>" />
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <meta http-equiv="Content-Script-Type" content="text/javascript" />

    ※root.phtmlに必ず<base>タグを<head>の先頭につける

  3) 他のページにリンクする場合は「url_for」を利用する

  例) 同じレイアウトのpage1

    <?=url_for($this, 'page1')?>

  例) sampleレイアウトのindex.htmlにリンク

    <?=url_for($this, 'sample/index')?>

  4) パーツHTMLの読み込みタグを記述

  root.phtmlに$templateをインクルードすると、手順1で設定したapp/view/root/のレイアウトが読み込まれる

    <?include $template?>

  5) パーツHTMLの作成 app/view/root/内に順1で設定したHTMLファイルを作成する

  6) 画像やCSS等public内に入れる

■ブラウザ確認方法

  - OSX
  http://localhost/telepath/プロジェクト名/root/xxxx

  ※xxxxは手順1で設定した値
  ※rootレイアウトの場合

■パブリッシュ
  PHPスクリプトで、静的HTMLの作成、消去、圧縮が実行可能

    - 作成
      php script/publish_html

    - 消去 
      php script/clear_html
    
    - ZIP圧縮 
      php script/archive_html

  HTMLファイルはpublicに書き出される

vim:set sw=2 sts=2 ft=memo:
