INSERT INTO `dbshop_single_article` (`single_article_id`, `single_article_writer`, `single_article_add_time`, `article_tag`, `template_tag`) VALUES
(1, '', '1549469993', 'index_floor_1_title', 'dbmall'),
(2, '', '1549475083', 'index_floor_2_title', 'dbmall'),
(3, '', '1549469927', 'index_floor_3_title', 'dbmall'),
(4, '', '1549469952', 'index_floor_4_title', 'dbmall'),
(5, '', '1549475680', 'index_service', 'dbmall'),
(6, '', '1549475691', 'index_service', 'dbmall'),
(7, '', '1549475712', 'index_pay', 'dbmall'),
(8, '', '1549475722', 'index_pay', 'dbmall'),
(9, '', '1549475761', 'index_about', 'dbmall'),
(10, '', '1549475816', 'index_about', 'dbmall'),
(11, '', '1549475848', 'index_customer', 'dbmall'),
(12, '', '1549475857', 'index_customer', 'dbmall'),
(13, '', '1549475879', 'index_help', 'dbmall'),
(14, '', '1549475890', 'index_help', 'dbmall'),
(15, '', '1549476004', 'index_tel', 'dbmall'),
(16, '', '1549476052', 'index_chengnuo', 'dbmall'),
(17, '', '1549476076', 'index_chengnuo', 'dbmall'),
(18, '', '1549476094', 'index_chengnuo', 'dbmall'),
(19, '', '1549476111', 'index_chengnuo', 'dbmall'),
(20, '', '1549476127', 'index_chengnuo', 'dbmall');

INSERT INTO `dbshop_single_article_extend` (`single_article_id`, `single_article_title`, `single_article_body`, `single_article_title_extend`, `single_article_keywords`, `single_article_description`, `language`) VALUES
(1, '1F標題', '<p>特價商品</p>', '', '熱銷,商品', '熱銷,商品', 'zh_TW'),
(2, '2f標題', '<p>精品促銷</p>', '', '時尚,數碼', '時尚,數碼', 'zh_TW'),
(3, '3f標題', '<p>時尚潮流</p>', '', '時尚潮流', '時尚潮流', 'zh_TW'),
(4, '4f標題', '<p>熱門推薦</p>', '', '熱門,推薦', '熱門,推薦', 'zh_TW'),
(5, '聯系我們', '<p></p>', '', '', '', 'zh_TW'),
(6, '退換服務', NULL, '', '', '', 'zh_TW'),
(7, '在線支付', NULL, '', '', '', 'zh_TW'),
(8, '線下支付', NULL, '', '', '', 'zh_TW'),
(9, '關于我們', NULL, '', '', '', 'zh_TW'),
(10, '網站介紹', NULL, '', '', '', 'zh_TW'),
(11, '聯系客服', NULL, '', '', '', 'zh_TW'),
(12, '服務說明', NULL, '', '', '', 'zh_TW'),
(13, '如何購買', NULL, '', '', '', 'zh_TW'),
(14, '如何收貨', NULL, '', '', '', 'zh_TW'),
(15, '底部電話', '<p><span style="font-size: 20px;"><strong>400-800-xxxx</strong></span></p><p>周一至周五 9:00-18:00<br/></p>', '', '400-800-xxxx,周一,周五,00-18', '400-800-xxxx,周一,周五,00-18', 'zh_TW'),
(16, '一小時快速響應', NULL, '', '', '', 'zh_TW'),
(17, '7天無理由退貨', NULL, '', '', '', 'zh_TW'),
(18, '15天免費換貨', NULL, '', '', '', 'zh_TW'),
(19, '滿100包郵', NULL, '', '', '', 'zh_TW'),
(20, '售后服務', NULL, '', '', '', 'zh_TW');

