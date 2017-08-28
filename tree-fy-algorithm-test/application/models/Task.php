<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @author Kamyar
 */

require_once 'application/core/tree-fy-algorithm/Node.php';
require_once 'application/core/tree-fy-algorithm/Tree.php';
require_once dirname(__FILE__) . '/TreeView.php';

class Task extends CI_Model {
    
    /**
     * 
     * @param type $dbc
     * @return int
     * @author Kamyar
     */
    public function checkTaskTable(&$dbc) {
        $obj = [
            "stat"   =>   -1, 
            "msg"    =>   "",
            "data"   =>   [],
            "count"  =>  0
        ];
        
        $sql = "CREATE TABLE IF NOT EXISTS tasks(
                id serial NOT NULL PRIMARY KEY, 
                title VARCHAR(255) NOT NULL, 
                status SMALLINT NOT NULL DEFAULT 0, 
                parent_id INT NOT NULL DEFAULT 0
                );";
        
        $query = $dbc->query($sql);
        
        if($query) {
            $obj["stat"] = 0;
        }
        
        return $obj;
    }
    
    /**
     * 
     * @param type $dbc
     * @return int
     * @author Kamyar
     */
    public function getTasks(&$dbc, &$condition = NULL, &$start = NULL, &$length = NULL, &$columns = NULL, &$order = NULL) {
        $obj = [
            "stat"   =>   -1, 
            "msg"    =>   "",
            "data"   =>   [],
            "count"  =>  0
        ];
        
        if(($condition != NULL && $start != NULL && $length != NULL && $columns != NULL && $order != NULL) || ($start != NULL && $length)) {
            $explicit = [
                "id" => TRUE, 
                "title" => FALSE, 
                "status" => FALSE, 
                "parent_id" => TRUE, 
            ];
            $mask = [
                "status" => "CASE
                                 WHEN status = 0 THEN 'IN PROGRESS'
                                 WHEN status = 1 THEN 'DONE'
                                 WHEN status = 2 THEN 'COMPLETE'
                                 ELSE ''
                             END"
            ];
            $this->_normalise_start($start);
            $this->_translate_order($order);
            $this->_prepare_condition($condition, $mask, $explicit);
            $extended_support = TRUE;
        } else {
            $extended_support = FALSE;
        }
        
        $sql = "SELECT 
                    id, 
                    title, 
                    CASE
                        WHEN status = 0 THEN 'IN PROGRESS'
                        WHEN status = 1 THEN 'DONE'
                        WHEN status = 2 THEN 'COMPLETE'
                        ELSE ''
                    END AS status, 
                    parent_id 
                FROM 
                    tasks 
                WHERE 
                    id > 0" .
                ($extended_support ? "$condition 
                                        ORDER BY $columns $order 
                                        LIMIT $length 
                                        OFFSET $start" 
                : "") . ";";
        
        $query = $dbc->query($sql);
        
        if($query) {
            $obj["data"] = $query->result_array();
            $cnt = $this->getTaskCount($dbc);
            $obj["count"] = ($cnt["stat"] == 0 ? $cnt["data"] : count($obj["data"]));
            $obj["stat"] = 0;
        }
        
        return $obj;
    }
    
    /**
     * 
     * @param type $dbc
     * @return int
     * @author Kamyar
     */
    private function getTaskCount(&$dbc) {
        $obj = [
            "stat"   =>   -1, 
            "msg"    =>   "",
            "data"   =>   [],
            "count"  =>  0
        ];
        
        $sql = "SELECT COUNT(*) AS sum FROM tasks;";
        
        $query = $dbc->query($sql);
        
        if($query) {
            $obj["data"] = $query->row_array()["sum"];
            $obj["stat"] = 0;
        }
        
        return $obj;
    }

    /**
     * 
     * @param type $order
     * @return boolean
     * 
     * @author Kamyar
     * Begin: function
     */
    private function _translate_order(&$order) {
        switch ($order) {
            case 1:
                $order = "ASC";
                break;
            case 2:
                $order = "DESC";
                break;
            default:
                return FALSE;
        }
        return TRUE;
    }
    /*
     * Kamyar
     * End: function
     */
    
    /**
     * 
     * @param type $start
     * 
     * @author Kamyar
     * Begin: function
     */
    private function _normalise_start(&$start) {
        --$start;
    }
    /*
     * Kamyar
     * End: function
     */
    
    /**
     * 
     * @param type $condition
     * @param type $mask
     * @param type $explicit
     * 
     * @author Kamyar
     * Begin: function
     */
    private function _prepare_condition(&$condition, $mask = NULL, $explicit = NULL) {
        $sentence = "";
        foreach($condition as $key => $value) {
            if($value != "" && $value != NULL) {
                if($mask != NULL && array_key_exists($key, $mask)) {
                    $sentence .= " AND {$mask[$key]} " . (!is_null($explicit) && array_key_exists($key, $explicit) ? ($explicit["$key"] ? "= {$value}" : "ilike '%{$value}%'") : "ilike '%{$value}%'");
                } else {
                    $sentence .= " AND {$key} " . (!is_null($explicit) && array_key_exists($key, $explicit) ? ($explicit["$key"] ? "= {$value}" : "ilike '%{$value}%'") : "ilike '%{$value}%'");
                }
            }
        }
        $condition = $sentence;
    }
    /*
     * Kamyar
     * End: function
     */
    
    /**
     * 
     * @param type $dbc
     * @return type
     * @author Kamyar
     */
    public function createHierarchicalVisual(&$dbc) {
        $obj = [
            "stat"      =>      -1, 
            "msg"       =>      "", 
            "int_msg"   =>      "", 
            "data"      =>      [], 
            "count"     =>      0, 
            "view"      =>      ""
        ];

        $data = $this->getTasks($dbc);
        if ($data["stat"] == 0) {
            try {
                $nodes = $this->nodify($data['data']);
                $tree_obj = new Tree($nodes);
//                $tree_obj->processTree($obj["count"], $obj["view"]);
                $tree = $tree_obj->generateTree();
                $treeView = new TreeView($tree);
                $treeView->taskMakeHierarchyView($tree, $obj['view']);
            } catch (Exception $ex) {
                $obj["msg"] = $ex->getMessage();
                $obj["int_msg"] = $ex->getCode();
            } finally {
                $obj["data"] = $data;
                $obj["stat"] = 0;
            }
        } else {
            $obj["msg"] = $data["msg"];
        }

        return $obj;
    }
    
    /**
     * 
     * @param type $dbc
     * @param type $title
     * @param type $parent_id
     * @return int
     * @author Kamyar
     */
    public function saveTask(&$dbc, &$title, &$parent_id) {
        $obj = [
            "stat"   =>   -1, 
            "msg"    =>   "",
            "data"   =>   [],
            "count"  =>  0
        ];
        
        $sql = "INSERT INTO 
                    tasks (
                        title, 
                        parent_id
                    ) 
                VALUES (
                    ?, 
                    ?
                );";
        
        $query = $dbc->query($sql, [
            $title, 
            $parent_id
        ]);
        
        if($query) {
            $obj["data"] = $dbc->insert_id();
            $obj["stat"] = 0;
            $this->reviewStatus($dbc);
        }
        
        return $obj;
    }
    
    /**
     * 
     * @param type $dbc
     * @param type $id
     * @return string
     * @author Kamyar
     */
    public function changeStatus(&$dbc, &$id) {
        $obj = [
            "stat"   =>   -1, 
            "msg"    =>   "",
            "data"   =>   [],
            "count"  =>  0
        ];
        
        $req_sql = "SELECT status FROM tasks WHERE id = ?;";
        
        $req_query = $dbc->query($req_sql, $id);
        
        if($req_query) {
            $status = $req_query->row_array()["status"];
            
            if($status == 0) {
                if($this->toggleStatus($dbc, $id)) {
                    $this->reviewStatus($dbc);
                    $obj["stat"] = 0;
                }
            } else {
                $check_sql = "SELECT COUNT(*) AS sum FROM tasks WHERE parent_id = ?;";
                    
                $check_query = $dbc->query($check_sql, $id);

                if($check_query) {
                    $check = $check_query->row_array()["sum"];

                    if($check == 0) {
                        if($this->toggleStatus($dbc, $id, 0)) {
                            $this->reviewStatus($dbc);
                            $obj["stat"] = 0;
                        }
                    } else {
                        $obj["msg"] = "Parent task's status cannot be reverted.";
                    }
                }
            }
        }
        
        return $obj;
    }
    
    /**
     * 
     * @param type $dbc
     * @param type $id
     * @param type $name
     * @return int
     * @author Kamyar
     */
    public function renameTask(&$dbc, &$id, &$name) {
        $obj = [
            "stat"   =>   -1, 
            "msg"    =>   "",
            "data"   =>   [],
            "count"  =>  0
        ];
        
        $sql = "UPDATE tasks SET title = ? WHERE id = ?;";
        
        $query = $dbc->query($sql, [
            $name, 
            $id
        ]);
        
        if($query) {
            $obj["data"] = [
                "id"    =>  $id, 
                "name"  =>  $name
            ];
            $obj["stat"] = 0;
        }
        
        return $obj;
    }
    
    /**
     * 
     * @param type $dbc
     * @param type $id
     * @param type $parent_id
     * @return string
     * @author Kamyar
     */
    public function changeParent(&$dbc, &$id, &$parent_id) {
        $obj = [
            "stat"   =>   -1, 
            "msg"    =>   "",
            "data"   =>   [],
            "count"  =>  0
        ];
        
        $check_sql = "SELECT COUNT(*) as cnt FROM tasks WHERE id = ?;";
        
        $check_query = $dbc->query($check_sql, [
            $id
        ]);
        
        if($check_query) {
            $check_data = $check_query->row_array()["cnt"];
            if($check_data > 1) {
                $obj["msg"] = "Data integrity is gone.";
            } else {
                if($check_data < 1) {
                    $obj["msg"] = "No such task found.";
                } else {
                    $res = $this->getTasks($dbc);
                    if($res["stat"] == 0) {
                        $data = $res["data"];
                        $nodes = $this->nodify($data);
                        $Tree = new Tree($nodes);
                        $tree = $Tree->generateTree();
                        if($this->checkCirculation($tree, $id, $parent_id)) {
                            $sql = "UPDATE tasks SET parent_id = ? WHERE id = ?;";
                            $query = $dbc->query($sql, [
                                $parent_id,
                                $id
                            ]);
                            if($query) {
                                $obj["data"] = [
                                    "id" => $id,
                                    "parent_id" => $parent_id
                                ];
                                $this->reviewStatus($dbc);
                                $obj["stat"] = 0;
                            }
                        } else {
                            $obj["msg"] = "The new Parent of the task cannot be a child of itself. Otherwise, circulation would happen.";
                        }
                    }
                }
            }
        }
        
        return $obj;
    }
    
    /**
     * 
     * @param type $tree
     * @param type $id
     * @param type $parent_id
     * @return boolean
     * @author Kamyar
     */
    private function checkCirculation(&$tree, &$id, &$parent_id) {
        $rootParent = $this->getParent($tree, $id);
        if(!is_null($rootParent)) {
            return $this->checkChilds($rootParent, $parent_id);
        }
        return FALSE;
    }
    
    /**
     * 
     * @param type $tree
     * @param type $parent_id
     * @return boolean
     * @author Kamyar
     */
    private function checkChilds(&$tree, &$parent_id) {
        foreach($tree as &$node) {
            if($node->id == $parent_id) {
                return FALSE;
            }
            if(count($node->children) > 0) {
                return $this->checkChilds($node->children, $parent_id);
            }
        }
        return TRUE;
    }

    /**
     * 
     * @param type $tree
     * @param type $id
     * @return boolean
     * @author Kamyar
     */
    private function getParent(&$tree, &$id) {
        foreach($tree as $item) {
            if($item->id == $id) {
                return $item;
            }
            if(count($item->children) > 0) {
                return $this->getParent($item->children, $id);
            }
        }
        return FALSE;
    }

    /**
     * 
     * @param type $dbc
     * @param type $id
     * @param type $status
     * @return boolean
     * @author Kamyar
     */
    private function toggleStatus(&$dbc, &$id, $status = 1) {
        $sql = "UPDATE tasks SET status = ? WHERE id = ?;";
        $query = $dbc->query($sql, [
            $status, 
            $id
        ]);
        if($query) {
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * 
     * @param type $dbc
     * @author Kamyar
     */
    private function reviewStatus(&$dbc) {
        $res = $this->getTasks($dbc);
        if($res["stat"] == 0) {
            $data = $res["data"];
            $nodes = $this->nodify($data);
            $Tree = new Tree($nodes);
            $tree = $Tree->generateTree();
            $this->propagateStatus($tree, $dbc);
        }
    }
    
    /**
     * 
     * @param type $tree
     * @param type $dbc
     * @return boolean
     * @author Kamyar
     */
    private function propagateStatus(&$tree, &$dbc) {
        $ok = TRUE;
        foreach($tree as &$node) {
            $good = TRUE;
            if(count($node->children) > 0) {
                $tmp = $this->propagateStatus($node->children, $dbc);
                $good = $tmp;
                if($ok/* || $good*/) {
                    $ok = $tmp;
                }
            }
            if($node->data == "IN PROGRESS") {
                $ok = FALSE;
            }
            $id = $node->id;
            if($ok || $good) {
                if($node->data == "DONE") {
                    $this->completeStatus($dbc, $id);
                }
            } else {
                if(!($ok && $good)) {
                    if($node->data == "COMPLETE") {
                        $this->revertStatus($dbc, $id);
                    }
                }
            }
        }
        return $ok;
    }
    
    /**
     * 
     * @param type $dbc
     * @param type $id
     * @return type
     * @author Kamyar
     */
    private function completeStatus(&$dbc, &$id) {
        return (
                $dbc->query("UPDATE tasks SET status = 2 WHERE id = ?;", $id) 
                ? TRUE 
                : FALSE);
    }
    
    /**
     * 
     * @param type $dbc
     * @param type $id
     * @return type
     * @author Kamyar
     */
    private function revertStatus(&$dbc, &$id) {
        return (
                $dbc->query("UPDATE tasks SET status = 1 WHERE id = ?;", $id) 
                ? TRUE 
                : FALSE);
    }
    
    private function nodify(&$data) {
        $nodes = [];
        foreach ($data as $task) {
            $node = new Node($task['id'], $task['parent_id'], $task['title'], $task['status']);
            $nodes[] = $node;
        }
        return $nodes;
    }
    
    
}
