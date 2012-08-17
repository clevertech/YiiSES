<?php

class m120809_075915_ses_initial_migration extends CDbMigration
{
	public function up()
	{
		/**
		 * Campaigns
		 */
		$this->execute('CREATE TABLE `campaign` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(200) DEFAULT NULL,
			  `utm_source` varchar(200) DEFAULT NULL,
			  `utm_medium` varchar(200) DEFAULT NULL,
			  `utm_term` varchar(200) DEFAULT NULL,
			  `utm_content` varchar(200) DEFAULT NULL,
			  `subject` text,
			  `body_html` text,
			  `total_sent` int(11) DEFAULT 0,
			  `total_failed` int(11) DEFAULT 0,
			  `total_list` int(11) DEFAULT 0,
			  `sent_at` datetime DEFAULT NULL,
			  `body_text` text,
			  `status` tinyint(4) DEFAULT 0,
			  `template_id` int(11) DEFAULT 0,
			  `to_subscribers` tinyint(1) DEFAULT 0,
			  `custom` text,
			  `selected_users` text,
			  `create_time` int(10) DEFAULT NULL,
			  `scheduled_for` int(10) DEFAULT 0,
			  `total_opened` int(10) DEFAULT 0,
			  PRIMARY KEY (`id`),
			  KEY `k_scheduled_for` (`scheduled_for`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8');

		/**
		 * Emails
		 */
		$this->execute('CREATE TABLE `campaign_email` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `campaign_id` int(11) NOT NULL,
			  `status` int(11) NOT NULL DEFAULT 1,
			  `from_address` varchar(255) NOT NULL,
			  `from_name` varchar(255) DEFAULT NULL,
			  `to_address` varchar(255) NOT NULL,
			  `to_name` varchar(255) DEFAULT NULL,
			  `subject` varchar(255) NOT NULL,
			  `text_body` text,
			  `html_body` text,
			  `create_time` int(11) NOT NULL,
			  `send_time` int(11) DEFAULT NULL,
			  `opened` tinyint(1) DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  KEY `k_campaign_email_send_time` (`send_time`),
			  KEY `k_campaign_email_status` (`status`),
			  KEY `k_campaign_email_status_send_time` (`status`,`send_time`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8');

		/**
		 * Unsubscribed
		 */
		$this->execute('CREATE TABLE `campaign_unsubscribed` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `email` varchar(255) DEFAULT NULL,
			  `create_time` int(11) NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `k_unsubscribed_email` (`email`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8');

		/**
		 * User subscribed and username fields
		 * Note: Modify according to your table!
		 */
		// $this->addColumn('user','subscribed','TINYINT(1) DEFAULT 1');
		// $this->addColumn('user','username','VARCHAR(20) DEFAULT NULL');
	}

	public function down()
	{
		$this->dropTable('campaign');
		$this->dropTable('campaign_email');
		$this->dropTable('campaign_unsubscribed');
		// $this->dropColumn('user', 'subscribed');
		// $this->dropColumn('user', 'username');
	}
}