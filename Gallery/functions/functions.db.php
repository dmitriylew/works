<?php
function db_connect(){
    mysql_pconnect(DB_HOST, DB_USER, DB_PASSWORD);
    mysql_select_db(DB_NAME);
    db_query("set names 'cp1251'"); 
    
}

function db_disconnect(){
    mysql_close();
}

function db_query($query){
    $query = str_replace('{#DB_PF#}', DB_PF, $query);
    if(defined("DB_ENGINE") && DB_ENGINE == 'MyISAM'){
        if(preg_match('/^\s*delete\s+from\s+([\w\d]+)\s*(using\s+([\w\d,\s]+)\s+)?(where\s+(.*))?$/si', $query, $save)){
            $tbl = $save[1];
            if( !empty($save[5]) ){
                $str_where = $save[5];
                $str_where = preg_replace('/order\s+by(.+)$/si', '', $str_where);
                
                if( !empty($save[2]) ){
                    $using_tbl = $save[3];
                }
                else{
                    $str_where = preg_replace_callback(
                        '/(or\s+|and\s+|^\s*)([\(]*)\s*([\w\d]+)/si', 
                        create_function('$m', 'return "{$m[1]} {$m[2]} '.$tbl.'.{$m[3]}";'),
                        $str_where
                    );
                }
            }
            else{
                $str_where = '';
                $using_tbl = '';
            }
            
            $rows = db_get_fk($tbl);
            foreach($rows as $fk){
                if($using_tbl)
                    $sql = "delete from {$fk->tbl} using {$fk->tbl}, {$using_tbl} where {$fk->tbl}.{$fk->fld} = {$fk->ref_tbl}.{$fk->ref_fld}";
                else
                    $sql = "delete from {$fk->tbl} using {$fk->tbl}, {$fk->ref_tbl} where {$fk->tbl}.{$fk->fld} = {$fk->ref_tbl}.{$fk->ref_fld}";
                if($str_where) $sql .= " and ({$str_where}) ";
                db_query($sql);
            }
        }
    }
    if($query){
        return mysql_query($query);
    }
    else
        return false;
}

function db_escape($data){
    return mysql_real_escape_string($data);
}

function db_insert_id(){
    return mysql_insert_id();
}

function db_row($query){
    $result = db_query($query);
    if(is_resource($result)) return mysql_fetch_object($result);
    else return (object)array();
}

function db_row_array($query){
    $result = db_query($query);
    if(is_resource($result)) return mysql_fetch_array($result);
    else return array();
}

function db_rows($query){
    $rows = array();
    $result = db_query($query);
    if(is_resource($result)){
        while($row = mysql_fetch_object($result)) $rows[] = $row;
    }
    return $rows;
}

function db_insert($table, $param, $is_replace = false){
    $is_set_a = false;
    $rows = db_rows("desc {$table}");
    $arFields = array();
    foreach($rows as $item){
        if(!isset($param[$item->Field]) && !$is_set_a) continue;
        
        $arFields[] = "`{$item->Field}`";
        
        if(($item->Extra == 'auto_increment')){
            $arValues[] = "NULL";
        }
        else if(!empty($param[$item->Field])){
            $v = $param[$item->Field];
            if(preg_match('/^\s*f:/i', $v, $save)){
                $arValues[] = preg_replace('/^\s*f:/i', '', $v);
            }
            else{
                $arValues[] = "'".db_escape($v)."'";
            }
        }
        else if($item->Default)
            $arValues[] = "'".$item->Default."'";
        else
            $arValues[] = "''";
    }
    if(count($arFields)){
        
        $sql = ($is_replace?"REPLACE":"INSERT")." INTO {$table}(".join(", ", $arFields).") VALUES(".join(", ", $arValues).")";
        /*
         if($table != 'company2category'){ 
        $sql = ($is_replace?"REPLACE":"INSERT")." INTO {$table}(".join(", ", $arFields).") VALUES(".join(", ", $arValues).")";
         } else {        
        $sql = "INSERT"." INTO {$table}(".join(", ", $arFields).") VALUES(".join(", ", $arValues).")";
         }
         */
    //     echo $sql;
       
         
        if($sql) db_query($sql);
    }
    else return false;
      //exit(); 
}

