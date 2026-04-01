-- Create child tables
CREATE TABLE IF NOT EXISTS `request_investments` (
  `request_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `budget_planned` tinyint(1) NOT NULL DEFAULT 0,
  `objective` text DEFAULT NULL,
  `start_date_duration` text DEFAULT NULL,
  `amount_ht` decimal(15,2) NOT NULL,
  PRIMARY KEY (`request_id`),
  CONSTRAINT `fk_inv_req` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `request_vacations` (
  `request_id` int(11) NOT NULL,
  `leave_type` varchar(100) NOT NULL,
  `duration_days` decimal(5,1) NOT NULL,
  `dates_period` text NOT NULL,
  `comment` text DEFAULT NULL,
  PRIMARY KEY (`request_id`),
  CONSTRAINT `fk_vac_req` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `request_expenses` (
  `request_id` int(11) NOT NULL,
  `expense_category` varchar(100) NOT NULL,
  `expense_date` text NOT NULL,
  `description` text DEFAULT NULL,
  `amount_ttc` decimal(15,2) NOT NULL,
  PRIMARY KEY (`request_id`),
  CONSTRAINT `fk_exp_req` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migrate data
INSERT IGNORE INTO request_investments (request_id, title, budget_planned, objective, start_date_duration, amount_ht)
SELECT id, type, budget_planned, objective, start_date_duration, amount
FROM requests WHERE workflow_type = 'investment';

INSERT IGNORE INTO request_vacations (request_id, leave_type, duration_days, dates_period, comment)
SELECT id, type, amount, start_date_duration, objective
FROM requests WHERE workflow_type = 'vacation';

INSERT IGNORE INTO request_expenses (request_id, expense_category, expense_date, description, amount_ttc)
SELECT id, type, start_date_duration, objective, amount
FROM requests WHERE workflow_type = 'expense';

-- Drop old columns safely
SET @exist := (SELECT count(*) FROM information_schema.columns WHERE table_schema='flowdb' AND table_name='requests' AND column_name='type');
SET @s = IF(@exist > 0, 'ALTER TABLE `requests` DROP COLUMN `type`, DROP COLUMN `budget_planned`, DROP COLUMN `objective`, DROP COLUMN `start_date_duration`, DROP COLUMN `amount`', 'SELECT 1');
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Create View v_requests to keep backward compatibility for SELECT queries
DROP VIEW IF EXISTS v_requests;
CREATE VIEW v_requests AS
SELECT r.*,
COALESCE(inv.title, vac.leave_type, exp.expense_category) AS type,
COALESCE(inv.amount_ht, vac.duration_days, exp.amount_ttc) AS amount,
COALESCE(inv.objective, vac.comment, exp.description) AS objective,
COALESCE(inv.start_date_duration, vac.dates_period, exp.expense_date) AS start_date_duration,
COALESCE(inv.budget_planned, 0) AS budget_planned
FROM requests r
LEFT JOIN request_investments inv ON r.id = inv.request_id
LEFT JOIN request_vacations vac ON r.id = vac.request_id
LEFT JOIN request_expenses exp ON r.id = exp.request_id;
