<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class TreeView {
    private $tree;
    
    public function __construct(&$tree) {
        $this->tree = $tree;
    }

    public function taskMakeHierarchyView(&$nodes, &$view, $level = 0) {
        $view .= "<ul>";
        for ($i = 0; $i < count($nodes); ++$i) {
            $class = $level == 0 ? "root" : "child";
            $view .= $level == 0 ? "<div class='row' style='margin: 32px 0;'>" : "";
            $view .= "<li class='" . $class . "'>";
            $view .= "<a style='text-transform: uppercase; color: black; height: 50px !important;" . (($nodes[$i]->data == "IN PROGRESS") ? "background: red !important;" : (($nodes[$i]->data == "DONE") ? "background: blue !important;" : (($nodes[$i]->data == "COMPLETE") ? "background: green !important;" : ""))) . "'>";
//            $view .= "<img src='" . $img . "' style='align: top; padding: 3px;'><br>";
            $view .= "<p style='font-weight: bold; font-size: small; '>{$this->taskNameCutter($nodes[$i]->name, 100)}</p>";
            $view .= "<p style='font-weight: normal; font-size: 12px !important; width: 85px;'>{$this->taskNameCutter($nodes[$i]->data, 100)}</p>";
            $view .= "<p style='clear: both;'></p>";
            $view .= "</a>";
            if (count($nodes[$i]->children) != 0) {
                $this->taskMakeHierarchyView($nodes[$i]->children, $view, $level + 1);
            }
            $view .= "</li>";
            $view .= $level == 0 ? "</div>" : "";
        }
        $view .= "</ul>";
    }
    
    private function taskNameCutter(&$name, $charNo) {
        $len = 0;
        try {
            if(mb_strlen($name, "utf-8") == strlen($name)) {
                $len = mb_strlen($name, mb_internal_encoding());
            }
        } finally {
            if ($len <= $charNo) {
                return $name;
            }
            $result = "";
            for ($i = 0; $i < $len; ++$i) {
                $result .= $name[$i];
                if ($i >= $charNo) {
                    break;
                }
            }
            $result .= " ...";
        }
        return $result;
    }
}