function db_update(){
    $args = func_get_args();
    if(count($args) == 2 && $args[1] === true){
        $where = $args[0];
        $SQL = array();
        foreach($where as $k => $v){
            if(intval($k) !== $k && !is_array($v)){
                if(preg_match('/^\s*([=><]*)\s*([a-z]+:)?/i', $v, $save)){
                    $zn = $save[1]? $save[1] : '=';
                    $isf = isset($save[2])? strtoupper($save[2]) : false;
                }
                else{
                    $zn = '=';
                    $isf = false;
                }
                
                $v = preg_replace('/^\s*[=><]*\s*([a-z]+:)?/i', '', $v);
                
                if($isf == 'F:'){                    
                    if(count(explode('.',$v)))
                        $v = '`'.join('`.`', preg_split('/[\s\.]+/i', $v) ).'`';
                }
                else if($isf == 'LIKE:'){
                    $zn = ' LIKE ';
                    $v = "'".db_escape($v)."'";
                }
                else
                    $v = "'".db_escape($v)."'";
                
                $k = '`'.join('`.`', preg_split('/[\s\.]+/i', $k) ).'`';
                $SQL[] = "{$k} {$zn} {$v}";
            }
            else if(intval($k) === $k && !is_array($v)) $SQL[] = "{$v}";
            else if(intval($k) === $k && is_array($v)){
                $q = db_update($v, true);
                $SQL[] = count($v)==1?"{$q} ":" ({$q})";
            }
        }
        return join(' ', $SQL);
    }
    else{
        list($table, $param, $where) = $args;
        $au = array(); 
        $rows = db_rows("desc {$table}");
        
        foreach($rows as $item){
            $name = $item->Field;
            if(isset($param[$name])){
                $v = $param[$name];
                if(preg_match('/^\s*f:/i', $v, $save)){
                    $v = preg_replace('/^\s*f:/i', '', $v);
                    $au[] = "`".$name."`=".$v."";
                }
                else{
                    $au[] = "`".$name."`='".db_escape($v)."'";
                }
            }
        }
        
        $set = (!empty($au))? ' SET '.join(", ", $au) : '';
        
        if(empty($set)) return '';
        else{
            $str_where = db_update($where, true);
            $sql = "UPDATE {$table} {$set} ".($str_where?"where {$str_where}" : "");
            db_query($sql);
        }
    }
}

function db_sql_select($param = array()){
    #$param['table'] = 'table as t' || array();
    #$param['table_left_join'] = array( array('table', 'table2 as t2', 'on where') );
    #$param['field_left_join'] = array( 't2.section' );
    #$param['field_table'] = array();
    #$param['where'] = array();
    
    $table_from = array();
    $regexp_alias = '';
    
    $table_left_join = array();
    if( isset($param['table_left_join'][0][0]) && is_array($param['table_left_join'][0]) ){
        foreach($param['table_left_join'] as $t_lj){
            $t = array_shift($t_lj);
            $t = str_replace('{#DB_PF#}', DB_PF, $t);
            if( empty($table_left_join[$t]) ){
                $table_left_join[$t] = array();
            }
            $table_left_join[$t][] = $t_lj;
        }
    }
    else if(!empty($param['table_left_join'])){
        $t = array_shift($param['table_left_join']);
        $t = str_replace('{#DB_PF#}', DB_PF, $t);
        $table_left_join[$t][] = $param['table_left_join'];
    }
    else{
        $table_left_join = array();
    }
            
    if(is_array($param['table'])) $table = $param['table'];
    else $table = array($param['table']);
    
    foreach($table as $t){
        $t = str_replace('{#DB_PF#}', DB_PF, $t);
        $table_from[] = count($table_from)? ", {$t} " : " {$t} ";
        if( preg_match('/^\s*([a-z0-9_]+)\s+(as)?\s+([a-z0-9_]+)\s*$/i', $t, $save) ){
            $table_name = $save[1];
            if(isset($table_left_join[$table_name])){
                foreach($table_left_join[$table_name] as $lj)
                    $table_from[] = " left join {$lj[0]} on ( {$lj[1]} ) ";
            }
        }
    }
    
    if(empty($param['field_table']))
        $field = " * ";
    else if(is_array($param['field_table']))
        $field = join(', ', $param['field_table']);
    else
        $field = " * ";
        
    if(!empty($param['field_left_join']))
        $field .= ", ".join(", ", $param['field_left_join']);
        
    if(!empty($param['where'])){
        $str_where = db_update($param['where'], true);
        if($str_where) $str_where = " where {$str_where} ";
    }
    else $str_where = "";
    
    return "select {$field} from ".join(' ', $table_from)." {$str_where} ";
}

