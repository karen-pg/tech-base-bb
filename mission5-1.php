<?php
// DB接続
$pdo = new PDO(
    'mysql:dbname=データベース名;host=localhost',
    'ユーザー名',
    'パスワード',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING]
);

// テーブル作成
$sql = 'CREATE TABLE IF NOT EXISTS bb
(id INT AUTO_INCREMENT PRIMARY KEY,
dt DATETIME,
name VARCHAR(10),
password VARCHAR(10),
comment TEXT)';
$pdo->query($sql);

// 変数初期化
$editing_id = $name = $disabled = $cmt = null;

// データ挿入
if (!empty($_POST['name']) && !empty($_POST['cmt'])) {
    if (!empty($_POST['editing_id'])) {
        // 編集モード
        $sql = 'UPDATE bb SET dt = NOW(), name = :name, comment = :cmt WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $_POST['name'], PDO::PARAM_STR);
        $stmt->bindParam(':cmt', $_POST['cmt'], PDO::PARAM_STR);
        $stmt->bindParam(':id', $_POST['editing_id'], PDO::PARAM_INT);
        $stmt->execute();
    } else {
        // 挿入モード
        $sql = 'INSERT INTO bb SET dt = NOW(), name = :name, password = :pw, comment = :cmt';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $_POST['name'], PDO::PARAM_STR);
        $stmt->bindParam(':pw', $_POST['pw'], PDO::PARAM_STR);
        $stmt->bindParam(':cmt', $_POST['cmt'], PDO::PARAM_STR);
        $stmt->execute();
    }
}

// 削除
if (!empty($_POST['delete'])) {
    $sql = 'SELECT * FROM bb WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $_POST['delete'], PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch();
    if ($result['password'] == $_POST['delete_pw']) {
        $sql = 'DELETE FROM bb WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $result['id'], PDO::PARAM_INT);
        $stmt->execute();
    }
}

// 編集
if (!empty($_POST['edit'])) {
    $sql = 'SELECT * FROM bb WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $_POST['edit'], PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch();
    if (!empty($_POST['edit_pw'])) {
        if ($result['password'] == $_POST['edit_pw']) {
            $editing_id = $result['id'];
            $name = $result['name'] ;
            $cmt = $result['comment'];
            $disabled = 'disabled';
        }
    }
}

// データ出力
$sql = 'SELECT * FROM bb';
$stmt = $pdo->query($sql);
$results = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>掲示板</title>
    <style>
        body {
            margin: 0;
            display: flex;
            justify-content: space-between;
        }

        .container {
            padding: 20px;
        }

        .sticky {
            width: 440px;
            position: fixed;
            top: 0;
            right: 0;
        }

        hr {
            margin: 0 480px 0 10px;
            height: 100vh;
        }

        form {
            padding: 10px;
            margin-bottom: 30px;
        }

        .form-unit {
            margin-bottom: 10px;
        }

        label {
            width: 100px;
            display: inline-block;
        }

        input {
            width: 300px;
            padding: 3px;

        }

        #editing_id {
            border: 0;
        }

        input[type=submit] {
            display: block;
            width: 100px;
            margin: auto;
        }

        .short-text {
            width: 60px;
        }

        .article {
            margin-bottom: 20px;
        }

        .small-text {
            display: flex;
            margin: 5px;
            font-size: 12px;
        }

        .small-text>p {
            margin: 0 20px 0 0;
        }

        .output-comment {
            margin: 0;
            font-size: 1.1em;
        }
    </style>
</head>

<body>
    <div class="container comments">
        <!-- データ出力 -->
        <?php foreach ($results as $result):
        // if ($result['password']) {
        //     $output_password = "パスワードあり";
        // } else {
        //     $output_password = "";
        // }
        ?>
        <div class=article>
            <div class=small-text>
                <p>ID：<?=htmlspecialchars($result['id'], ENT_QUOTES); ?>
                </p>
                <p><?=htmlspecialchars($result['name'], ENT_QUOTES); ?>
                </p>
                <p><?=htmlspecialchars($result['dt'], ENT_QUOTES); ?>
                </p>
                <p><?=htmlspecialchars($result['password'], ENT_QUOTES); ?>
                </p>
            </div>
            <p class="output-comment"><?=htmlspecialchars($result['comment'], ENT_QUOTES); ?>
            </p>
        </div>
        <?php endforeach; ?>
    </div>
    <hr>
    <div class="container sticky">
        <form action="" method="post">
            <div class="form-unit">
                <?php if (isset($editing_id)): ?>
                <label for="editing_id">編集中 ：</label>
                <input type="text" name="editing_id" id="editing_id" readonly
                    value="<?=$editing_id; ?>">
                <?php endif; ?>
            </div>
            <div class="form-unit">
                <label for="name">ユーザー名</label>
                <input type="text" name="name" id="name"
                    value="<?=$name; ?>">
            </div>
            <div class="form-unit">
                <label for="pw">パスワード</label>
                <input type="text" name="pw" id="pw" <?=$disabled; ?>>
            </div>
            <div class=" form-unit">
                <label for="cmt">コメント</label>
                <input type="text" name="cmt" id="cmt"
                    value="<?=$cmt; ?>">
            </div>
            <input type="submit" name="submit" value="送信">
        </form>
        <form action="" method="post">
            <div class="form-unit">
                <label for="delete">削除番号</label>
                <input type="text" name="delete" id="delete" class="short-text">
            </div>
            <div class="form-unit">
                <label for="delete_pw">パスワード</label>
                <input type="text" name="delete_pw" id="delete_pw">
            </div>
            <input type="submit" name="submit" value="削除">
        </form>
        <form action="" method="post">
            <div class="form-unit">
                <label for="edit">編集番号</label>
                <input type="text" name="edit" id="edit" class="short-text">
            </div>
            <div class="form-unit">
                <label for="edit_pw">パスワード</label>
                <input type="text" name="edit_pw" id="edit_pw">
            </div>
            <input type="submit" name="submit" value="編集">
        </form>
    </div>
</body>

</html>