<?php
  define(MAX_SITE, "20");
  define(SITE_NUM, 2); //n*10が取得サイト数

  $date       = new Datetime();
  $dtYmd      = $date->format("Y-m-d");
  $dtHis      = $date->format("Y-m-d H:i:s");