function get_table_alias($table){
    $name = is_array($table)? $table[0] : $table;
    $name = str_replace('{#DB_PF#}', DB_PF, $name);
    if( preg_match('/^\s*([a-z0-9_]+)\s+(as)?\s+([a-z0-9_]+)\s*$/i', $name, $save) ){
        return array($save[1], $save[3]);
    }
    else return array($name, '');
}

function db_get_fk($ref_tbl){
    $fk = get_meta_data("CORE_FOREIGN_KEYS");
    if( empty($fk) ) $fk = array();
    $ref_tbl = str_replace('{#DB_PF#}', DB_PF, $ref_tbl);
    $rows = preg_grep('/^[\w\d]+\|[\w\d]+\|'.$ref_tbl.'\|[\w\d]+$/i', $fk);
    foreach($rows as $i => $row){
        list($tbl, $fld, $ref_tbl, $ref_fld) = explode('|', $row);
        $rows[$i] = (object)array(
            'tbl' => $tbl,
            'fld' => $fld,
            'ref_tbl' => $ref_tbl,
            'ref_fld' => $ref_fld
        );
    }
    return $rows;
}

function db_set_fk($tbl, $fld, $ref_tbl, $ref_fld){
    $tbl = str_replace('{#DB_PF#}', DB_PF, $tbl);
    $ref_tbl = str_replace('{#DB_PF#}', DB_PF, $ref_tbl);
    
    $fk = get_meta_data("CORE_FOREIGN_KEYS");
    if( empty($fk) ) $fk = array();
    
    $str = "{$tbl}|{$fld}|{$ref_tbl}|{$ref_fld}";
    if(array_search($str, $fk) === false){
        $fk[] = $str;
        set_meta_data("CORE_FOREIGN_KEYS", $fk);
    }
}

function db_drop_fk($tbl, $fld, $ref_tbl, $ref_fld){
    $tbl = str_replace('{#DB_PF#}', DB_PF, $tbl);
    $ref_tbl = str_replace('{#DB_PF#}', DB_PF, $ref_tbl);
    
    $fk = get_meta_data("CORE_FOREIGN_KEYS");
    if( empty($fk) ) $fk = array();
    
    $str = "{$tbl}|{$fld}|{$ref_tbl}|{$ref_fld}";
    $index = array_search($str, $fk);
    if($index !== false){
        unset($fk[$index]);
        set_meta_data("CORE_FOREIGN_KEYS", $fk);
    }
}

function db_get_str2array($table, $str, $type = 'ARRAY'){
    $data = array();
    
    /*$str = preg_replace('/^;|;$/i', '', $str);
    $str = str_replace(';', ',', $str);*/
    
    $str = join(', ', preg_grep('/\d+/', explode(';', $str)));
    
    if($str){
        $rows = db_rows("select id, header from {$table} where id in ($str)");
        if($type == 'ARRAY'){
            foreach($rows as $row) $data[] = get_o($row, 'header');
            return $data;
        }
        else if($type == 'ARRAY_OBJECT') return $rows;
    }
    else
        return array();
        
}

?>
