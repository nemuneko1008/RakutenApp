-- 楽天ジャンル情報テーブル
CREATE TABLE `rakuten`.`rakuten_genre` (
  `genre_id` int(30) unsigned NOT NULL COMMENT 'ジャンルID',
  `genre_name` varchar(255) NOT NULL COMMENT 'ジャンル名',
  `genre_level` int(10) NOT NULL COMMENT 'ジャンル階層',
  `parent_id` int(30) unsigned NOT NULL COMMENT '親ジャンルID',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '登録日時',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新日時',
  PRIMARY KEY (`genre_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='ジャンル情報';
