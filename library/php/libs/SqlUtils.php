<?php
namespace libs;

trait SqlUtils{


    /**
     * list到sql的select
     * @param $list
     * @return string
     */
    public function listToSqlSelect($list){
        return !count($list) ? '1' : '`'.implode('`,`',$list).'`';
    }


    /**
     * list到sql的Update,会屏蔽修改为null
     * @param array $list
     * [
     *    string =>[
     *      string ||  ['sql'=>string [,'arr'=>array]]
     *      [,...]
     *     ]
     * ]
     * 'name'=>'aa'   // 'name=:name' [':name'=>'aa'];
     * 'money'=>['sql'=>'money_actual']   // 'money=money_actual';
     * 'money'=>['sql'=>'money+:money','arr'=>[':money'=>1]]   // 'money=money+:money' [':money'=>1];
     *
     *
     * @return array
     */
    public function listToSqlUpdate($list){
        $sql = '';
        $arr = array();
        foreach ($list as $key => $value){
            if(isset($value)){
                if(is_array($value)){
                    $sql .= "`$key`=".$value['sql'].',';
                    if(! empty($value['arr']))
                        $arr += $value['arr'];
                }else{
                    $sql .= "`$key`=:u_$key,";
                    $arr[":u_$key"] = $value;
                }
            }
        }
        $sql = substr($sql,0,strlen($sql)-1);
        return array('sql'=>$sql,'arr'=>$arr);
    }

    /**
     * list到sql的In
     * @param array $list
     * @return array
     */
    public function listToSqlIn($list){
        $sqlArr = [];
        $dataArr = [];
        foreach($list as $index => $item){
            array_push($sqlArr,":in_$index");
            $dataArr["in_$index"] = $item;
        }
        return array('sql'=>'('.implode(',',$sqlArr).')','arr'=>$dataArr);
    }



    /**
     * list到sql的Where,会屏蔽条件为null
     * @param array $list
     * [
     *    string =>[
     *      string ||  ['sql'=>string [,'arr'=>array]]
     *      [,...]
     *     ]
     * ]
     * 'name'=>'aa'   // 'name=:name' [':name'=>'aa'];
     * 'money'=>['sql'=>'money > money_actual']   // 'money > money_actual';
     * 'time'=>['sql'=>'money > :money','arr'=>[':money'=>88]]   // 'money > :money' [':money'=>88];
     * @return array
     */
    public function listToSqlWhere($list){
        $arr = [];
        $sql = '';
        foreach ($list as $key => $value){
            if($value){
                if(is_array($value)){
                    $sql .= $value['sql'].' AND ';
                    if(! empty($value['arr']))
                        $arr += $value['arr'];
                }else{
                    $sql .= "`$key`=:w_$key AND ";
                    $arr[":w_$key"] = $value;
                }
            }
        }
        $sql = substr($sql,0,strlen($sql)-5);

        return array('sql'=>$sql ? " WHERE $sql " : '','arr'=>$arr);
    }





    /**
     * 排序
     * @param $sorted ['key'=>'','order'=>'asc|desc']
     * @param $allow ['']
     * @param $defaultArr [''=>'asc|desc']
     * @return string
     */
    public function sortToString($sorted,$allow,$defaultArr){
        if($sorted === [] || !in_array($sorted['key'],$allow)){
            $key = array_keys($defaultArr)[0];
            $order = $defaultArr[$key];
        }else{
            $key = $sorted['key'];
            $order = $sorted['order'];
        }

        return 'ORDER BY '.$key.' '.$order;
    }


    /**
     * 筛选 ， 查询
     * @param array $filtered ['data'=>[],'allow'=>[key:[]]]
     * @param array $searched ['data'='','allow'=[],'isVague'=>bool]
     * @param string $otherSql
     * @param array $otherArr 其他数据数组
     * @return array
     */
    public function merge($filtered,$searched,$otherSql = '',$otherArr = []){
        $arr = [];

        //$filtered
        $filtersStr = '';
        if($filtered['data'] !== []){
            foreach($filtered['data'] as $key => $value){
                if(in_array($key,$filtered['allow']) && count($value)>0){
                    $t_arr = [];
                    foreach($value as $index=>$i){
                        $t_arr[] = ":_$key$index";
                        $arr["_$key$index"] = $i;
                    }
                    $filtersStr.="`$key` in (".implode(',',$t_arr).') and ';
                }
            }
            $filtersStr = substr($filtersStr,0,strlen($filtersStr)-5);
        }


        //$searched
        $searchedStr = '';
        if($searched['data'] !== []){
            foreach($searched['data'] as $key => $value){
                if(in_array($key,$searched['allow'])){
                    if($searched['isVague']){
                        $searchedStr.= "`$key` like :_{$key} and ";
                        $arr['_'.$key] = "%{$value}%";
                    }else{
                        $searchedStr.= "`$key`=:_{$key} and ";
                        $arr['_'.$key] = $value;
                    }
                }
            }
            $searchedStr = ($len = strlen($searchedStr)) >= 5 ?substr($searchedStr,0,$len-5) : '';
        }

        $str = $otherSql;

        if($filtersStr)
            $str .= $str ? ' and ' . $filtersStr : $filtersStr;

        if($searchedStr)
            $str .= $str ? ' and ' . $searchedStr : $searchedStr;

        if($str)
            $str = ' where '.$str;

        if(count($otherArr))
            $arr = $arr + $otherArr;

        return array('str'=>$str,'arr'=>$arr);
    }

}