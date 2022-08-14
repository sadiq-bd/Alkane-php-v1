<?php

  // render html page
  $layout = new Alkane\HtmlPageLayout\HtmlPageLayout();
  $layout->setTitle('Developer Sadiq');
  $layout->setDescription('My App Description');
  $layout->setKeywords('My App, Description');
  $layout->setFavicon('resource/images/favicon.ico');
  $layout->setAuthor('Sadiq');
  $layout->setRobots('index, follow');
  
  if (!isset($cssLinks)) $cssLinks = array();
  $layout->setCss(array_merge([
    'https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap'
  ], $cssLinks));

  if (!isset($jsLinks)) $jsLinks = array();
  $layout->setJs(array_merge([
    _BASE_URL_ . 'resource/scripts/functions.js'
  ], $jsLinks));

  $layout->render();


