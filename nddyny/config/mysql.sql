CREATE TABLE nddyny_app
(
  app_id       INT AUTO_INCREMENT
  COMMENT 'app_id'
    PRIMARY KEY,
  app_name     VARCHAR(100) DEFAULT ''             NOT NULL
  COMMENT '应用名',
  status       TINYINT DEFAULT '0'                 NOT NULL
  COMMENT '状态',
  created_user INT DEFAULT '0'                     NOT NULL
  COMMENT '创建用户',
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
  COMMENT '创建时间',
  updated_user INT DEFAULT '0'                     NOT NULL
  COMMENT '修改用户',
  updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
  COMMENT '修改时间'
)
  COMMENT '应用表';

CREATE TABLE nddyny_user
(
  user_id            INT AUTO_INCREMENT
  COMMENT 'user_id'
    PRIMARY KEY,
  nickname           VARCHAR(7) DEFAULT ''               NOT NULL
  COMMENT '昵称',
  truename           VARCHAR(15) DEFAULT ''              NOT NULL
  COMMENT '真实姓名',
  mail               VARCHAR(100) DEFAULT ''             NOT NULL
  COMMENT '邮箱',
  google_auth_secret VARCHAR(100) DEFAULT ''             NOT NULL
  COMMENT '动态码密钥',
  password           VARCHAR(32) DEFAULT ''              NOT NULL
  COMMENT '密码',
  password_salt      VARCHAR(32) DEFAULT ''              NOT NULL
  COMMENT '密码盐',
  role               VARCHAR(20) DEFAULT 'normal'        NOT NULL
  COMMENT '角色',
  status             TINYINT DEFAULT '0'                 NOT NULL
  COMMENT '状态',
  created_user       INT DEFAULT '0'                     NOT NULL
  COMMENT '创建用户',
  created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
  COMMENT '创建时间',
  updated_user       INT DEFAULT '0'                     NOT NULL
  COMMENT '修改用户',
  updated_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
  COMMENT '修改时间'
)
  COMMENT '用户表';

CREATE TABLE nddyny_process_ssh
(
  ssh_id       INT AUTO_INCREMENT
  COMMENT 'ssh_id'
    PRIMARY KEY,
  ssh_no       VARCHAR(100) DEFAULT ''             NOT NULL
  COMMENT '编号',
  app_id       INT DEFAULT '0'                     NOT NULL
  COMMENT 'app_id',
  user_id      INT DEFAULT '0'                     NOT NULL
  COMMENT 'user_id（为0则是全用户共享）',
  group_name   VARCHAR(40) DEFAULT '默认'            NOT NULL
  COMMENT '分组名',
  address      VARCHAR(100) DEFAULT ''             NOT NULL
  COMMENT '服务器地址',
  port         INT DEFAULT '22'                    NOT NULL
  COMMENT '端口',
  account      VARCHAR(100) DEFAULT ''             NOT NULL
  COMMENT '帐号',
  password     VARCHAR(100) DEFAULT ''             NOT NULL
  COMMENT '密码',
  run          VARCHAR(2000) DEFAULT ''            NOT NULL
  COMMENT '命令',
  uri          VARCHAR(255) DEFAULT ''             NOT NULL
  COMMENT '参数',
  status       TINYINT DEFAULT '0'                 NOT NULL
  COMMENT '状态',
  created_user INT DEFAULT '0'                     NOT NULL
  COMMENT '创建用户',
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
  COMMENT '创建时间',
  updated_user INT DEFAULT '0'                     NOT NULL
  COMMENT '修改用户',
  updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
  COMMENT '修改时间',
  CONSTRAINT uk_sshno_appid_userid
  UNIQUE (ssh_no, app_id, user_id)
)
  COMMENT 'SSH类型的进程管理';

CREATE TABLE nddyny_process_method
(
  method_id    INT AUTO_INCREMENT
  COMMENT 'ssh_id'
    PRIMARY KEY,
  method_no    VARCHAR(100) DEFAULT ''             NOT NULL
  COMMENT '编号',
  app_id       INT DEFAULT '0'                     NOT NULL
  COMMENT 'app_id',
  user_id      INT DEFAULT '0'                     NOT NULL
  COMMENT 'user_id（为0则是全用户共享）',
  group_name   VARCHAR(40) DEFAULT '默认'            NOT NULL
  COMMENT '分组名',
  class_name   VARCHAR(255) DEFAULT ''             NOT NULL
  COMMENT '类名',
  method_name  VARCHAR(255) DEFAULT ''             NOT NULL
  COMMENT '方法名',
  params       VARCHAR(1000) DEFAULT ''            NOT NULL
  COMMENT '参数',
  status       TINYINT DEFAULT '0'                 NOT NULL
  COMMENT '状态',
  created_user INT DEFAULT '0'                     NOT NULL
  COMMENT '创建用户',
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
  COMMENT '创建时间',
  updated_user INT DEFAULT '0'                     NOT NULL
  COMMENT '修改用户',
  updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
  COMMENT '修改时间',
  CONSTRAINT uk_methodno_appid_userid
  UNIQUE (method_no, app_id, user_id)
)
  COMMENT 'method类型的进程管理';