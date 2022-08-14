<?php

namespace Alkane\HtmlPageLayout;

use \Alkane\AlkaneAPI\AlkaneAPI;

/**
 * Class HtmlPageLayout
 * @category  HTML Page Layout
 * @author    Sadiq <sadiq.com.bd@gmail.com>
 * @copyright Copyright (c) 2022
 * @version   1.0.4
 * @package   Alkane\HtmlPageLayout
 */

 class HtmlPageLayout extends AlkaneAPI {
         
        /**
        * @var string
        */
        private $title = '';
    
        /**
        * @var string
        */
        private $description = '';
    
        /**
        * @var string
        */
        private $keywords = '';
    
        /**
        * @var string
        */
        private $author = '';
    
        /**
        * @var string
        */
        private $robots = '';
    
        /**
        * @var string
        */
        private $favicon = '';

        /**
         * @var bool
         */
        private $is_responsive = true;
    
        /**
        * @var string|array
        */
        private $css;

        /**
         * @var string
         */
        private $custom_style = '';
    
        /**
         * @var string
         */
        private $custom_script = '';

        /**
        * @var string|array
        */
        private $js;
    
        /**
        * @var string
        */
        private $body = '';
    
        /**
        * @var string
        */
        private $footer = '';
    
        /**
        * @var string
        */
        private $header = '';

        /**
         * sets the title
         * @param string $title
         */
        public function setTitle(string $title) {
            $this->title = $title;
        }

        /**
         * sets the description
         * @param string $description
         */
        public function setDescription(string $description) {
            $this->description = $description;
        }

        /**
         * sets the keywords
         * @param string $keywords
         */
        public function setKeywords(string $keywords) {
            $this->keywords = $keywords;
        }

        /**
         * sets the author
         * @param string $author
         */
        public function setAuthor(string $author) {
            $this->author = $author;

        }

        /**
         * sets the robots
         * @param string $robots
         */

        public function setRobots(string $robots) {
            $this->robots = $robots;

        }

        /**
         * sets the favicon
         * @param string $favicon
         */

        public function setFavicon(string $favicon) {
            $this->favicon = $favicon;

        }

        /**
         * Is responsive
         * @param bool $is_responsive
         */
        public function isResponsive(bool $is_responsive) {
            $this->is_responsive = $is_responsive;
        }

        /**
         * sets the css
         * @param string $css
         */

        public function setCss($css) {
            $this->css = $css;
        }

        /**
         * sets the custom_style
         * @param string $custom_style
         */
        public function setCustomStyle(string $custom_style) {
            $this->custom_style = $custom_style;
        }

        /**
         * sets the js
         * @param string $js
         */

        public function setJs($js) {
            $this->js = $js;
        }

        /**
         * sets custom script
         * @param string $custom_script
         */
        public function setCustomScript(string $custom_script) {
            $this->custom_script = $custom_script;
        }

        /**
         * sets the footer
         * @param string $footer
         */

        public function setFooter(string $footer) {
            $this->footer = $footer;

        }

        /**
         * sets the header
         * @param string $header
         */

        public function setHeader(string $header) {
            $this->header = $header;

        }

        /**
         * sets the body
         * @param string $body
         */

        public function setBodyContent(string $body) {
            $this->body = $body;
        }


        /**
         * class constructor
         * @param string $title
         * @param string $description
         * @param string $keywords
         * @param string $author
         * @param string $robots
         * @param string $favicon
         * @return void
         */
        public function __construct($title = '', $description = '', $keywords = '', $author = '', $robots = '', $favicon = '') {
            $this->title = $title;
            $this->description = $description;
            $this->keywords = $keywords;
            $this->author = $author;
            $this->robots = $robots;
            $this->favicon = $favicon;
        }

        /**
         * renders the page header
         */
        public function render() {
            echo $this->defaultPageHeader();    // page header
            if (!empty($this->defaultPageBody())) {
                echo $this->defaultPageBody();   // page body
            }
        }


        /**
         * html page heder
         * @return string
         */

        private function defaultPageHeader() {
           if (!empty($this->header))  {
               return $this->header;
           }
           $header = '';
           $header .= "<!DOCTYPE html>\n";
           $header .= "<html lang=\"en\">\n";
           $header .= "<head>\n";
           $header .= "<meta charset=\"utf-8\">\n";
           $header .= "<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n";
           if ($this->is_responsive) {
               $header .= "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, shrink-to-fit=no\">\n";
           }
           $header .= "<title>{$this->title}</title>\n";
           $header .= "<meta name=\"description\" content=\"{$this->description}\">\n";
           $header .= "<meta name=\"keywords\" content=\"{$this->keywords}\">\n";
           $header .= "<meta name=\"author\" content=\"{$this->author}\">\n";
           $header .= "<meta name=\"robots\" content=\"{$this->robots}\">\n";
           $header .= "<link rel=\"shortcut icon\" href=\"{$this->favicon}\">\n";
           if (!empty($this->css)) {
               if (is_array($this->css)) {
                   foreach ($this->css as $css) {
                       $header .= "<link rel=\"stylesheet\" href=\"{$css}\">\n";
                   }
               } else {
                   $header .= "<link rel=\"stylesheet\" href=\"{$this->css}\">\n";
               }
           }
           if (!empty($this->js)) {
               if (is_array($this->js)) {
                   foreach ($this->js as $js) {
                       $header .= "<script src=\"{$js}\" type=\"text/javascript\"></script>\n";
                   }
               } else {
                   $header .= "<script src=\"{$this->js}\" type=\"text/javascript\"></script>\n";
               }
           }
           if (!empty($this->custom_style)) {
               $header .= "<style>\n";
               $header .= $this->custom_style . "\n";
               $header .= "</style>\n";
           }
           if (!empty($this->custom_script)) {
               $header .= "<script type=\"text/javascript\">\n";
               $header .= $this->custom_script . "\n";
               $header .= "</script>\n";
           }
           $header .= "</head>\n";
           $header .= "<body>\n";
           
           return $header;
        }

        /**
         * html page body
         * @return string
         */
        private function defaultPageBody() {
            return !empty($this->body) ? $this->body : '';
        }

        /**
         * html page footer
         * @return string
         */

        private function defaultPageFooter() {
            if (!empty($this->footer))  {
                return $this->footer;
            }
            $footer = '';
            $footer .= "\n</body>\n";
            $footer .= "</html>\n";

            return $footer;
        }

        /**
         * class destructor
         */
        public function __destruct() {
            // page footer
            echo $this->defaultPageFooter();    
        }
 }

