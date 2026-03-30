-- =============================================
-- QRCodex Migration: Single Time In/Out → AM/PM Time In/Out
-- Run this SQL if you already have the old event_schedules table
-- =============================================

-- Step 1: Rename existing columns to AM columns
ALTER TABLE `event_schedules` CHANGE `time_in_start` `am_time_in_start` time NOT NULL;
ALTER TABLE `event_schedules` CHANGE `time_in_end` `am_time_in_end` time NOT NULL;
ALTER TABLE `event_schedules` CHANGE `time_out_start` `am_time_out_start` time NOT NULL;
ALTER TABLE `event_schedules` CHANGE `time_out_end` `am_time_out_end` time NOT NULL;

-- Step 2: Add PM columns
ALTER TABLE `event_schedules` ADD `pm_time_in_start` time NOT NULL DEFAULT '13:00:00' AFTER `am_time_out_end`;
ALTER TABLE `event_schedules` ADD `pm_time_in_end` time NOT NULL DEFAULT '14:00:00' AFTER `pm_time_in_start`;
ALTER TABLE `event_schedules` ADD `pm_time_out_start` time NOT NULL DEFAULT '16:00:00' AFTER `pm_time_in_end`;
ALTER TABLE `event_schedules` ADD `pm_time_out_end` time NOT NULL DEFAULT '18:00:00' AFTER `pm_time_out_start`;

-- Step 3: Add period column to attendance table
ALTER TABLE `attendance` ADD `period` enum('am','pm') DEFAULT NULL AFTER `status`;