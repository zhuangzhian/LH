INSERT INTO dbshop_admin_group (admin_group_id, admin_group_purview) VALUES (1, 'a:1:{s:10:"purviewAll";s:1:"1";}');
INSERT INTO dbshop_admin_group_extend (admin_group_id, admin_group_name, `language`) VALUES (1, '管理员', 'zh_CN');

INSERT INTO dbshop_user_group (group_id) VALUES (1);
INSERT INTO dbshop_user_group_extend (group_id, group_name, `language`) VALUES (1, '普通会员', 'zh_CN');

INSERT INTO dbshop_currency (currency_id, currency_name, currency_code, currency_symbol, currency_decimal, currency_unit, currency_rate, currency_type, currency_state) VALUES
(1, 'CNY', 'CNY', '￥', 2, '元', '1.00000000', 1, 1);

INSERT INTO dbshop_express (express_id, express_name, express_info, express_url, express_sort, express_set, express_price, express_state) VALUES
(7, '顺丰速运', '国内服务质量最好，速度最快的速运公司。', '', 255, 'T', '0', 1);

INSERT INTO dbshop_stock_state (stock_state_id, state_sort, stock_type_state, state_type) VALUES
(1, 23, 1, 1),
(2, 255, 2, 1);
INSERT INTO dbshop_stock_state_extend (stock_state_id, stock_state_name, `language`) VALUES
(1, '有货', 'zh_CN'),
(2, '缺货', 'zh_CN');

INSERT INTO `dbshop_user_integral_type` (`integral_type_id`, `default_integral_num`, `integral_type_mark`, `integral_currency_con`) VALUES
(1, 0, 'integral_type_1', 1),
(2, 0, 'integral_type_2', 0);
INSERT INTO `dbshop_user_integral_type_extend` (`integral_type_id`, `integral_type_name`, `language`) VALUES
(1, '消费积分', 'zh_CN'),
(2, '等级积分', 'zh_CN');

INSERT INTO `dbshop_ad` (`ad_id`, `ad_class`, `ad_name`, `ad_place`, `goods_class_id`, `ad_type`, `ad_width`, `ad_height`, `ad_start_time`, `ad_end_time`, `ad_url`, `ad_body`, `ad_state`, `template_ad`, `show_type`) VALUES
(38, 'index', '首页幻灯片', 'class_right', NULL, 'slide', 992, 420, '', '', NULL, NULL, 1, 'dbmall', 'pc'),
(39, 'index', '1F图片', 'floor_1_image', NULL, 'image', 246, 495, '', '', NULL, '/public/demo/ad/39/f3569fb94ea872bf471b9e38b327b7df.jpg', 1, 'dbmall', 'pc'),
(40, 'index', '2F图片', 'floor_2_image', NULL, 'image', 246, 495, '', '', NULL, '/public/demo/ad/40/2d11328abab10fda29c6b38e43114e6b.jpg', 1, 'dbmall', 'pc'),
(41, 'index', '3F图片', 'floor_3_image', NULL, 'image', 246, 495, '', '', NULL, '/public/demo/ad/41/6cc78839c0a32cbe91e9113536212e2f.jpg', 1, 'dbmall', 'pc'),
(42, 'index', '4F图片', 'floor_4_image', NULL, 'image', 246, 495, '', '', NULL, '/public/demo/ad/42/e2d9b76b021bf4d67e1d48a921843d59.jpg', 1, 'dbmall', 'pc'),
(43, 'index', '幻灯片下1广告', 'huandengd_1', NULL, 'image', 310, 170, '', '', NULL, '/public/demo/ad/43/e47207fd8e6f7e96333889a01a1ae465.jpg', 1, 'dbmall', 'pc'),
(44, 'index', '幻灯片下2广告', 'huandengd_2', NULL, 'image', 310, 170, '', '', NULL, '/public/demo/ad/44/d694b5e1465c251aec9e91d34daef81a.jpg', 1, 'dbmall', 'pc'),
(45, 'index', '幻灯片下3广告', 'huandengd_3', NULL, 'image', 310, 170, '', '', NULL, '/public/demo/ad/45/a541ab3587f177e135e5cb0b8e92f591.jpg', 1, 'dbmall', 'pc'),
(46, 'index', '幻灯片下4广告', 'huandengd_4', NULL, 'image', 310, 170, '', '', NULL, '/public/demo/ad/46/3c8365264b7f682ce7154f894a807ebd.jpg', 1, 'dbmall', 'pc'),
(47, 'index', '横幅1', 'footer_1Fand2F', NULL, 'image', 1240, NULL, '', '', NULL, '/public/demo/ad/47/23936116e1fdf6c7bfef9082c5814ed0.jpg', 1, 'dbmall', 'pc'),
(48, 'index', '横幅2', 'footer_2Fand3F', NULL, 'image', 1240, NULL, '', '', NULL, '/public/demo/ad/48/31019caf22b42d1cd52073cbc5532c78.jpg', 1, 'dbmall', 'pc'),
(49, 'index', '横幅3', 'footer_3Fand4F', NULL, 'image', 1240, NULL, '', '', NULL, '/public/demo/ad/49/a580514522e2a1d6af728290439abdb0.jpg', 1, 'dbmall', 'pc');

INSERT INTO `dbshop_ad_slide` (`ad_id`, `ad_slide_info`, `ad_slide_image`, `ad_slide_sort`, `ad_slide_url`) VALUES
(38, NULL, '/public/demo/ad/38/c188b5236e725a4e30155614caf3a977.jpg', 1, NULL),
(38, NULL, '/public/demo/ad/38/5ce862594eeaee6da9306b36f44fc216.png', 2, NULL);