INSERT INTO `dbshop_goods_class` (`class_id`, `class_top_id`, `class_name`, `class_path`, `class_state`, `class_info`, `class_sort`, `class_title_extend`, `class_keywords`, `class_description`, `class_icon`, `class_image`, `navigation_show`) VALUES
(1, 0, '家用電器', '1', 1, '', 1, '', '', '', NULL, NULL, NULL),
(2, 0, '手機數碼', '2', 1, '', 2, '', '', '', NULL, NULL, NULL),
(3, 0, '家居家具', '3', 1, '', 4, '', '', '', NULL, NULL, NULL),
(4, 0, '母嬰用品', '4', 1, '', 3, '', '', '', NULL, NULL, NULL),
(6, 1, '電視', '1,6', 1, '', 255, '', '', '', NULL, NULL, NULL),
(7, 1, '空調', '1,7', 1, '', 255, '', '', '', NULL, NULL, NULL),
(8, 1, '洗衣機', '1,8', 1, '', 255, '', '', '', NULL, NULL, NULL),
(14, 2, '手機通訊', '2,14', 1, '', 255, '', '', '', NULL, NULL, NULL),
(15, 2, '攝影攝像', '2,15', 1, '', 255, '', '', '', NULL, NULL, NULL),
(16, 2, '數碼配件', '2,16', 1, '', 255, '', '', '', NULL, NULL, NULL),
(17, 2, '智能設備', '2,17', 1, '', 255, '', '', '', NULL, NULL, NULL),
(18, 3, '廚具', '3,18', 1, '', 255, '', '', '', NULL, NULL, NULL),
(19, 3, '家紡', '3,19', 1, '', 255, '', '', '', NULL, NULL, NULL),
(20, 3, '燈具', '3,20', 1, '', 255, '', '', '', NULL, NULL, NULL),
(21, 3, '家具', '3,21', 1, '', 255, '', '', '', NULL, NULL, NULL),
(22, 4, '營養輔食', '4,22', 1, '', 255, '', '', '', NULL, NULL, NULL),
(23, 4, '尿褲濕巾', '4,23', 1, '', 255, '', '', '', NULL, NULL, NULL),
(24, 4, '喂養用品', '4,24', 1, '', 255, '', '', '', NULL, NULL, NULL),
(25, 4, '洗護用品', '4,25', 1, '', 255, '', '', '', NULL, NULL, NULL),
(26, 3, '廚房衛浴', '3,26', 1, '', 255, '', '', '', NULL, NULL, NULL),
(27, 3, '五金電工', '3,27', 1, '', 255, '', '', '', NULL, NULL, NULL),
(29, 0, '圖書文娛', '29', 1, '', 255, '', '', '', NULL, NULL, NULL),
(30, 1, '電冰箱', '1,30', 1, '', 255, '', '', '', NULL, NULL, NULL);

INSERT INTO `dbshop_goods` (`goods_id`, `goods_item`, `brand_id`, `attribute_group_id`, `goods_state`, `goods_weight`, `goods_type`, `virtual_goods_add_state`, `goods_start_time`, `goods_end_time`, `goods_price`, `goods_shop_price`, `goods_preferential_price`, `goods_preferential_start_time`, `goods_preferential_end_time`, `goods_integral_num`, `goods_stock`, `goods_out_of_stock_set`, `goods_cart_buy_max_num`, `goods_cart_buy_min_num`, `goods_stock_state`, `goods_stock_state_open`, `goods_out_stock_state`, `goods_add_time`, `goods_sort`, `goods_tag_str`, `goods_click`, `goods_class_have_true`, `virtual_sales`, `goods_spec_type`, `adv_spec_group_id`, `main_class_id`, `virtual_email_send`, `virtual_phone_send`) VALUES
(1, 'DBS000001', 0, 0, 1, '0', 1, 0, '', '', '0', '2199', '0', '', '', 0, 10, 0, 0, 0, 1, 1, 2, '1549472239', 255, ',5,6,7,8,', 1, 1, 0, 1, '', 6, 2, 2),
(2, 'DBS000002', 0, 0, 1, '0', 1, 0, '', '', '0', '5199', '0', '', '', 0, 10, 0, 0, 0, 1, 1, 2, '1549472422', 255, ',5,6,7,8,', 0, 1, 0, 1, '', 6, 2, 2),
(3, 'DBS000003', 0, 0, 1, '0', 1, 0, '', '', '0', '6158', '0', '', '', 0, 10, 0, 0, 0, 1, 1, 2, '1549472735', 255, ',5,6,7,8,', 0, 1, 0, 1, '', 14, 2, 2),
(4, 'DBS000004', 0, 0, 1, '0', 1, 0, '', '', '0', '119', '0', '', '', 0, 10, 0, 0, 0, 1, 1, 2, '1549473628', 255, ',5,6,7,8,', 0, 1, 0, 1, '', 19, 2, 2),
(5, 'DBS000005', 0, 0, 1, '0', 1, 0, '', '', '0', '5799', '0', '', '', 0, 10, 0, 0, 0, 1, 1, 2, '1549473912', 255, ',5,6,7,8,', 0, 1, 0, 1, '', 18, 2, 2),
(6, 'DBS000006', 0, 0, 1, '0', 1, 0, '', '', '4000', '3099', '0', '', '', 0, 10, 0, 0, 0, 1, 1, 2, '1549474546', 255, ',5,6,7,8,', 2, 1, 0, 1, '', 30, 2, 2),
(7, 'DBS000007', 0, 0, 1, '0', 1, 0, '', '', '0', '5399', '0', '', '', 0, 10, 0, 0, 0, 1, 1, 2, '1549474234', 255, ',5,6,7,8,', 1, 1, 0, 1, '', 8, 2, 2),
(8, 'DBS000008', 0, 0, 1, '0', 1, 0, '', '', '0', '3558', '0', '', '', 0, 10, 0, 0, 0, 1, 1, 2, '1549474397', 255, ',5,6,7,8,', 0, 1, 0, 1, '', 14, 2, 2);

