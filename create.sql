SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';


-- -----------------------------------------------------
-- Table `later_job_monitor`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `later_job_monitor` ;

CREATE  TABLE IF NOT EXISTS `later_job_monitor` (
  `monitor_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `monitor_dte` DATETIME NOT NULL ,
  `worker_max_time` INT(10) UNSIGNED NULL DEFAULT NULL ,
  `worker_min_time` INT(10) UNSIGNED NULL DEFAULT NULL ,
  `worker_mean_time` INT(10) UNSIGNED NULL DEFAULT NULL ,
  `worker_mean_throughput` INT(10) UNSIGNED NULL DEFAULT NULL ,
  `worker_max_throughput` INT(10) UNSIGNED NULL DEFAULT NULL ,
  `worker_mean_utilization` DOUBLE NULL DEFAULT NULL ,
  `queue_no_waiting_jobs` INT(10) UNSIGNED NULL DEFAULT NULL ,
  `queue_no_failed_jobs` INT(10) UNSIGNED NULL DEFAULT NULL ,
  `queue_no_error_jobs` INT(10) UNSIGNED NULL DEFAULT NULL ,
  `queue_no_completed_jobs` INT(10) UNSIGNED NULL DEFAULT NULL ,
  `queue_no_processing_jobs` INT(10) UNSIGNED NULL DEFAULT NULL ,
  `queue_mean_service_time` INT(10) UNSIGNED NULL DEFAULT NULL ,
  `queue_min_service_time` INT(10) UNSIGNED NULL DEFAULT NULL ,
  `queue_max_service_time` INT(10) UNSIGNED NULL DEFAULT NULL ,
  `monitor_complete` TINYINT(1) NOT NULL DEFAULT '0' ,
  PRIMARY KEY (`monitor_id`) ,
  UNIQUE INDEX `UNIQ_B85F1F3F43827BBD` (`monitor_dte` ASC) )
ENGINE = InnoDB
AUTO_INCREMENT = 5
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `later_job_queue`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `later_job_queue` ;

CREATE  TABLE IF NOT EXISTS `later_job_queue` (
  `job_id` VARCHAR(36) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL ,
  `state_id` INT(10) UNSIGNED NOT NULL ,
  `dte_add` DATETIME NOT NULL ,
  `retry_count` INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
  `retry_last` DATETIME NULL DEFAULT NULL ,
  `job_data` LONGTEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL COMMENT '(DC2Type:object)' ,
  `handle` VARCHAR(36) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL ,
  `lock_timeout` DATETIME NULL DEFAULT NULL ,
  PRIMARY KEY (`job_id`) ,
  INDEX `IDX_7D8F6CFF918020D9` (`handle` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `later_job_transition`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `later_job_transition` ;

CREATE  TABLE IF NOT EXISTS `later_job_transition` (
  `transition_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `worker_id` VARCHAR(36) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL ,
  `job_id` VARCHAR(36) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL ,
  `state_id` INT(10) UNSIGNED NOT NULL ,
  `process_handle` VARCHAR(36) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL ,
  `dte_occured` DATETIME NOT NULL ,
  `transition_msg` VARCHAR(200) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL ,
  PRIMARY KEY (`transition_id`) ,
  INDEX `IDX_C40C4B866B20BA36` (`worker_id` ASC) ,
  INDEX `IDX_C40C4B86BE04EA9` (`job_id` ASC) )
ENGINE = InnoDB
AUTO_INCREMENT = 101
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
