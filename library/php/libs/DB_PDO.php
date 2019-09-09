<?php
/**
*author:TheLife
*version:3.0
*time:2017-11-20
*更多见:http://www.runoob.com/php/php-pdo.html
*/
namespace libs;

use Config_System;
use PDO;
use PDOException;

class DB_PDO{

    /** @var \PDO */
	private $pdo = null;
	private $transactions = 0;


    //静态变量保存全局实例
    private static $_instance = null;
    //私有克隆函数，防止外办克隆对象
    private function __clone() { }
    //静态方法，单例统一访问入口
    static public function getInstance() {
        if(!self::$_instance instanceof self){
            self::$_instance = new self ();
        }
        return self::$_instance;
    }
    //私有构造函数，防止外界实例化对象
    private function __construct() {
       $this->init();
    }

    private function init(){
        //连接
        if(!$this->conn(\configs\System::PDO_DSN,\configs\System::PDO_USERNAME,\configs\System::PDO_PASSWORD,\configs\System::PDO_DBNAME)) {
            if (\configs\System::$CONN_ERROR_EXIT)
                die(\configs\System::$CONN_ERROR_INFO);
            else
                $this->init();
        }
    }

    /**
     * 连接 mysql
     * @param $dsn
     * @param $username
     * @param $password
     * @param $dbname
     * @return bool
     */
	private function conn($dsn,$username,$password,$dbname){
        try {
            $pdo = new PDO("mysql:host=$dsn;dbname=$dbname;charset=utf8",$username,$password, array(PDO::MYSQL_ATTR_INIT_COMMAND=>"SET NAMES 'utf8'"));//charset 声明 DSN字符串
            //默认这个不是长连接，如果需要数据库长连接，需要最后加一个参数：array(PDO::ATTR_PERSISTENT => true)
            $pdo->setAttribute(PDO::ATTR_TIMEOUT,5);//连接超时时间
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);//交给mysql 进行字符转义 不在php中转义 防止注入
            $this->pdo = $pdo;
            return true;
        }
        catch (PDOException $e)
        {
            echo 'Mysql Connection failed: ' . $e->getMessage(); //连接失败,会抛出这个异常
            return false;
        }

    }
	
	/**
	 * 检查连接是否可用
	 * @return Boolean
	 */
	public function isPing(){
	    try{
	        $this->pdo->getAttribute(PDO::ATTR_SERVER_INFO);
	    } catch (PDOException $e) {
	        if(strpos($e->getMessage(), 'MySQL server has gone away')!==false){
	            return false;
	        }
	    }
	    return true;
	}

    /**
     * 查询一条结果集
     * @param $sqlStr
     * @param $pdoArray
     * @return array
     */
	public	function select($sqlStr,$pdoArray = array(),$driver_options = array()){
		$result = $this->pdo->prepare($sqlStr , $driver_options);
		$result->execute($pdoArray);
		return $result->fetch(PDO::FETCH_ASSOC);//按照键值对返回数据
	}
    /**
     * 查询所有结果集
     * @param $sqlStr
     * @param $pdoArray
     * @return array
     */
    public	function selectAll($sqlStr,$pdoArray = array(),$driver_options = array()){
        $result = $this->pdo->prepare($sqlStr , $driver_options);
        $result->execute($pdoArray);
        return $result->fetchAll(PDO::FETCH_ASSOC);//按照键值对返回数据
    }

    /** 查询返回 PDOStatement
     * @param $sqlStr
     * @param array $pdoArray
     * @param array $driver_options
     * @return \PDOStatement
     */
    public function selectSta($sqlStr,$pdoArray = array(),$driver_options = array()){
        $result = $this->pdo->prepare($sqlStr , $driver_options);
        $result->execute($pdoArray);
        return $result;
    }
    /**
     * 执行(非查询语句!返回影响行数)
     * @param $sqlStr
     * @param $pdoArray
     * @return integer
     */
	public function execs($sqlStr,$pdoArray = array(),$driver_options = array()){
		$result = $this->pdo->prepare($sqlStr , $driver_options);
		$result->execute($pdoArray);
		return $result->rowCount();
	}


    /**
     * 返回最后一次操作数据库的错误信息
     * @return array [0=>SQLSTATE 错误码 (5个字母或数字组成的在 ANSI SQL 标准中定义的标识符),1=>错误代码,2=>错误信息]
     */
    public	function errorInfo(){
        return $this->pdo->errorInfo ();
    }

    /**
     *获取跟数据库句柄上一次操作相关的 SQLSTATE
     * @return mixed  返回一个 SQLSTATE，一个由5个字母或数字组成的在 ANSI SQL 标准中定义的标识符。如果数据库句柄没有进行操作，则返回 NULL
     */
    public	function errorCode(){
        return $this->pdo->errorCode ();
    }

    /** 启动一个事务
        关闭自动提交模式。自动提交模式被关闭的同时，通过 PDO 对象实例对数据库做出的更改直到调用 PDO::commit() 结束事务才被提交。
        调用 PDO::rollBack() 将回滚对数据库做出的更改并将数据库连接返回到自动提交模式。
        包括 MySQL 在内的一些数据库，当发出一条类似 DROP TABLE 或 CREATE TABLE 这样的 DDL 语句时，会自动进行一个隐式地事务提交。
        隐式地提交将阻止你在此事务范围内回滚任何其他更改。
     * @return bool 成功时返回 TRUE， 或者在失败时返回 FALSE。
     */
    public	function beginTransaction(){
        ++$this->transactions;
        return $this->transactions == 1 ? $this->pdo->beginTransaction() : true;
    }

    /**
     * 提交一个事务
        提交一个事务，数据库连接返回到自动提交模式,直到下次调用 PDO::beginTransaction() 开始一个新的事务为止。
     * @return bool 成功时返回 TRUE， 或者在失败时返回 FALSE。
     */
    public function commit(){
        $re = true;
        if ($this->transactions == 1)
            $re = $this->pdo->commit();
        --$this->transactions;
        return $re;
    }
    /**
        回滚一个事务
        回滚由 PDO::beginTransaction() 发起的当前事务。如果没有事务激活，将抛出一个 PDOException 异常。
        如果数据库被设置成自动提交模式，此函数（方法）在回滚事务之后将恢复自动提交模式。
        包括 MySQL 在内的一些数据库， 当在一个事务内有类似删除或创建数据表等 DLL 语句时，会自动导致一个隐式地提交。隐式地提交将无法回滚此事务范围内的任何更改。
     * @return bool 成功时返回 TRUE， 或者在失败时返回 FALSE。
     */
    public	function rollback(){
        if($this->transactions == 1){
            $this->transactions = 0;
            return $this->pdo->rollBack();
        }else{
            --$this->transactions;
            return true;
        }
    }

    /**
     * 检查是否在一个事务内。此方法仅对支持事务的数据库驱动起作用。
     * @return mixed    如果当前事务处于激活，则返回 TRUE ，否则返回 FALSE
     */
    public	function inTransaction(){
        return $this->pdo->inTransaction ();
    }

    /**
     * 取出一个数据库连接的属性,(注意有些数据库/驱动可能不支持所有的数据库连接属性。)
        PDO::ATTR_AUTOCOMMIT
        PDO::ATTR_CASE
        PDO::ATTR_CLIENT_VERSION
        PDO::ATTR_CONNECTION_STATUS
        PDO::ATTR_DRIVER_NAME
        PDO::ATTR_ERRMODE
        PDO::ATTR_ORACLE_NULLS
        PDO::ATTR_PERSISTENT
        PDO::ATTR_PREFETCH
        PDO::ATTR_SERVER_INFO
        PDO::ATTR_SERVER_VERSION
        PDO::ATTR_TIMEOUT
     * @return mixed    成功调用则返回请求的 PDO 属性值。不成功则返回 null。
     */
    public function getAttribute($attribute){
        return $this->pdo->getAttribute($attribute);
    }
    /**
        设置数据库句柄属性。下面列出了一些可用的通用属性；有些驱动可能使用另外的特定属性。
        PDO::ATTR_CASE：强制列名为指定的大小写。
            PDO::CASE_LOWER：强制列名小写。
            PDO::CASE_NATURAL：保留数据库驱动返回的列名。
            PDO::CASE_UPPER：强制列名大写。
        PDO::ATTR_ERRMODE：错误报告。
            PDO::ERRMODE_SILENT： 仅设置错误代码。
            PDO::ERRMODE_WARNING: 引发 E_WARNING 错误
            PDO::ERRMODE_EXCEPTION: 抛出 exceptions 异常。
        PDO::ATTR_ORACLE_NULLS （在所有驱动中都可用，不仅限于Oracle）： 转换 NULL 和空字符串。
            PDO::NULL_NATURAL: 不转换。
            PDO::NULL_EMPTY_STRING： 将空字符串转换成 NULL。
            PDO::NULL_TO_STRING: 将 NULL 转换成空字符串。
        PDO::ATTR_STRINGIFY_FETCHES: 提取的时候将数值转换为字符串。 需要 bool。
        PDO::ATTR_STATEMENT_CLASS： 设置从PDOStatement派生的用户提供的语句类。 不能用于持久的PDO实例。 需要 array(string 类名, array(mixed 构造函数的参数))。
        PDO::ATTR_TIMEOUT： 指定超时的秒数。并非所有驱动都支持此选项，这意味着驱动和驱动之间可能会有差异。比如，SQLite等待的时间达到此值后就放弃获取可写锁，但其他驱动可能会将此值解释为一个连接或读取超时的间隔。 需要 int 类型。
        PDO::ATTR_AUTOCOMMIT （在OCI，Firebird 以及 MySQL中可用）： 是否自动提交每个单独的语句。
        PDO::ATTR_EMULATE_PREPARES 启用或禁用预处理语句的模拟。 有些驱动不支持或有限度地支持本地预处理。使用此设置强制PDO总是模拟预处理语句（如果为 TRUE ），或试着使用本地预处理语句（如果为 FALSE）。如果驱动不能成功预处理当前查询，它将总是回到模拟预处理语句上。 需要 bool 类型。
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY （在MySQL中可用）： 使用缓冲查询。
        PDO::ATTR_DEFAULT_FETCH_MODE： 设置默认的提取模式。关于模式的说明可以在 PDOStatement::fetch() 文档找到。
     * @return bool 成功时返回 TRUE， 或者在失败时返回 FALSE。
     */
    public function setAttribute( $attribute , $value){
        return $this->pdo->setAttribute($attribute,$value);
    }


	/**
	*获取当前pdo对象
	*/
	public	function getpdo(){
		return $this->pdo;
	}

    /**
        获取最后一次插入的id
        返回最后插入行的ID，或者是一个序列对象最后的值，取决于底层的驱动。比如，PDO_PGSQL() 要求为 name 参数指定序列对象的名称。
        注意： 在不同的 PDO 驱动之间，此方法可能不会返回一个有意义或一致的结果，因为底层数据库可能不支持自增字段或序列的概念。
     *  @return mixed
        如果没有为参数 name 指定序列名称，PDO::lastInsertId() 则返回一个表示最后插入数据库那一行的行ID的字符串。
        如果为参数 name 指定了序列名称，PDO::lastInsertId() 则返回一个表示从指定序列对象取回最后的值的字符串。
        如果当前 PDO 驱动不支持此功能，则 PDO::lastInsertId() 触发一个 IM001 SQLSTATE 。
     */
	public	function lastInsertId( $name = NULL ){
		return $this->pdo->lastInsertId($name);
	}
	/**
	 * 断开连接
	 */
	public	function close(){
        self::$_instance = null;
		$this->pdo = null;
	}
	public	function __destruct(){
	    $this->close();
	}




}