INSERT INTO `dbshop_goods_extend` (`goods_id`, `goods_name`, `goods_info`, `goods_body`, `goods_image_id`, `goods_extend_name`, `goods_keywords`, `goods_description`, `language`) VALUES
(1, '小米（MI）電視4A標準版 L55M5-AZ/L55M5-AD 55英寸', '', NULL, 2, 'K超高清 HDR 人工智能液晶網絡平板電視', '', '', 'zh_TW'),
(2, '索尼(SONY)KD-55X8000E 55英寸 4K超高清', '', NULL, 6, '智能安卓7.0 享受視覺盛宴 醇音技術', '', '', 'zh_TW'),
(3, '蘋果(Apple) iPhone X 64GB 深空灰色 移動聯通電信全網通4G手機', '', NULL, 8, '5.8英寸全面屏，無線充電，面容ID識別', '', '', 'zh_TW'),
(4, '南極人(NanJiren)家紡 簡約全棉四件套床上用品純棉斜紋雙人被套床單式4件套', '', NULL, 12, '高支高密 / 貼身舒適 / 經久耐用—南極人正品', '', '', 'zh_TW'),
(5, '方太（FOTILE）20立方風魔方 側吸式觸控式手感智控抽吸油煙機燃氣灶煙灶套餐JQD2T+HC8BE', '', NULL, 14, '方太幸福春節購，到手僅需5799！', '', '', 'zh_TW'),
(6, '美的（Midea）BCD-525WKPZM(E) 星際銀 525升對開門電冰箱', '', NULL, 17, '雙變頻風冷 APP智能操控 輕薄機身', '', '', 'zh_TW'),
(7, '西門子（SIEMENS） XQG100-WM14U561HW 10公斤 變頻 一鍵智能除漬', '', NULL, 19, '家居互聯遠程操控，特漬洗程序，256種水位調節，90℃高溫筒清潔，LED全觸控界面', '', '', 'zh_TW'),
(8, '小米Mix3 全網通版 8GB+128GB 黑色 磁動力滑蓋全面屏', '', NULL, 22, '磁動力滑蓋全面屏，前置雙攝', '', '', 'zh_TW');

