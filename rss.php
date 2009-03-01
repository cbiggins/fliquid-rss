<?php
    /**
    * Copyright 2009 Michael Little, Christian Biggins
    *
    * This program is free software: you can redistribute it and/or modify
    * it under the terms of the GNU General Public License as published by
    * the Free Software Foundation, either version 3 of the License, or
    * (at your option) any later version.
    *
    * This program is distributed in the hope that it will be useful,
    * but WITHOUT ANY WARRANTY; without even the implied warranty of
    * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    * GNU General Public License for more details.
    *
    * You should have received a copy of the GNU General Public License
    * along with this program. If not, see <http://www.gnu.org/licenses/>.
    */
    
    /**
    * RSS library. A library for retrieving RSS feeds and returning them as a usable array.
    * ONLY TESTED ON FEEDBURNER SO FAR!
    *
    * Version: 1.0.0 BETA
    * Last Modified: 01/03/2009
    */

    class FliquidRSS {

        public $parser;
        public $xml;
        public $xmlarray = array();
        public $itemcount = 0;
        public $maxitems = 10;

        function __construct($feedurl=null) {
            if (!is_null($feedurl)) {
                $this->newURL($feedurl);
            }
        }

        public function newURL($feedurl) {
            $this->resetRSS();
            $this->xml = file($feedurl);
            $this->xml = implode('', $this->xml);
            xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, FALSE);
            xml_parse_into_struct($this->parser, $this->xml, $this->xmlstruct);

            $this->cleanup();
        }

        public function resetRSS() {
            $this->parser = xml_parser_create('UTF-8');
            $this->itemcount = 0;
            $this->xml = '';
            $this->xmlarray = array();
        }

        public function setMaxResults($max) {
            if (is_int($max)) $this->maxitems = $max;
        }

        public function parseRSS() {

            $inItem = FALSE;

            foreach ($this->xmlstruct as $element) {
                if ($element['tag'] == 'item' && $element['type'] == 'open') { // We have just opened a new tag
                    if ($this->itemcount >= $this->maxitems) break;
                    $inItem = TRUE;
                    $this->itemcount++;
                }
                if ($inItem) {
                    if ($element['type'] == 'complete') {
                        switch ($element['tag']) {
                            case 'title':
                                $this->xmlarray[$this->itemcount]['title'] = $element['value'];
                            break;
                            case 'link':
                                $this->xmlarray[$this->itemcount]['link'] = $element['value'];
                            break;
                            case 'description':
                                $this->xmlarray[$this->itemcount]['description'] = $element['value'];
                            break;
                        }
                    }
                }
                if ($element['tag'] == 'item' && $element['type'] == 'close') { // We have just opened a new tag
                    $inItem = FALSE;
                }
            }
        }

        public function cleanup() {
            xml_parser_free($this->parser);
        }
    }
