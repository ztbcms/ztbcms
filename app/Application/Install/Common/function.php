<?php

function sql_split($sql, $tablepre) {
    if ($tablepre != "cms_") {
        $sql = str_replace("cms_", $tablepre, $sql);
    }

    $sql = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=utf8", $sql);

//    if ($r_tablepre != $s_tablepre) {
//        $sql = str_replace($s_tablepre, $r_tablepre, $sql);
//    }

    $sql = str_replace("\r", "\n", $sql);
    $ret = array();
    $num = 0;
    $queriesarray = explode(";\n", trim($sql));
    unset($sql);
    foreach ($queriesarray as $query) {
        $ret[$num] = '';
        $queries = explode("\n", trim($query));
        $queries = array_filter($queries);
        foreach ($queries as $q) {
            $str1 = substr($q, 0, 1);
            if ($str1 != '#' && $str1 != '-') {
                $ret[$num] .= $q;
            }

        }
        $num++;
    }
    return $ret;
}