INSERT INTO `dbshop_goods_image` (`goods_image_id`, `goods_id`, `goods_title_image`, `goods_thumbnail_image`, `goods_watermark_image`, `goods_source_image`, `image_sort`, `image_slide`, `editor_session_str`, `language`) VALUES
(1, 1, '/public/demo/goods/20190207/205975beb56ba9d3bf6f55d2a72e1a99.jpg', '/public/demo/goods/20190207/thumb_205975beb56ba9d3bf6f55d2a72e1a99.jpg', '/public/demo/goods/20190207/205975beb56ba9d3bf6f55d2a72e1a99.jpg', '/public/demo/goods/20190207/205975beb56ba9d3bf6f55d2a72e1a99.jpg', 255, 1, NULL, 'zh_TW'),
(2, 1, '/public/demo/goods/20190207/2094d0cd98ddee55b1a5696434f43483.jpg', '/public/demo/goods/20190207/thumb_2094d0cd98ddee55b1a5696434f43483.jpg', '/public/demo/goods/20190207/2094d0cd98ddee55b1a5696434f43483.jpg', '/public/demo/goods/20190207/2094d0cd98ddee55b1a5696434f43483.jpg', 255, 1, NULL, 'zh_TW'),
(3, 1, '/public/demo/goods/20190207/628c5af1b375828c485aad4f01fe9524.jpg', '/public/demo/goods/20190207/thumb_628c5af1b375828c485aad4f01fe9524.jpg', '/public/demo/goods/20190207/628c5af1b375828c485aad4f01fe9524.jpg', '/public/demo/goods/20190207/628c5af1b375828c485aad4f01fe9524.jpg', 255, 1, NULL, 'zh_TW'),
(4, 1, '/public/demo/goods/20190207/929f5fa1841a169ca21687a41f8a09c4.jpg', '/public/demo/goods/20190207/thumb_929f5fa1841a169ca21687a41f8a09c4.jpg', '/public/demo/goods/20190207/929f5fa1841a169ca21687a41f8a09c4.jpg', '/public/demo/goods/20190207/929f5fa1841a169ca21687a41f8a09c4.jpg', 255, 1, NULL, 'zh_TW'),
(5, 2, '/public/demo/goods/20190207/ca2f79d7a5411624ac1db73b1d879690.jpg', '/public/demo/goods/20190207/thumb_ca2f79d7a5411624ac1db73b1d879690.jpg', '/public/demo/goods/20190207/ca2f79d7a5411624ac1db73b1d879690.jpg', '/public/demo/goods/20190207/ca2f79d7a5411624ac1db73b1d879690.jpg', 255, 1, NULL, 'zh_TW'),
(6, 2, '/public/demo/goods/20190207/c534950c4a79ea20293e483fd4ed4aff.jpg', '/public/demo/goods/20190207/thumb_c534950c4a79ea20293e483fd4ed4aff.jpg', '/public/demo/goods/20190207/c534950c4a79ea20293e483fd4ed4aff.jpg', '/public/demo/goods/20190207/c534950c4a79ea20293e483fd4ed4aff.jpg', 255, 1, NULL, 'zh_TW'),
(7, 2, '/public/demo/goods/20190207/cccb1e61af8217a4a3ba123772be3362.jpg', '/public/demo/goods/20190207/thumb_cccb1e61af8217a4a3ba123772be3362.jpg', '/public/demo/goods/20190207/cccb1e61af8217a4a3ba123772be3362.jpg', '/public/demo/goods/20190207/cccb1e61af8217a4a3ba123772be3362.jpg', 255, 1, NULL, 'zh_TW'),
(8, 3, '/public/demo/goods/20190207/8fa36ef874fa22000315de160e44b02e.jpg', '/public/demo/goods/20190207/thumb_8fa36ef874fa22000315de160e44b02e.jpg', '/public/demo/goods/20190207/8fa36ef874fa22000315de160e44b02e.jpg', '/public/demo/goods/20190207/8fa36ef874fa22000315de160e44b02e.jpg', 255, 1, NULL, 'zh_TW'),
(9, 3, '/public/demo/goods/20190207/b4fe9d54e6ce250c1a0519f16c71cc24.jpg', '/public/demo/goods/20190207/thumb_b4fe9d54e6ce250c1a0519f16c71cc24.jpg', '/public/demo/goods/20190207/b4fe9d54e6ce250c1a0519f16c71cc24.jpg', '/public/demo/goods/20190207/b4fe9d54e6ce250c1a0519f16c71cc24.jpg', 255, 1, NULL, 'zh_TW'),
(10, 3, '/public/demo/goods/20190207/17a1e58a73d3d31b3bfa237cacc08fc6.jpg', '/public/demo/goods/20190207/thumb_17a1e58a73d3d31b3bfa237cacc08fc6.jpg', '/public/demo/goods/20190207/17a1e58a73d3d31b3bfa237cacc08fc6.jpg', '/public/demo/goods/20190207/17a1e58a73d3d31b3bfa237cacc08fc6.jpg', 255, 1, NULL, 'zh_TW'),
(11, 4, '/public/demo/goods/20190207/5d0debacf0fec409a2ac0c544a9b4d01.jpg', '/public/demo/goods/20190207/thumb_5d0debacf0fec409a2ac0c544a9b4d01.jpg', '/public/demo/goods/20190207/5d0debacf0fec409a2ac0c544a9b4d01.jpg', '/public/demo/goods/20190207/5d0debacf0fec409a2ac0c544a9b4d01.jpg', 255, 1, NULL, 'zh_TW'),
(12, 4, '/public/demo/goods/20190207/ff468a75401a5ea18a89604aa85ddbce.jpg', '/public/demo/goods/20190207/thumb_ff468a75401a5ea18a89604aa85ddbce.jpg', '/public/demo/goods/20190207/ff468a75401a5ea18a89604aa85ddbce.jpg', '/public/demo/goods/20190207/ff468a75401a5ea18a89604aa85ddbce.jpg', 255, 1, NULL, 'zh_TW'),
(13, 4, '/public/demo/goods/20190207/2a4a0e3add89d3ee51e35286ba7a0203.jpg', '/public/demo/goods/20190207/thumb_2a4a0e3add89d3ee51e35286ba7a0203.jpg', '/public/demo/goods/20190207/2a4a0e3add89d3ee51e35286ba7a0203.jpg', '/public/demo/goods/20190207/2a4a0e3add89d3ee51e35286ba7a0203.jpg', 255, 1, NULL, 'zh_TW'),
(14, 5, '/public/demo/goods/20190207/b5c225bed8f0dc249315c09151208541.jpg', '/public/demo/goods/20190207/thumb_b5c225bed8f0dc249315c09151208541.jpg', '/public/demo/goods/20190207/b5c225bed8f0dc249315c09151208541.jpg', '/public/demo/goods/20190207/b5c225bed8f0dc249315c09151208541.jpg', 255, 1, NULL, 'zh_TW'),
(15, 5, '/public/demo/goods/20190207/1ae4af6169af3a36c57c81dba9151fe4.png', '/public/demo/goods/20190207/thumb_1ae4af6169af3a36c57c81dba9151fe4.png', '/public/demo/goods/20190207/1ae4af6169af3a36c57c81dba9151fe4.png', '/public/demo/goods/20190207/1ae4af6169af3a36c57c81dba9151fe4.png', 255, 1, NULL, 'zh_TW'),
(16, 6, '/public/demo/goods/20190207/1eaa97cbbb3bff8891f94fc2ebcc2ced.jpg', '/public/demo/goods/20190207/thumb_1eaa97cbbb3bff8891f94fc2ebcc2ced.jpg', '/public/demo/goods/20190207/1eaa97cbbb3bff8891f94fc2ebcc2ced.jpg', '/public/demo/goods/20190207/1eaa97cbbb3bff8891f94fc2ebcc2ced.jpg', 255, 1, NULL, 'zh_TW'),
(17, 6, '/public/demo/goods/20190207/1bb0543aa7433fca1e25a06c75ef8f8d.jpg', '/public/demo/goods/20190207/thumb_1bb0543aa7433fca1e25a06c75ef8f8d.jpg', '/public/demo/goods/20190207/1bb0543aa7433fca1e25a06c75ef8f8d.jpg', '/public/demo/goods/20190207/1bb0543aa7433fca1e25a06c75ef8f8d.jpg', 255, 1, NULL, 'zh_TW'),
(18, 6, '/public/demo/goods/20190207/81b235c99ff28f59f6d1f74723de3bef.jpg', '/public/demo/goods/20190207/thumb_81b235c99ff28f59f6d1f74723de3bef.jpg', '/public/demo/goods/20190207/81b235c99ff28f59f6d1f74723de3bef.jpg', '/public/demo/goods/20190207/81b235c99ff28f59f6d1f74723de3bef.jpg', 255, 1, NULL, 'zh_TW'),
(19, 7, '/public/demo/goods/20190207/ac41193ced77f8867f40b331fb6286fe.jpg', '/public/demo/goods/20190207/thumb_ac41193ced77f8867f40b331fb6286fe.jpg', '/public/demo/goods/20190207/ac41193ced77f8867f40b331fb6286fe.jpg', '/public/demo/goods/20190207/ac41193ced77f8867f40b331fb6286fe.jpg', 255, 1, NULL, 'zh_TW'),
(20, 8, '/public/demo/goods/20190207/db5db1718bd131c8bf92da06ac657fea.jpg', '/public/demo/goods/20190207/thumb_db5db1718bd131c8bf92da06ac657fea.jpg', '/public/demo/goods/20190207/db5db1718bd131c8bf92da06ac657fea.jpg', '/public/demo/goods/20190207/db5db1718bd131c8bf92da06ac657fea.jpg', 255, 1, NULL, 'zh_TW'),
(21, 8, '/public/demo/goods/20190207/4b24eaedb431b880978c1617dc948810.jpg', '/public/demo/goods/20190207/thumb_4b24eaedb431b880978c1617dc948810.jpg', '/public/demo/goods/20190207/4b24eaedb431b880978c1617dc948810.jpg', '/public/demo/goods/20190207/4b24eaedb431b880978c1617dc948810.jpg', 255, 1, NULL, 'zh_TW'),
(22, 8, '/public/demo/goods/20190207/b4793d059bbfb5c56537be2d0de9bef8.jpg', '/public/demo/goods/20190207/thumb_b4793d059bbfb5c56537be2d0de9bef8.jpg', '/public/demo/goods/20190207/b4793d059bbfb5c56537be2d0de9bef8.jpg', '/public/demo/goods/20190207/b4793d059bbfb5c56537be2d0de9bef8.jpg', 255, 1, NULL, 'zh_TW');

