<?php
include "./lib.php";
$worlds = getdata();
//自动整理
$i = 0;
foreach ($worlds as $world) {
    $worldsn[$i++] = $world;
}
if ($worldsn !== $worlds) {
    putdata($worldsn);
    $worlds = $worldsn;
}

if ($_POST['eng']) { //添加
    $f = 0;
    //ENG CHI STATUS DEL
    for ($i = 0; $i < count($worlds); $i++) {
        if ($worlds[$i][0] == $_POST['eng']) {
            $worlds[$i][1] = $_POST['chn'];
            $worlds[$i][2] = [0, 0, 0, 0];
            $worlds[$i][3] = 1;
            $f = 1;
        }
    }
    if ($f === 0) $worlds[] = array($_POST['eng'], $_POST['chn'], [0, 0, 0, 0], 1);
    putdata($worlds);
    header("Location:/");
}

if ($_GET['setstatus']) { //状态设置
    $eng = $_GET["setstatus"];
    for ($i = 0; $i < count($worlds); $i++) {
        if ($worlds[$i][0] === $eng) {
            $worlds[$i][2][$_GET["stuid"]] = $_GET['status'];
        }
    }
    //var_dump($worlds[55]);
    putdata($worlds);
    //header("Location:/");
    die("DONE");
}

if ($_GET['del']) { //删除
    $eng = $_GET["del"];
    for ($i = 0; $i < count($worlds); $i++) {
        if ($worlds[$i][0] == $eng) {
            $worlds[$i][3] = 0;
        }
    }
    putdata($worlds);
    //header("Location:/");
    die("DONE");
}


if ($_GET['chidisp']) {
    setcookie('CHI', $_GET['chidisp'],);
    //header("Location:/");
    die("DONE");
}
$worlds = array_reverse($worlds);
$chidisp = $_COOKIE['CHI'] === '2' ? true : false;

//筛选器
if ($_GET['check']) { //标记单词检测
    setcookie('filter', 'check',);
    //header("Location:/");
    die("DONE");
}
if ($_GET['unmem']) { //未背诵
    setcookie('filter', 'unmem',);
    //header("Location:/");
    die("DONE");
}
if ($_GET['clearf']) { //清除筛选
    setcookie('filter', '', time() - 3600);
    //header("Location:/");
    die("DONE");
}
switch ($_COOKIE['filter']) {
    case 'check':
        $worlds = array_filter($worlds, function ($v) {
            return $v[3] == 1 && $v[2][3] == 1;
        });
        //随机乱序
        shuffle($worlds);
        break;
    case "unmem":
        $worlds = array_filter($worlds, function ($v) {
            return $v[3] == 1 && $v[2][0] == 0;
        });
        break;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>背个单词</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.staticfile.net/twitter-bootstrap/5.1.1/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.staticfile.net/twitter-bootstrap/5.1.1/js/bootstrap.bundle.min.js"></script>
</head>

<body>

    <div class="container mt-3">
        <form method="post">
            <input name="eng" placeholder="英文单词" id='eng'>
            <input name="chn" placeholder="中文意思" id='chn'>
            <button type="submit" class="btn btn-danger">添加</button>
            <button type="button" onclick="auto();return 0;" class="btn btn-success">自动汉译</button>
        </form>
        <a href="javascript:jump('/?clearf=1')" class="btn btn-info">刷新/首页</a>
        <a href="javascript:jump('/?check=1')" class="btn btn-warning">标记单词检测</a>
        <a href="javascript:jump('/?unmem=1')" class="btn btn-primary">未背诵的</a>
        <table class="table">
            <thead>
                <tr>
                    <th>单词</th>
                    <th>汉意
                        <?php
                        echo
                        $chidisp ?
                            "<a type='button' onclick='jump(\"?chidisp=1\")'>隐藏</a>" :
                            "<a type='button' onclick='jump(\"?chidisp=2\")'>显示</a>" ?>
                    </th>
                    <th>第一次背诵</th>
                    <th>第二次复习</th>
                    <th>第三次复习</th>
                    <th>遗忘标记</th>
                    <th>其他操作</th>
                </tr>
            </thead>
            <tbody>
                <?php
                //输出表格
                for ($i = 0; $i < count($worlds); $i++) {
                    if ($worlds[$i][3] == 1) {
                        echo "<tr>";
                        echo "<td>" . $worlds[$i][0] . "</td>";
                        echo "<td>" . (!$chidisp ? "<span onclick='alert(\"{$worlds[$i][1]}\")'>--</span>" : $worlds[$i][1]) . "</td>";
                        for ($j = 0; $j < 3; $j++) {
                            if ($worlds[$i][2][$j] == 1) {
                                echo "<td class='text-success'><a type='button' onclick='jump(\"?setstatus=" . $worlds[$i][0] . "&stuid=" . $j . "&status=0\")'>√</a></td>";
                            } else {
                                echo "<td class='text-danger'><a type='button' onclick='jump(\"?setstatus=" . $worlds[$i][0] . "&stuid=" . $j . "&status=1\")'>x</a></td>";
                            }
                        }
                        if ($worlds[$i][2][3] == 1) {
                            echo "<td class='bg-warning' onclick='jump(\"?setstatus=" . $worlds[$i][0] . "&stuid=" . $j . "&status=0\")'>⭐</td>";
                        } else {
                            echo "<td class='text-light' onclick='jump(\"?setstatus=" . $worlds[$i][0] . "&stuid=" . $j . "&status=1\")'>---</td>";
                        }
                        echo "<td><a type='button' onclick='jump(\"?del=" . $worlds[$i][0] . "&stuid=" . $i . "\")'>删除</a></td>";
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>

</body>

</html>
<script src="http://apps.bdimg.com/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="./md5.js"></script>
<script>
    function auto() {
        eng = document.getElementById('eng').value;
        if (eng == '') {
            alert('请输入英文单词');
        }
        var appid = '20230128001541935';
        var key = 'nslobMNQzItNWD3RAypZ';
        var salt = (new Date).getTime();
        var query = eng;
        // 多个query可以用\n连接  如 query='apple\norange\nbanana\npear'
        var from = 'en';
        var to = 'zh';
        var str1 = appid + query + salt + key;
        var sign = MD5(str1);
        $.ajax({
            url: 'http://api.fanyi.baidu.com/api/trans/vip/translate',
            type: 'get',
            dataType: 'jsonp',
            data: {
                q: query,
                appid: appid,
                salt: salt,
                from: from,
                to: to,
                sign: sign
            },
            success: function(data) {
                console.log(data);
                document.getElementById('chn').value = data.trans_result[0].dst;
            }
        });
    }

    function jump(url) {
        //记录当前页面滑动位置到COOKIE，同时不影响其它COOKIE
        var scrollPos = document.documentElement.scrollTop || document.body.scrollTop;
        document.cookie = "scrollTop=" + scrollPos; //写入COOKIE
        console.log(document.cookie)
        fetch(url)
            .then(response => response.text())
            .then(data => {
                if(data==="DONE"){
                    window.location.reload()
                }else{
                    alert(data);
                }
            })
    }

    //读取页面滑动


    setTimeout(() => {
        var scrollPos = document.cookie.indexOf("scrollTop");
        document.documentElement.scrollTop = document.body.scrollTop = document.cookie.substring(scrollPos + 10);
    }, 20);
</script>