-- 楽天商品ランキング情報
CREATE TABLE `rakuten`.`rakuten_item_ranking` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `genre_id` int(30) unsigned NOT NULL COMMENT 'ジャンルID',
  `item_name` varchar(255) NOT NULL COMMENT '商品名',
  `rank` int(10) NOT NULL COMMENT '順位',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '登録日時',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新日時',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品ランキング情報';