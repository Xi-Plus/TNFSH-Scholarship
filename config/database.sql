SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `admin` (
  `adm_account` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `adm_password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `adm_name` varchar(20) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `apply` (
  `app_id` int(11) NOT NULL,
  `app_name` varchar(20) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `attachments` (
  `att_id` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `att_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `att_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `data` (
  `data_id` int(11) NOT NULL,
  `data_semester` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `data_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `data_apply` int(11) NOT NULL,
  `data_date_start` date NOT NULL,
  `data_date_end` date NOT NULL,
  `data_money` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `data_quota` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `data_note` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `data_attachments` (
  `da_data` int(11) NOT NULL,
  `da_attachment` varchar(32) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `data_qualifications` (
  `dq_data` int(11) NOT NULL,
  `dq_qualification` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `login_session` (
  `ls_account` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `ls_cookie` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `ls_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `qualifications` (
  `qua_id` int(11) NOT NULL,
  `qua_category` int(11) NOT NULL,
  `qua_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `qualification_category` (
  `qc_id` int(11) NOT NULL,
  `qc_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE `admin`
  ADD PRIMARY KEY (`adm_account`);

ALTER TABLE `apply`
  ADD PRIMARY KEY (`app_id`);

ALTER TABLE `attachments`
  ADD PRIMARY KEY (`att_id`);

ALTER TABLE `data`
  ADD PRIMARY KEY (`data_id`),
  ADD KEY `data_apply` (`data_apply`);

ALTER TABLE `data_attachments`
  ADD KEY `data_id` (`da_data`),
  ADD KEY `file` (`da_attachment`);

ALTER TABLE `data_qualifications`
  ADD KEY `data_id` (`dq_data`),
  ADD KEY `qua_id` (`dq_qualification`);

ALTER TABLE `login_session`
  ADD PRIMARY KEY (`ls_cookie`),
  ADD KEY `account` (`ls_account`);

ALTER TABLE `qualifications`
  ADD PRIMARY KEY (`qua_id`),
  ADD KEY `qua_cat_id` (`qua_category`);

ALTER TABLE `qualification_category`
  ADD PRIMARY KEY (`qc_id`);


ALTER TABLE `apply`
  MODIFY `app_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `data`
  MODIFY `data_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `qualifications`
  MODIFY `qua_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `qualification_category`
  MODIFY `qc_id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `data`
  ADD CONSTRAINT `data_ibfk_1` FOREIGN KEY (`data_apply`) REFERENCES `apply` (`app_id`);

ALTER TABLE `data_attachments`
  ADD CONSTRAINT `data_attachments_ibfk_1` FOREIGN KEY (`da_attachment`) REFERENCES `attachments` (`att_id`),
  ADD CONSTRAINT `data_attachments_ibfk_2` FOREIGN KEY (`da_data`) REFERENCES `data` (`data_id`);

ALTER TABLE `data_qualifications`
  ADD CONSTRAINT `data_qualifications_ibfk_1` FOREIGN KEY (`dq_qualification`) REFERENCES `qualifications` (`qua_id`),
  ADD CONSTRAINT `data_qualifications_ibfk_2` FOREIGN KEY (`dq_data`) REFERENCES `data` (`data_id`);

ALTER TABLE `login_session`
  ADD CONSTRAINT `login_session_ibfk_1` FOREIGN KEY (`ls_account`) REFERENCES `admin` (`adm_account`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `qualifications`
  ADD CONSTRAINT `qualifications_ibfk_1` FOREIGN KEY (`qua_category`) REFERENCES `qualification_category` (`qc_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