INSERT INTO `dbshop_goods_in_class` (`goods_id`, `class_id`, `class_state`, `class_goods_sort`, `class_recommend`) VALUES
(1, 1, 1, 255, 0),
(1, 6, 1, 255, 0),
(2, 1, 1, 255, 0),
(2, 6, 1, 255, 0),
(3, 2, 1, 255, 0),
(3, 14, 1, 255, 0),
(4, 3, 1, 255, 0),
(4, 19, 1, 255, 0),
(5, 3, 1, 255, 0),
(5, 18, 1, 255, 0),
(6, 1, 1, 255, 0),
(7, 1, 1, 255, 0),
(7, 8, 1, 255, 0),
(8, 2, 1, 255, 0),
(8, 14, 1, 255, 0),
(6, 30, 1, 255, 0);

INSERT INTO `dbshop_goods_tag` (`tag_id`, `tag_type`, `tag_group_id`, `tag_str`, `tag_sort`, `template_tag`, `show_type`) VALUES
(5, 'index_floor_1', 0, '', 255, 'dbmall', 'pc'),
(6, 'index_floor_2', 0, '', 255, 'dbmall', 'pc'),
(7, 'index_floor_3', 0, '', 255, 'dbmall', 'pc'),
(8, 'index_floor_4', 0, '', 255, 'dbmall', 'pc');

