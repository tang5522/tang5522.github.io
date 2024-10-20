<?php 
/**
 * Template name: 专题
 * Description:   A zhuanti page
 */
 ?>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta
      name="viewport"
      content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=0"
    />
    <title>&#x81ea;&#x5df1;&#x62cd;&#x7684;&#x7167;&#x7247;</title>
    <link rel="stylesheet" href="wp-content/csszz/style.min.css" />
    <link rel="stylesheet" href="https://lf6-cdn-tos.bytecdntp.com/cdn/expire-1-M/fancybox/3.5.7/jquery.fancybox.min.css" />
  </head>
  <body>
    <!-- 主体 -->
    <div id="app">
      <div id="title">
        <h1 <div class="tit">&#x70b9;&#x51fb;&#x56fe;&#x7247;&#x5373;&#x53ef;&#x8fdb;&#x5165;&#x6d4f;&#x89c8;&#x6a21;&#x5f0f;</h1>
        <div class="tool-tbshow">

        </div>

        <div>
          <!-- 弹窗 -->
          <div id="str" style="display: none">
            <div class="str_mark">
              <div class="str_title">自定义获取图片数量</div>
              <strong class="off">X</strong>
              <input
                type="number"
                value=""
                placeholder="请输入获取的数量！"
                oninput="if(value.length>3)value=value.slice(0,3)"
                onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^0-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"
                onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^0-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"
              />
              <button class="btn" onclick="btn()">获 取</button>
              <button
                class="btn"
                onclick="clears ()"
                style="background-color: #dc143c"
              >
                清空
              </button>
              <div id="num_button">
                <button class="button_1" name="1">1张</button>
                <button class="button_5" name="5">5张</button>
                <button class="button_10" name="10">10张</button>
                <button class="button_20" name="20">20张</button>
              </div>
              <div id="diong">你还没有输入获取的数量噢!</div>
              <div id="ok_diong">获取成功！</div>
            </div>
          </div>
        </div>
        <!-- 按钮 -->
        <div>
          <button ><a href="/">首页</a> </button>
          <button id="next" style="background-color: #ff4500">换一批</button>
         
          <button id="img_num" onclick="img_num()" style="display: none">
            当前图片数量
          </button>
          <button
            class="btn Strat_img"
            disabled
            style="
              background-color: #a0cfff;
              border-color: #a0cfff;
              cursor: not-allowed;
            "
          >
            获取
          </button>
          <button
            class="btn Stp"
            style="
              color: #fff;
              background-color: #a0cfff;
              border-color: #a0cfff;
            "
          >
            停止
          </button>
        </div>


        <!-- 侧边按钮 -->
        <div id="app_btn">
          <a class="close" href="#">去顶部</a>
        </div>
        <div id="loadmore">图片努力加载中……</div>
      </div>
      <!-- 图片列表 -->
      <div id="data_div" style="display: none">
        <h1 class="title">图片列表</h1>
        <h2 style="color: #fff; font-size: 14px">
          当没有新的数据时，需要开启采集才会自动开始获取噢！
        </h2>
        <button
          onclick="btn_list()"
          style="background-color: deepskyblue; margin: 10px; padding: 0 12px"
        >
          获取图片列表
        </button>
        <button
          onclick="btn_on()"
          style="
            background-color: rgb(14 80 239);
            margin: 10px;
            padding: 0 12px;
          "
        >
          开启自动获取
        </button>
        <!-- <button
          onclick="btn_off()"
          style="
            background-color: rgb(255, 0, 76);
            margin: 10px;
            padding: 0 12px;
          "
        >
          关闭自动获取
        </button> -->
        <div id="list_img"></div>
      </div>

      <!-- end -->
      <div id="content"><div id="walBox"></div></div>
    </div>
  </body>
  <!-- 引入依赖文件 -->
  <script src="https://lf26-cdn-tos.bytecdntp.com/cdn/expire-1-M/jquery/3.6.0/jquery.min.js"></script>
  <script src="wp-content/csszz/lazyload.min.js"></script>
  <script src="https://lf3-cdn-tos.bytecdntp.com/cdn/expire-1-M/fancybox/3.5.7/jquery.fancybox.min.js"></script>
  <script src="wp-content/csszz/index.js"></script>
</html>



