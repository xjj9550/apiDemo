<?php

class Article
{
    /**
     * 数据库操作对象
     * @var PDO
     */
    private $_db;

    /**
     * Article constructor
     * @param PDO $_db
     */
    public function __construct(PDO $_db)
    {
        $this->_db = $_db;
    }

    /**
     * 文章发表
     * @param $title string 文章标题
     * @param $content string 文章内容
     * @param $user_id int 用户ID
     * @return array 文章的信息
     * @throws Exception
     */
    public function create($title,$content,$user_id)
    {
        if(empty($title)){
            throw new Exception("文章的标题不能为空",\Error::ARTICLE_TITLE_COMMOT_NULL);
        }
        if(empty($content)){
            throw new Exception("文章的内容不能为空",\Error::ARTICLE_CONTENT_CONNOT_NULL);
        }
        $sql = "insert into `article`(`title`,`content`,`user_id`,`creat_time`) values(:title,:content,:user_id,:creat_time)";

        $time = date("Y-m-d H:i:s",time());
        $sm = $this->_db->prepare($sql);
        
        // 预处理完就要绑定参数
        $sm->bindParam(':title',$title);
        $sm->bindParam(':content',$content);
        $sm->bindParam(':user_id',$user_id);
        $sm->bindParam(':creat_time',$time);

        if(!$sm->execute()){
            throw new Exception('发表文章失败',\Error::ARTICLE_CREATE_FAIL);
        }
        return [
            'title'=>$title,
            'content'=>$content,
            'article_id'=>$this->_db->lastInsertId(),
            'creat_time'=>$time,
            'user_id'=>$user_id
        ];
    }

    /**
     * 查看文章
     * @param $article_id int 文章的ID
     * @return mixed
     * @throws Exception 
     */
    public function view($article_id)
    {
        if(empty($article_id)){
            throw new Exception('文章的ID不能为空',\Error::ARTICLE_ID_CONNOT_NULL);
        }
        $sql = "select * from `article` where `id`=:id";
        $sm = $this->_db->prepare($sql);
        $sm->bindParam(':id',$article_id);
        if(!$sm->execute()){
            throw new Exception('获取文章失败',\Error::ARTICLE_GET_FAIL);
        }
        $article = $sm->fetch(PDO::FETCH_ASSOC);
        if(empty($article)){
            throw new Exception("文章不存在",\Error::ARTICLE_NOT_EXISTS);
        }
        return $article;
    }

    /**
     * 编辑文章
     * @param $article_id int 文章ID
     * @param $title string 文章标题
     * @param $content string 文章内容
     * @param $user_id int 用户id
     * @return array|mixed
     * @throws Exception 
     */
    public function edit($article_id,$title,$content,$user_id)
    {
        $article = $this->view($article_id);//获取文章的id
        if($user_id !== (int)$article['user_id']){
            throw new Exception("你无法操作此文章",\Error::PREMISSION_NOT_ALLOW);
        }
        $title = empty($title)?$article['title']:$title;
        $content = empty($content)?$article['content']:$content;
        if($title == $article['title'] && $content == $article['content']){
            return $article;
        }

        $sql = "update `article` set `title`=:title,`content`=:content where `id`=:id";
        $sm = $this->_db->prepare($sql);
        $sm->bindParam(':title',$title);
        $sm->bindParam(':content',$content);
        $sm->bindParam(':id',$article_id);
        if(!$sm->execute()){
            throw new Exception("编辑文章失败",\Error::ARTICLE_EDIT_ERROR);
        }

        return [
            'title' => $title,
            'content' => $content,
            'article_id' => $article_id,
            'user_id'   => $user_id
        ];
    }

    public function delete($article_id,$user_id)
    {
        $article = $this->view($article_id);
        if($user_id != (int)$article['user_id']){
            throw new Exception("无权操作此文章",\Error::PREMISSION_NOT_ALLOW);
        }

        // 定义修改语句
        $sql = "delete from `article` where `id` = :id and `user_id` = :user_id";
        $sm = $this->_db->prepare($sql);
        $sm -> bindParam(':id',$article_id);
        $sm -> bindParam(':user_id',$user_id);

        // 执行语句
        if(!$sm->execute()){
            throw new Exception("编辑删除失败",\Error::ARTICLE_DELETE_FAIL);
        }
        return $article;
    }

    public function _list($article_id,$user_id)
    {
        $artice = $this->view($article_id);
        if($user_id != (int)$article['user_id']){
            throw new Exception("您无权查看此文章",\Error::PREMISSION_NOT_ALLOW);
        }

        $sql = "select * from `article`(`title`,`content`,`user_id`,`create_time`) values(:title,:content,:user_id,:create_time)";
        $sm = $this->_db_prepare($sql);
        $sm -> bindParam(':title',$title);
        $sm -> bindParam(':content',$content);
        $sm -> bindParam(':user_id',$user_id);
        $sm -> bindParam(':create_time',$create_time);

        if(!$sm->excute()){
            throw new Exception("查看文章失败",\Error::ARTICLE_DELETE_FAIL);
        }
        return[
            'title' => $title,
            'content'=>$content,
            'article_id'=>$this->db->lastInsertId(),
            'create_time'=>$time,
            'user_id'=>$user_id
        ];
    }
}