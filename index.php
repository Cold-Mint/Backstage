<html>
<body>
<script>
    function initDataBase() {
        const result = confirm("初始化数据库之前，请现在conf.php内配置服务器信息。否则可能初始化失败。");
        if (result == true) {
            window.open("/php/dataBase.php?action=initDataBase");
        }
    }

    function manageDatabase() {
        window.open("http://localhost/phpmyadmin/")
    }
</script>
<button onclick="initDataBase()">初始化数据库</button>
<button onclick="manageDatabase()">管理数据库(如果可用)</button>
</body>

</html>