INSERT INTO `dbshop_goods_tag_extend` (`tag_id`, `tag_name`, `language`) VALUES
(5, '1F商品', 'zh_TW'),
(6, '2f商品', 'zh_TW'),
(7, '3f商品', 'zh_TW'),
(8, '4f商品', 'zh_TW');

INSERT INTO `dbshop_goods_tag_in_goods` (`tag_id`, `goods_id`, `tag_goods_sort`) VALUES
(5, 1, 255),
(5, 2, 255),
(5, 3, 255),
(5, 4, 255),
(5, 5, 255),
(5, 6, 255),
(5, 7, 255),
(5, 8, 255),
(6, 5, 255),
(6, 8, 90),
(6, 7, 255),
(6, 3, 100),
(6, 2, 1),
(6, 6, 255),
(6, 4, 255),
(6, 1, 2),
(7, 2, 255),
(7, 7, 1),
(7, 8, 4),
(7, 3, 255),
(7, 6, 2),
(7, 4, 3),
(7, 1, 255),
(7, 5, 255),
(8, 8, 255),
(8, 7, 4),
(8, 3, 255),
(8, 2, 7),
(8, 4, 6),
(8, 6, 255),
(8, 1, 2),
(8, 5, 3);

INSERT INTO `dbshop_goods_usergroup_price` (`goods_id`, `user_group_id`, `goods_color`, `goods_size`, `adv_spec_tag_id`, `goods_user_group_price`) VALUES
(1, 1, '', '', '', '0'),
(2, 1, '', '', '', '0'),
(3, 1, '', '', '', '0'),
(4, 1, '', '', '', '0'),
(5, 1, '', '', '', '0'),
(7, 1, '', '', '', '0'),
(8, 1, '', '', '', '0'),
(6, 1, '', '', '', '0');