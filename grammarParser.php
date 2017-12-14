<?php
/**
* GrammarParser By CIC
*/
class GrammarParser
{
    public $keywordUnitArray = array();
    public $operatorUnitArray = array();
    private $inputString;
    private $inputArray;
    private $tempString;
    function __construct($inputString)
    {
        $this->inputString = $inputString;
        $this->preHandle();
    }
    function preHandle()
    {
        $tempString = str_replace("\n"," ",$this->inputString);
        $tempString = str_replace("<br>"," ",$tempString);
        $tempString = str_replace("</br>"," ",$tempString);
        $tempString = str_replace("&nbsp;"," ",$tempString);
        $tempString = preg_replace("/\s+/"," ",$tempString);
        $this->tempString = trim($tempString);
        $this->inputArray = explode(" ",$this->tempString);
    }
    function generateList()
    {
        $n = count($this->inputArray);
        for($i=0;$i<$n;$i++)
        {
            $word = $this->inputArray[$i];
            if(in_array($word,Unit::$operatorList)){
                //直接是操作符的情况
                $tunit = new Unit($word);
                array_push($this->operatorUnitArray,$tunit);
            }else if(in_array($word,Unit::$keywordList)) {
                //直接是关键字的情况
                $tunit = new Unit($word);
                array_push($this->keywordUnitArray,$tunit);
            }else{
                //两种都不是，但有可能中间夹着操作符
                $word = $this->inputArray[$i];
                $tempOpList = array();
                for($j=0;$j<count(Unit::$operatorList);$j++){
                    //逐个查找操作符位置，并替换为空格供后面分割
                    $key = Unit::$operatorList[$j];
                    if(strlen($key)<=strlen($word)){
                        $arr = getCharpos($word,$key);
                        foreach ($arr as $t => $value) {
                            $t_unit = new Unit($key);
                            $t_unit->startPos = $value;
                            array_push($tempOpList,$t_unit);
                            $word = str_replace($key," ",$word);
                        }
                    }
                }
                $tempKwList = explode(" ",$word);
                foreach ($tempKwList as $key => $value) {
                    if(trim($value)!=""){
                        $t_unit = new Unit($value); 
                        array_push($this->keywordUnitArray,$t_unit);
                    }
                }
                //按照位置顺序排序
                usort($tempOpList,"my_sort");
                foreach ($tempOpList as $key => $value) {
                    array_push($this->operatorUnitArray,$value);
                }
            }
        }
    }
}
/**
* UnitClass By CIC
* 最小单位语法
*/
class Unit 
{
    const OPERATOR = 1;
    const KEYWORD = 2;
    const VARIABLE = 3; 
    public $startPos;
    //以特殊顺序排好的数组，防止某些关键字被提前分割
    public static $keywordList = array(
        "xor_eq","xor","while","wchar_t","volatile","void","virtual","using",
        "unsigned","union","typename","typeid","typedef","try","true","throw",
        "thread_local","this","template","switch","struct","static_cast",
        "static_assert","static","sizeof","signed","short","return","reinterpret_cast",
        "register","public","protected","private","or_eq","or","operator","nullptr",
        "not_eq","not","noexcept","new","namespace","mutable","long","int","inline",
        "if","goto","friend","for","float","false","extern","export","explicit","enum",
        "else","dynamic_cast","double","do","delete","default","decltype","continue",
        "constexpr","const_cast","const","compl","class","char32_t","char16_t","char",
        "catch","case","break","bool","bitor","bitand","auto","asm","and_eq","and",
        "alignof","alignas","bitand","and_eq","delete","xor_eq","not_eq","bitor",
        "or_eq","compl","xor","not","and",
    );
    public static $operatorList = array(
        "%:%:",">>=","<<=","->*","...","new","ˆ=","&=","==","!=",">=","<=",
        "%=","+=","&&","|=","*=","/=","-=","<<","->","<%","%>",":>","<:","or","##",
        "%:","::","--","++","ˆ",".*",">>","||",",","=",">","<",";",":","?",")","(",
        "}","[","]","#",".","+","~","!","{","<","|","&","-","*","/","%",">",
    );
    private $type;
    public $content;
    function __construct($content)
    {
        $this->content = trim($content);
        $this->type = $this->getType();
    }
    function getType()
    {
        if(in_array($this->content,self::$keywordList))
        {
            $this->type = self::KEYWORD;
            return self::KEYWORD;
        }
        if(in_array($this->content,self::$operatorList))
        {
            $this->type = self::OPERATOR;
            return self::OPERATOR;
        }
        $this->type = self::VARIABLE;
        return self::VARIABLE;
    }
}
function my_sort($a,$b){
    if($a->startPos == $b->startPos){
        return 0;
    }else if($a->startPos < $b->startPos){
        return -1;
    }else{
        return 1;
    }
}
function getCharpos($str, $char){
       $j = 0;
       $arr = array();
       $count = substr_count($str, $char);
       for($i = 0; $i < $count; $i++){
             $j = strpos($str, $char, $j);
             $arr[] = $j;
             $j = $j+1;
       }
       return $arr;
}
function checkInterface($stringA,$stringB){
    $helper = new GrammarParser($stringA);
    $helper->generateList();
    $helper2 = new GrammarParser($stringB);
    $helper2->generateList();
    // echo "A字符串是: ".$stringA."\n";
    // echo "B字符串是: ".$stringB."\n";
    // print_r("A's operatorList length: ".count($helper->operatorUnitArray)."\n");
    // print_r("B's operatorList length: ".count($helper2->operatorUnitArray)."\n");
    // print_r("A's keywordList length: ".count($helper->keywordUnitArray)."\n");
    // print_r("B's keywordList length: ".count($helper2->keywordUnitArray)."\n");
    if(count($helper->operatorUnitArray)==count($helper2->operatorUnitArray)){
        if(count($helper->keywordUnitArray)==count($helper2->keywordUnitArray)){
            $keycount = count($helper->operatorUnitArray);
            $A = $helper->operatorUnitArray;
            $B = $helper2->operatorUnitArray;
            for($i=0;$i<$keycount;$i++){
                if( $A[$i]->content != $B[$i]->content ){
                    // print_r("分析结果不同1\n");
                    return false;
                }
            }
            $keycount = count($helper->keywordUnitArray);
            $A = $helper->keywordUnitArray;
            $B = $helper2->keywordUnitArray;
            print_r($A);
            print_r($B);
            for($i=0;$i<$keycount;$i++){
                if( $A[$i]->content != $B[$i]->content ){
                    // print_r("分析结果不同\n");
                    return false;
                }
            }
            // print_r("分析结果相同\n");
            return true;
        }
    }
    // print_r("分析结果不同\n");
    return false;
}
// $string = "C(int i,int j,int k):A(i),b1(j)";
// $string2 = "C ( int i,\nint j, \n\nint   k):\nA(i),b1(j)";
// checkInterface($string,$string2);
// echo "\n";
// $string = "template<typename T>";
// $string2 = "template    < typename T >";
// checkInterface($string,$string2);
// echo "\n";
// $string = "protect:";
// $string2 = "protect        \n  :";
// checkInterface($string,$string2);
// echo "\n";
// $string = "c=k";
// $string2 = "c\n   =\n\n          k";
// checkInterface($string,$string2);
// echo "\n";
// $string = "a[j]>a[j+1]";
// $string2 = "a   [\nj  \n]>\na\n [\n   j\n   +\n1\n]";
// checkInterface($string,$string2);
// echo "\n";
// $string = "template<typename T> void Print(T* a,int n)";
// $string2 = "template<\n     typename\n     T> void \nPrint\n(T    * a  ,\nint n)";
// checkInterface($string,$string2);
?>