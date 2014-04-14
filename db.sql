
--
-- Table structure for table `vtcf_user`
--

CREATE TABLE IF NOT EXISTS `vtcf_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` int(11) NOT NULL DEFAULT '0',
  `sent` float NOT NULL,
  `phrase` varchar(255) NOT NULL,
  `entropy` varchar(255) NOT NULL,
  `wallet` varchar(255) NOT NULL,
  `IP` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4398 ;

-- --------------------------------------------------------

