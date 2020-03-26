CREATE TABLE `im_stock_main_pb` (
  `p_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `p_stock_id` CHAR(8) not null COMMENT '',
  `p_pb_val` int(10) default 0 COMMENT '',
  `p_trans_on` date DEFAULT NULL COMMENT '交易日期',
  `p_added_on` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'add时间',
  `p_update_on` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`p_id`),
  KEY `trans_on` (`p_trans_on`)
) ENGINE=InnoDB AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8mb4 COMMENT='市净率每日数据';