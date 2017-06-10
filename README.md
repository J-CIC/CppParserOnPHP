# CppPhraserOnPHP
简单的PHP，检测C++两个语句在语法层面上是否一致，要求变量名及顺序相同，只在可编译情况下风格不一致


使用方法
调用函数checkInterface($string,$string2);
传入两个字符串，返回值为true表示相同，false表示不同

示例:
    $string = "C(int i,int j,int k):A(i),b1(j)";
    $string2 = "C ( int i,\nint j, \n\nint   k):\nA(i),b1(j)";
    checkInterface($string,$string2);
    echo "\n";
    $string = "template<typename T>";
    $string2 = "template    < typename T >";
    checkInterface($string,$string2);
    echo "\n";
    $string = "protect:";
    $string2 = "protect        \n  :";
    checkInterface($string,$string2);
    echo "\n";
    $string = "c=k";
    $string2 = "c\n   =\n\n          k";
    checkInterface($string,$string2);
    echo "\n";
    $string = "a[j]>a[j+1]";
    $string2 = "a   [\nj  \n]>\na\n [\n   j\n   +\n1\n]";
    checkInterface($string,$string2);
    echo "\n";
    $string = "template<typename T> void Print(T* a,int n)";
    $string2 = "template<\n     typename\n     T> void \nPrint\n(T    * a  ,\nint n)";
    checkInterface($string,$string2);