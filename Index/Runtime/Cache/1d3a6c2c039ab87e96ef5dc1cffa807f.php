<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo C(STR_WEBSITE_TITLE);?></title>
<script type="text/javascript" src="__PUBLIC__/js/jquery-1.11.2.min.js"></script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="__PUBLIC__/css/style.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div id="container">
  <div id="banner">
    <h1><?php echo C(STR_WEBSITE_HEADER);?></h1>
  </div>
  <div id="navbar">
    <ul>
      <li><a href="<?php echo U('/Event/logout');?>"><?php echo C(STR_LOGOUT);?></a></li>
      <li><a href="http://www.mtgjudge.cn/" class="right"><?php echo C(STR_BACK_TO_MAINSITE);?></a></li>
    </ul>
  </div>

  <div class="clear"></div>
  <div id="sidebar">
    <p><?php echo ($_SESSION['fullname']); ?></p>
     <div class="navlist">
      <ul>
        <li><a href="<?php echo U('/Panel/index');?>"><?php echo C(STR_CONTROL_PANEL);?></a></li>
        <li><a href="<?php echo U('/Store/index');?>"><?php echo C(STR_FAVORITE_STORE);?></a></li>
        <li><a href="<?php echo U('/Store/storelist');?>"><?php echo C(STR_STORE_LIST);?></a></li>
        <li><a href="<?php echo U('/Panel/showchecklist');?>"><?php echo C(STR_L2_CHECKLIST_1);?></a></li>
      </ul>
    </div>
    <p><?php echo C(STR_NAVIGATE);?></p>
    <div class="navlist">
      <ul>
        <li><a href="<?php echo U('/Event/index');?>"><?php echo C(STR_EVENT_CALENDAR);?></a></li>
        <li><a href="<?php echo U('/Event/eventlist',Array('judge' => $_SESSION['id']));?>"><?php echo C(STR_MY_EVENTS);?></a></li>
        <li><a href="<?php echo U('/Event/eventlist',Array('judge' => $_SESSION['id'], 'past' => '1'));?>"><?php echo C(STR_MY_PAST_EVENTS);?></a></li>
        <li><a href="<?php echo U('/Event/eventlist');?>"><?php echo C(STR_FUTURE_EVENT);?></a></li>
        <li><a href="<?php echo U('/Event/eventlist',Array('past' => '1'));?>"><?php echo C('STR_PAST_EVENT_SHORT');?></a></li>
        <li><a href="<?php echo U('/Event/championlist');?>"><?php echo C('STR_CHAMPION_LIST');?></a></li>
        <li><a href="<?php echo U('/Event/add');?>"><?php echo C(STR_ADD_EVENT);?></a></li>
        <li><a href="<?php echo U('/Event/import');?>"><?php echo C(STR_EXCEL_IMPORT);?></a></li>
        <li><a href="<?php echo U('/Event/cleanup');?>"><?php echo C(STR_CLEANUP);?></a></li>
      </ul>
    </div>
    <p><?php echo C(STR_USEFUL_LINKS);?></p>
    <div class="navlist">
      <ul>
        <li><a href="<?php echo C(STR_HELP_URL);?>"><?php echo C(STR_HELP);?></a></li>
        <li><a href="<?php echo C(STR_DOCUMENTS_URL);?>"><?php echo C(STR_DOCUMENTS);?></a></li>
        <li><a href="<?php echo C(STR_JUDGEWIKI_URL);?>"><?php echo C(STR_JUDGEWIKI);?></a></li>
        <li><a href="<?php echo C(STR_JUDGEAPPS_URL);?>"><?php echo C(STR_JUDGEAPPS);?></a></li>
        <li><a href="<?php echo C(STR_JUDGECENTER_URL);?>"><?php echo C(STR_JUDGECENTER);?></a></li>
      </ul>
    </div>
    <p>&nbsp;</p>
  </div>
  <div>
    <h2><?php echo C(STR_ANNOUNCEMENT);?>&nbsp;(<?php echo ($announcement['title']); ?>)</h2>
    <p><?php echo ($announcement['content']); ?></p>
  </div>
    <p><div style="text-align: center; font-weight: bold; font-size: 110%;"><?php echo C(STR_EVENT_CALENDAR);?></div></p>

  <?php echo ($calendar); ?>


  
<!--   <div id="bottom">
    <h2>Services</h2>
    <p><img class="imgleft" src="__PUBLIC__/images/orb.png" alt="" />Lorem ipsum dolor sit amet, consectetuer adipiscing elit. </p>
  </div> -->
  <div class="clear"></div>
  <div id="footer">

    <div align="center">
      <p>&copy; <?php echo C(STR_FOOTER_COPY);?> | Powered by <a href="http://wiki.mtgjudge.cn/eventminder:portal#版本历史"><?php echo C(STR_SOFTWARE_TITLE); echo C(STR_SOFTWARE_VERSION);?></a></p>
    </div>
  </div>
</div>
</body>
</html>