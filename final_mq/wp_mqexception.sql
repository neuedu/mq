-- phpMyAdmin SQL Dump
-- version 2.11.0
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2014 �?07 �?21 �?10:23
-- 服务器版本: 5.6.11
-- PHP 版本: 5.5.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- 数据库: `wordpresst`
--

-- --------------------------------------------------------

--
-- 表的结构 `wp_mqexception`
--

DROP TABLE IF EXISTS `wp_mqexception`;
CREATE TABLE IF NOT EXISTS `wp_mqexception` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `message` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `if_sync` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;
