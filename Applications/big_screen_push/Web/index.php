<html xmlns="http://www.w3.org/1999/html"><head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>socket样例</title>
  <link href="/css/bootstrap.min.css" rel="stylesheet">
  <link href="/css/style.css" rel="stylesheet">
	
  <script type="text/javascript" src="/js/swfobject.js"></script>
  <script type="text/javascript" src="/js/web_socket.js"></script>
  <script type="text/javascript" src="/js/jquery.min.js"></script>
    <script type="text/javascript" src="/js/json2.js"></script>

  <script type="text/javascript">
    if (typeof console == "undefined") {    this.console = { log: function (msg) {  } };}
    // 如果浏览器不支持websocket，会使用这个flash自动模拟websocket协议，此过程对开发者透明
    WEB_SOCKET_SWF_LOCATION = "/swf/WebSocketMain.swf";
    // 开启flash的websocket debug
    WEB_SOCKET_DEBUG = true;
    var ws, terId, client_list={};

    // 连接服务端
    function connect() {
       // 创建websocket
       ws = new WebSocket("ws://180.168.69.13:8050/b_sub");
       // ws = new WebSocket("ws://"+document.domain+":7272?group_id=123123");
       // 当socket连接打开时，输入用户名
       ws.onopen = onopen;
       // 当有消息时根据消息类型显示不同信息
       ws.onmessage = onmessage; 
       ws.onclose = function() {
    	  console.log("连接关闭，定时重连");
          connect();
       };
       ws.onerror = function() {
     	  console.log("出现错误");
       };
    }

    // 连接建立时发送登录信息
    function onopen()
    {
      terId = '<?php echo isset($_GET['terId']) ? $_GET['terId'] : "100000000"?>';
        // 登录
        var login_data = '{"type":"login","terId":"'+terId+'"}';
        console.log("websocket握手成功，发送登录数据:"+login_data);
        ws.send(login_data);
    }

    // 服务端发来消息时
    function onmessage(e)
    {
        console.log(e.data);
        var data = JSON.parse(e.data);

        switch(data['type']){
            // 服务端ping客户端
            case 'ping':
                ws.send('{"type":"pong"}');
                break;
            // 发言
            case 'push':
                alert(1);
                break;

        }
        switch (data['cmd']){
          case 'play':
            document.getElementById('out').innerText += data['content'] +"\n";
            break;
        }
    }

    function news() {
        var obj = new Object();
        obj.cmd = "news";
        obj.content = '[{"title":"标题1","content":"内容1","time":"5"},{"title":"标题2","content":"内容2","time":"4"},{"title":"标题3","content":"内容3","time":"6"}]';
        var json = JSON.stringify(obj);
        var sendObj = new Object();
        sendObj.type = "push";
        sendObj.info = json;
        var sendJson = JSON.stringify(sendObj);
        ws.send(sendJson);
    }

    function live_ad(url) {
        var obj = new Object();
        obj.cmd = "live";
      var ad_url = document.getElementById('url').value;
      var area = document.getElementById('area').value;
      obj.content = '{"type":"start","url":"'+ad_url+'","area":"'+area+'"}';
        var json = JSON.stringify(obj);
        var sendObj = new Object();
        sendObj.type = "push";
        sendObj.info = json;
        var sendJson = JSON.stringify(sendObj);
        ws.send(sendJson);
    }
    function live_stop(){
      var obj = new Object();
      obj.cmd = "live";
      obj.content = '{"type":"end"}';
      var json = JSON.stringify(obj);
      var sendObj = new Object();
      sendObj.type = "push";
      sendObj.info = json;
      var sendJson = JSON.stringify(sendObj);
      ws.send(sendJson);
    }



    function update_area(area){
      var obj = new Object();
      obj.cmd = "update";
      obj.content = document.getElementById("u_area").value;
      var json = JSON.stringify(obj);
      var sendObj = new Object();
      sendObj.type = "push";
      sendObj.info = json;
      var sendJson = JSON.stringify(sendObj);
      ws.send(sendJson);
    }

    function change_area(area){
      var obj = new Object();
      obj.cmd = "change";
      obj.content = document.getElementById("c_area").value;
      var json = JSON.stringify(obj);
      var sendObj = new Object();
      sendObj.type = "push";
      sendObj.info = json;
      var sendJson = JSON.stringify(sendObj);
      ws.send(sendJson);
    }

    function upd_qr() {
        var url = document.getElementById("qr").value;
        var obj = new Object();
        obj.cmd = "qr";
        obj.content = '{"url":"'+url+'"}';
        var json = JSON.stringify(obj);
        var sendObj = new Object();
        sendObj.type = "push";
        sendObj.info = json;
        var sendJson = JSON.stringify(sendObj);
        ws.send(sendJson);
    }
    function clickPhoto() {
      var obj = new Object();
      obj.cmd = "photo";
      obj.content = '{"type":"click"}';
      var json = JSON.stringify(obj);
      var sendObj = new Object();
      sendObj.type = "push";
      sendObj.info = json;
      var sendJson = JSON.stringify(sendObj);
      ws.send(sendJson);
    }
    function openPhoto() {
      var obj = new Object();
      obj.cmd = "photo";
      obj.content = '{"type":"open"}';
      var json = JSON.stringify(obj);
      var sendObj = new Object();
      sendObj.type = "push";
      sendObj.info = json;
      var sendJson = JSON.stringify(sendObj);
      ws.send(sendJson);
    }

    function okPhoto() {
      var obj = new Object();
      obj.cmd = "photo";
      obj.content = '{"type":"ok","open_id":"123456","nickName":"傅尼玛","headUrl":"12312312"}';
      var json = JSON.stringify(obj);
      var sendObj = new Object();
      sendObj.type = "push";
      sendObj.info = json;
      var sendJson = JSON.stringify(sendObj);
      ws.send(sendJson);
    }
    function canclePhoto() {
      var obj = new Object();
      obj.cmd = "photo";
      obj.content = '{"type":"cancle"}';
      var json = JSON.stringify(obj);
      var sendObj = new Object();
      sendObj.type = "push";
      sendObj.info = json;
      var sendJson = JSON.stringify(sendObj);
      ws.send(sendJson);
    }
    function del(){
      var obj = new Object();
      obj.cmd ="photo";
      obj.content = '{"type":"del"}';
      var json = JSON.stringify(obj);
      var sendObj = new Object();
      sendObj.type = "push";
      sendObj.info = json;
      var sendJson = JSON.stringify(sendObj);
      ws.send(sendJson);
    }

    function add() {
      var obj = new Object();
      obj.cmd = "photo";
      obj.content = '{"type":"add"}';
      var json = JSON.stringify(obj);
      var sendObj = new Object();
      sendObj.type = "push";
      sendObj.info = json;
      var sendJson = JSON.stringify(sendObj);
      ws.send(sendJson);
    }


    // 提交对话
    function onSubmit() {
      var input = document.getElementById("textarea");
      var to_client_id = $("#client_list option:selected").attr("value");
      var to_client_name = $("#client_list option:selected").text();
      ws.send('{"type":"say","to_client_id":"'+to_client_id+'","to_client_name":"'+to_client_name+'","content":"'+input.value.replace(/"/g, '\\"').replace(/\n/g,'\\n').replace(/\r/g, '\\r')+'"}');
      input.value = "";
      input.focus();
    }

  </script>
</head>
<body onload="connect();">
标题：<input type="text" id=title> 内容：<input type="text" id=info><button type="button" onClick="news()">新闻推送</button> </br>
 URL：<input type="text" id=url>区域：<input type="text" id=area><button type="button" onClick="live_ad()">直播推送</button> </br>
 <button type="button" onClick="live_stop()">直播停止</button> </br>
<input type="text" id=u_area value="A"><button type="button" onClick="update_area()">区域更新</button> </br>
<input type="text" id=c_area value="A"><button type="button" onClick="change_area()">区域切换</button> </br>
<input type="text" id=qr value="http://www.baidu.com"><button type="button" onClick="upd_qr()">二维码更新</button> </br>
<button type="button" onClick="add()">摄像头+</button>
<button type="button" onClick="del()">摄像头-</button>
<button type="button" onClick="openPhoto()">开启摄像头</button>
<button type="button" onClick="clickPhoto()">拍照</button>
<button type="button" onClick="okPhoto()">确认上传</button>
<button type="button" onClick="canclePhoto()">取消上传</button>
<pre><code id=out></code></pre>
</body>
</html>
