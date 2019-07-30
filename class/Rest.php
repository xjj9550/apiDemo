<?php

class Rest
{
    /**
     * @var User
     */
    private $_user;

    /**
     * @var Article
     */
    private $_article;

    /**
     * 请求方法
     * @var 
     */
    private $_requestMethod;

    /**
     * 版本号
     */
    private $_version;

    /**
     * 资源标识
     */
    private $_requestUrl;

    /**
     * 允许请求的资源
     * @var array
     */
    private $_allowResource = ['users','articles'];

    /**
     * 允许请求的方法
     * @var array
     */
    private $_allowMethod = ['GET','POST','PUT','DELETE'];

    /**
     * 常见的状态码
     * @var array   
     */
    private $_statusCode = [
        200 => 'ok',
        204 => 'No Content',
        400 => 'Bad Request',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allow',
        500 => 'server Internal Error',

    ];

    /**
     * 请求资源
     * @var 
     */
    private $requestResource;

    /**
     * Rest constructor
     * @param User $_user
     * @param Article $_article
     */
    public function __construct($_user,$_article)
    {
        $this->_user = $_user;
        $this->_article = $_article;
    }

    /**
     * api 启动方法
     */
    public function run()
    {
        try{
            $this->setMethod();
            $this->setResource();
            if($this->requestResource == 'users'){
                $this->sendUsers();
            }else{
                $this->sendArticles();
            }
        }catch(Exception $e){
            $this->_json($e->getMessage(),$e->getCode());
        }
    }

    /**
     * 处理文章逻辑
     */
    private function sendArticles()
    {

    }

    /**
     * 处理用户逻辑
     */
    private function sendUsers()
    {
        if($this->_requestMethod !== "POST"){
            throw new Exception('请求方法不被允许',405);
        }
        if(empty($this->_requestUrl)){
            throw new Exception('请求参数缺失',400);
        }
        if($this->_requestUrl == 'login'){
            $this->dologin();
        }elseif($this->_requestUrl == 'register'){
            $this->doregister();
        }else{
            throw new Exception('请求资源不被允许',405);
        }
    }

    /**
     * 用户注册接口
     * @throws Exception
     */
    private function doregister()
    {
        $data = $this->getBody();
        if(empty($data['name'])){
            throw new Exception('用户名不能为空',400);
        }
        if(empty($data['password'])){
            throw new Exception('用户密码不能为空',400);
        }
        $user = $this->_user->register($data['name'],$data['password']);
        if($user){
            $this->_json('注册成功',200);
        }
    }

    private function getBody()
    {
        $data = file_get_contents("php://input");
        if(empty($data)){
            throw new Exception('请求参数错误',400);
        }
        return json_decode($data,true);
    }

    private function dologin()
    {
        $data = $this->getBody();
        if(empty($data['name'])){
            throw new Exception('用户名不能为空',400);
        }
        if(empty($data['password'])){
            throw new Exception('用户密码不能为空',400);
        }
        $user = $this->_user->login($data['name'],$data['password']);
        $data = [
            'data'=>[
                'user_id'=>$user['id'],
                'name'=>$user['name'],
                'token'=>session_id(),
            ],
            'message'=>'登录成功',
            'code'=>200
        ];
        return json_encode($data);
    }
    
    /**
     * 发表文章
     * @throws Exception
     */
    private function articleCreate()
    {
        $data = $this->getBody();
        // 判断用户是否已经登录
        if(!$this->isLogin($data['token'])){
            throw new Exception('请重新登录',403);
        }

        // 判断文章标题和内容是否为空
        if(empty($data['title'])){
            throw new Exception('文章标题不能为空',400);
        }
        if(empty($data['content'])){
            throw new Exception('文章的内容不能为空',400);
        }

        $user_id = $_SESSION['userInfo']['id'];
        $return = $this->_article->create($data['title'],$data['content'],$user_id);
        if(!empty($return)){
            $this->_json('发表成功',200);
        }else{
            $this->_json('发表失败',500);
        }
    }

    /**
     * 判断用户是否登录
     */
    private function isLogin($token)
    {
        $sessionID = session_id();
        if($sessionID != $token){
            return false;
        }
        return true;
    }
    /**
     * 处理资源
     * @throws Exception
     */
    public function setResource()
    {
        $path = $_SERVER['PATH_INFO'];
        $params = explode('/',$path);
        // var_dump($params);
        $this->requestResource = $params['2'];
        if(!in_array($this->requestResource,$this->_allowResource)){
            throw new Exception('请求资源不被允许',405);
        }
        $this->_version = $params[1];

        if(!empty($params[3])){
            $this->_requestUrl = $params[3];
        }
    }

    /**
     * 设置api请求方法
     * @throws Exception
     */
    private function setMethod()
    {
        $this->_requestMethod = $_SERVER['REQUEST_METHOD'];
        IF(!in_array($this->_requestMethod,$this->_allowMethod)){
            throw new Exception('请求方法不被允许',405);
        }
    }

    /**
     * 数据输出
     * @param $message string 提示信息
     * @param $code int 状态码
     */
    private function _json($message,$code)
    {
        if($code !== 200 && $code>200){
            header('HTTP/1.1'.$code.' '.$this->_statusCode[$code]);
        }
        header("Content-Type:application/json;charset:utf-8");
        if(!empty($message)){
            echo json_encode(['message'=>$message,'code'=>$code]);
        }
        die;
    }

    /**
     * 文章修改API
     */
    private function articleEdit()
    {
        $data =  $this->getBody();
        if($this->isLogin($data['token'])){
            throw new Exception('请重新登录',403);
        }
        $article = $this->_article->view($this->_requestUri);
        if($article['user_id'] != $_SESSION['userInfo']['id']){
            throw new Exception('请重新登录',403);
        }
        $return = $this->_article->edit($this->_requestUri,$data['title'],$data['content'],$_SESSION['userInfo']['id']);
        if($return){
            $data = [
                'data'=> [
                    'title'=>$data['title'],
                    'content'=>$data['content'],
                    'user_id'=>$article['user_id'],
                    'creat_time'=>$article['creat_time']
                ],
                'message' => '修改成功',
                'code'=>200
            ];
            echo json_encode($data);
            die;
        }
        $data = [
            'data' => [
                'title'=>$article['title'],
                'content'=>$article['content'],
                'user_id'=>$article['user_id'],
                'creat_time'=>$article['creat_time']                
            ],
            'message' => '文章修改失败',
            'code'=>500
        ];
        echo json_encode($data);
        die;
    }
}