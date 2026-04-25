<?php
class PluginAutosaveCleaner extends Plugin {

    // プラグインのためのDB基本設定（今後、管理画面作成の場合などで利用）
    // Basic Database Settings for Plugins (to be used when creating an admin panel, etc.)
    public function init() {
        $this->dbFields = array();
    }

    // 管理パネルが読み込まれたタイミングで実行されるフック
    // A hook that runs when the admin panel loads
    public function afterAdminLoad() {

    // グローバルページ管理オブジェクトおよび言語オブジェクトを呼び出す
    // Call the Global Page Management Object and the Language Object
        global $pages;
        global $L;

    // このセッションですでに削除済みの場合はスキップ（負荷軽減と通知の重複防止）
    // Skip if already checked within this session (to reduce load and prevent duplicate notifications)
        if (Session::get('autosave_checked')) {
            return;
        }

        $count = 0;

    // ログインステータスをチェック
    // Check login status
        $login = new Login();
        if ($login->isLogged()) {

    // ユーザーが「admin」（管理者）権限を持っている場合のみ実行
    // Execute only if the user has 'admin' (administrator) privileges
            $username = Session::get('username');
            $user = new User($username);
            $role = $user->role();

            if ($role === 'admin') {

    // データベースエントリおよび関連フォルダを削除
    // Deleting Folders and Database Entries
                $pageKeys = array_keys($pages->db);
                foreach ($pageKeys as $key) {
    // 1. キーが autosave- で始まるかチェック
    // 1. Check if the key starts with 'autosave-'
                    if (strpos($key, 'autosave-') === 0) {
    // 2. データベースの生データを直接参照して autosave タイプであることをチェック
    // 2. Check whether it is an 'autosave' type by directly referencing the raw data in the database
                        if (isset($pages->db[$key]['type']) && $pages->db[$key]['type'] === 'autosave') {
                            if ($pages->delete($key)) {
                                $count++;
                            }
                        }
                    }
                }

    // 削除されたデータがある場合のみ通知を表示
    // Display a notification only if there is deleted data
                if ($count > 0) {
                    // Alert::set($count . $this->activate('ac-alert-count'));
                    $alertmessage = sprintf($L->get('ac-alert-count'), $count);
                    Alert::set($alertmessage);
                }

    // チェック済みフラグを立てる
    // Set the 'Checked' flag
                Session::set('autosave_checked', true);
            }
        }
    }
}
