<html><head>
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
    var ws, liveId, client_list={};

    // 连接服务端
    function connect() {
       // 创建websocket
        //ws = new WebSocket("ws://180.153.149.248:81/w_sub?group_id=123123");
       ws = new WebSocket("ws://"+document.domain+":7272");
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
        liveId = '<?php echo isset($_GET['liveId']) ? $_GET['liveId'] : 100000000?>';
        // 登录
        var login_data = '{"type":"login","liveId":"'+liveId+'"}';
        console.log("websocket握手成功，发送登录数据:"+login_data);
        ws.send(login_data);
    }

    function addUid(){
      var uid = document.getElementById("bindId").value;
      var upd_data = '{"type":"bindUid","uid":"'+uid+'"}';
      ws.send(upd_data);
    }

    function del(){
      var commentId = document.getElementById("delId").value;
      var send_data = '{"type":"delComment","commentId":"'+commentId+'"}';
      ws.send(send_data);
    }

    function send(){
      var uid = document.getElementById("sendId").value;
      var info = document.getElementById("sendInfo").value;
      var obj = new Object();
      obj.type = 'sendComment';
      obj.uid = uid;
      obj.info = info;
      var send_data = JSON.stringify(obj);
      ws.send(send_data);
    }

    function say(){
        var info = document.getElementById("sayInfo").value;
      var commentId = document.getElementById("sayCommentId").value;
      var liveId = document.getElementById("sayLiveId").value;
      var obj = new Object();
      obj.type = 'say';
      obj.info = info;
      obj.commentId = commentId;
      obj.liveId = liveId;
      var send_data = JSON.stringify(obj);
      ws.send(send_data);
    }

    function adminLogin(){
      var adminUid = document.getElementById("adminUid").value;
      var adminType = document.getElementById("adminType").value;
      var adminLiveId = document.getElementById("adminLiveId").value;
      var send_data = '{"type":"adminLogin","uid":"'+adminUid+'","liveId":"'+adminLiveId+'","adminType":"'+adminType+'"}';
      ws.send(send_data);
    }

    function reply() {
      var commentId = document.getElementById("comId").value;
      var uid = document.getElementById("comUid").value;
      var info = document.getElementById("reInfo").value;
      var nick = document.getElementById("nick").value;
      var send_data = '{"type":"reply","uid":"'+uid+'","info":"'+info+'","nickName":"'+nick+'","commentId":"'+commentId+'"}';
      ws.send(send_data);
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

    function flash(){
        var obj = '<policy-file-request/>';
        ws.send(obj);
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

    function show_prompt(){
        liveId = prompt('输入直播ID：', '');
        if(!liveId || liveId=='null'){
            liveId = '10000000';
        }
    }
  </script>
</head>
<body onload="connect();">
<input type="text" id=in>
<button type="button" onClick="flash()">flash安全测试</button></br>
<input type="text" id=bindId value="user id"><button type="button" onClick="addUid()">绑定UID</button></br>
<input type="text" id=adminUid value="admin user id"><input type="text" id=adminType value="admin type"><input type="text" id=adminLiveId value="adminLiveId"><button type="button" onClick="adminLogin()">管理员登录</button></br>
<input type="text" id=delId value="comment id"><button type="button" onClick="del()">删除评论</button></br>
<input type="text" id=sendId value="send to uid"><input type="text" id=sendInfo value="send comment info"><button type="button" onClick="send()">分发评论</button></br>
<input type="text" id=sayInfo value="comment Info"><input type="text" id=sayCommentId value="comment Id"><input type="text" id=sayLiveId value="live Id"><button type="button" onClick="say()">发表评论</button></br>
<input type="text" id=comId value="comment Id"><input type="text" id=nick value="replyer nickname"><input type="text" id=comUid value="comment User ID"><input type="text" id=reInfo value="reply info"><button type="button" onClick="reply()">回复评论</button></br>

<pre><code id=out></code></pre>
</body>
</html>
