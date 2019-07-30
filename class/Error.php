<?php

class Error 
{
    // 用户模块
    const USERNAME_CONNOT_NULL = 001;
    const USERPASS_CONNOT_NULL = 002;
    const USERNAME_EXISTS = 003;
    const REGIDTER_FAIL = 004;
    const LOGIN_FAIL = 005;
    const USERNAME_OR_PASSWORD_ERROR = 006;

    // 文章模块
    const ARTICLE_TITLE_COMMOT_NULL = 101;
    const ARTICLE_CONTENT_CONNOT_NULL = 102;
    const ARTICLE_CREATE_FAIL = 103;
    const ARTICLE_ID_CONNOT_NULL = 104;
    const ARTICLE_GET_FAIL = 105;
    const ARTICLE_NOT_EXISTS = 106;
    const PREMISSION_NOT_ALLOW = 107;
    const ARTICLE_EDIT_ERROR = 108;
    const ARTICLE_DELETE_FAIL = 109;
}