/**
 * Idempotent ALTER patches for databases created from an older schema.sql.
 */
export const SCHEMA_PATCHES = [
  'ALTER TABLE profiles ADD COLUMN status VARCHAR(50) DEFAULT \'active\'',
  'ALTER TABLE shareholders MODIFY COLUMN email VARCHAR(255) NULL',
  'ALTER TABLE shareholders MODIFY COLUMN name VARCHAR(255) NULL',
  'ALTER TABLE shareholders ADD COLUMN full_name VARCHAR(255) NULL',
  'ALTER TABLE shareholders ADD COLUMN phone_number VARCHAR(50) NULL',
  'ALTER TABLE shareholders MODIFY COLUMN phone VARCHAR(50) NULL',
  'ALTER TABLE shareholders ADD COLUMN country_code VARCHAR(10) NULL',
  'ALTER TABLE shareholders ADD COLUMN full_phone_number VARCHAR(50) NULL',
  'ALTER TABLE shareholders ADD COLUMN company_name VARCHAR(255) NULL',
  'ALTER TABLE shareholders ADD COLUMN address TEXT NULL',
  'ALTER TABLE shareholders ADD COLUMN nationality VARCHAR(100) NULL',
  'ALTER TABLE shareholders ADD COLUMN investment_amount DECIMAL(14,2) NULL',
  'ALTER TABLE shareholders ADD COLUMN signature LONGTEXT NULL',
  'ALTER TABLE shareholders ADD COLUMN signature_image_url TEXT NULL',
  'ALTER TABLE shareholders ADD COLUMN agreement_signed_at DATETIME NULL',
  'ALTER TABLE shareholders ADD COLUMN is_guest TINYINT(1) DEFAULT 1',
  'ALTER TABLE shareholders ADD COLUMN user_id CHAR(36) NULL',
  'ALTER TABLE shareholders ADD COLUMN submitted_at DATETIME NULL',
  'ALTER TABLE shareholders ADD COLUMN approved_at DATETIME NULL',
  'ALTER TABLE shareholders ADD COLUMN approved_by CHAR(36) NULL',
  'ALTER TABLE shareholders ADD COLUMN rejection_reason TEXT NULL',
  'ALTER TABLE shareholders ADD COLUMN admin_notes TEXT NULL',
  'ALTER TABLE whatsapp_message_logs ADD COLUMN recipient_phone VARCHAR(50) NULL',
  'ALTER TABLE whatsapp_message_logs ADD COLUMN related_registration_id CHAR(36) NULL',
  'ALTER TABLE whatsapp_message_logs ADD COLUMN retry_count INT DEFAULT 0',
  'ALTER TABLE whatsapp_message_logs ADD COLUMN sent_at DATETIME NULL',
  'ALTER TABLE tasks ADD COLUMN category_id CHAR(36) NULL',
  'ALTER TABLE task_assignments ADD COLUMN last_update_at DATETIME NULL',
  'ALTER TABLE task_assignments ADD COLUMN declined_at DATETIME NULL',
  'ALTER TABLE task_assignments ADD COLUMN invite_token CHAR(36) NULL',
  'ALTER TABLE tasks ADD COLUMN notification_template LONGTEXT NULL',
  'ALTER TABLE tasks ADD COLUMN schedules_json JSON NULL',
  'ALTER TABLE tasks ADD COLUMN is_scheduled TINYINT(1) DEFAULT 0',
  'ALTER TABLE tasks ADD COLUMN color VARCHAR(20) DEFAULT NULL',
  'ALTER TABLE tasks ADD COLUMN start_time VARCHAR(10) DEFAULT NULL',
  'ALTER TABLE tasks ADD COLUMN deadline_time VARCHAR(10) DEFAULT NULL',
  'ALTER TABLE task_attachments ADD COLUMN attachment_type VARCHAR(50) DEFAULT NULL',
  'ALTER TABLE applications ADD COLUMN status_changed_at DATETIME NULL',
  'ALTER TABLE applications ADD COLUMN status_changed_by CHAR(36) NULL',
  'ALTER TABLE shareholders ADD COLUMN deleted_at DATETIME NULL',
  'ALTER TABLE courses ADD COLUMN sort_order INT NOT NULL DEFAULT 0',
  'ALTER TABLE courses ADD COLUMN category VARCHAR(100) DEFAULT NULL',
  'ALTER TABLE events ADD COLUMN meals_json JSON DEFAULT NULL',
  'ALTER TABLE events ADD COLUMN specify_meals TINYINT(1) DEFAULT 0',
  'ALTER TABLE system_settings ADD COLUMN application_name VARCHAR(255) DEFAULT NULL',
  'ALTER TABLE system_settings ADD COLUMN pdf_header_text TEXT DEFAULT NULL',
  'ALTER TABLE system_settings ADD COLUMN pdf_footer_text TEXT DEFAULT NULL',
  'ALTER TABLE system_settings ADD COLUMN pdf_header_url TEXT DEFAULT NULL',
  'ALTER TABLE system_settings ADD COLUMN pdf_header_file_path VARCHAR(255) DEFAULT NULL',
  'ALTER TABLE system_settings ADD COLUMN pdf_footer_url TEXT DEFAULT NULL',
  'ALTER TABLE system_settings ADD COLUMN pdf_footer_file_path VARCHAR(255) DEFAULT NULL',
  'ALTER TABLE shareholders ADD COLUMN agreement_pdf_url TEXT NULL',
  'ALTER TABLE shareholders ADD COLUMN agreement_pdf_path TEXT NULL',
  'ALTER TABLE shareholders ADD COLUMN pdf_generated_at DATETIME NULL',
  'ALTER TABLE courses ADD COLUMN curriculum_json JSON DEFAULT NULL',
  'ALTER TABLE courses ADD COLUMN delivery_mode VARCHAR(255) DEFAULT NULL',
  'ALTER TABLE courses ADD COLUMN icon VARCHAR(50) DEFAULT NULL',
  'ALTER TABLE courses ADD COLUMN color VARCHAR(20) DEFAULT NULL',
  'ALTER TABLE system_settings ADD COLUMN license_agreement_json JSON DEFAULT NULL',
  'ALTER TABLE users ADD COLUMN username VARCHAR(100) NULL',
  'ALTER TABLE profiles ADD COLUMN username VARCHAR(100) NULL',
  'ALTER TABLE otp_sessions ADD COLUMN purpose VARCHAR(50) DEFAULT \'login\'',
  'ALTER TABLE event_meals ADD COLUMN image_url TEXT NULL',
  'ALTER TABLE registrations ADD COLUMN company_name VARCHAR(255) NULL',
  'ALTER TABLE registrations ADD COLUMN course_ids JSON NULL',
  'ALTER TABLE registrations ADD COLUMN total_price DECIMAL(14,2) NULL',
  'ALTER TABLE registrations ADD COLUMN status VARCHAR(50) DEFAULT \'pending\'',
  'ALTER TABLE registrations ADD COLUMN payment_id VARCHAR(255) NULL',
  'ALTER TABLE registrations ADD COLUMN payment_date DATETIME NULL',
];

export const CREATE_STATEMENTS = [
  `CREATE TABLE IF NOT EXISTS pending_registrations (
    id CHAR(36) NOT NULL PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'user',
    otp VARCHAR(10) NOT NULL,
    expires_at DATETIME NOT NULL,
    invite_token VARCHAR(36) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pending_reg_email (email),
    INDEX idx_pending_reg_phone (phone)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`,
  `CREATE TABLE IF NOT EXISTS task_notification_queue (
    id CHAR(36) NOT NULL PRIMARY KEY,
    task_id CHAR(36) NOT NULL,
    assignment_id CHAR(36) NOT NULL,
    scheduled_at DATETIME NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    sent_at DATETIME DEFAULT NULL,
    last_error TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_task_notif_sched (scheduled_at, status),
    INDEX idx_task_notif_assignment (assignment_id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`,
  `CREATE TABLE IF NOT EXISTS task_cc (
    id CHAR(36) NOT NULL PRIMARY KEY,
    task_id CHAR(36) NOT NULL,
    user_id CHAR(36) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_task_cc (task_id, user_id),
    INDEX idx_task_cc_task (task_id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`,
  `CREATE TABLE IF NOT EXISTS application_status_history (
    id CHAR(36) NOT NULL PRIMARY KEY DEFAULT (UUID()),
    application_id CHAR(36) NOT NULL,
    old_status VARCHAR(50) DEFAULT NULL,
    new_status VARCHAR(50) DEFAULT NULL,
    reason TEXT DEFAULT NULL,
    changed_by CHAR(36) DEFAULT NULL,
    changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_app_status_hist_app (application_id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`,
  `CREATE TABLE IF NOT EXISTS webhook_settings (
    id CHAR(36) NOT NULL PRIMARY KEY DEFAULT (UUID()),
    user_id CHAR(36) NOT NULL,
    webhook_url TEXT NOT NULL,
    triggers JSON DEFAULT NULL,
    enabled TINYINT(1) DEFAULT 1,
    secret_key VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_webhook_user (user_id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`,
  `CREATE TABLE IF NOT EXISTS event_meals (
    id CHAR(36) NOT NULL PRIMARY KEY DEFAULT (UUID()),
    name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    category VARCHAR(100) DEFAULT 'General',
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_event_meals_active (is_active)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`,
  `CREATE TABLE IF NOT EXISTS announcement_categories (
    id CHAR(36) NOT NULL PRIMARY KEY DEFAULT (UUID()),
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_ann_category_slug (slug)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`,
  `CREATE TABLE IF NOT EXISTS certificates (
    id CHAR(36) NOT NULL PRIMARY KEY DEFAULT (UUID()),
    certificate_number VARCHAR(100) NOT NULL,
    registration_id CHAR(36) DEFAULT NULL,
    student_name VARCHAR(255) NOT NULL,
    course_name VARCHAR(255) NOT NULL,
    completion_date DATETIME DEFAULT NULL,
    status VARCHAR(50) DEFAULT 'active',
    revoked_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_certificates_reg (registration_id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`,
  `CREATE TABLE IF NOT EXISTS invoices (
    id CHAR(36) NOT NULL PRIMARY KEY DEFAULT (UUID()),
    invoice_number VARCHAR(100) NOT NULL,
    registration_id CHAR(36) DEFAULT NULL,
    client_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) DEFAULT NULL,
    courses_json JSON DEFAULT NULL,
    subtotal DECIMAL(14,2) DEFAULT 0,
    tax DECIMAL(14,2) DEFAULT 0,
    total DECIMAL(14,2) DEFAULT 0,
    payment_method VARCHAR(100) DEFAULT NULL,
    payment_status VARCHAR(50) DEFAULT 'pending',
    payment_date DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_invoices_reg (registration_id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`,
  `CREATE TABLE IF NOT EXISTS student_progress (
    id CHAR(36) NOT NULL PRIMARY KEY DEFAULT (UUID()),
    registration_id CHAR(36) NOT NULL,
    course_id CHAR(36) NOT NULL,
    progress_percentage DECIMAL(5,2) DEFAULT 0,
    status VARCHAR(50) DEFAULT 'not_started',
    start_date DATETIME DEFAULT NULL,
    completion_date DATETIME DEFAULT NULL,
    last_updated DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_progress_reg (registration_id),
    INDEX idx_progress_course (course_id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`,
  `CREATE TABLE IF NOT EXISTS course_feedback (
    id CHAR(36) NOT NULL PRIMARY KEY DEFAULT (UUID()),
    registration_id CHAR(36) DEFAULT NULL,
    course_id CHAR(36) DEFAULT NULL,
    student_name VARCHAR(255) DEFAULT NULL,
    rating INT DEFAULT 5,
    feedback_text TEXT DEFAULT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_feedback_course (course_id),
    INDEX idx_feedback_reg (registration_id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`,
];

export const DATA_PATCHES = [];

export async function applySchemaPatches(pool) {
  for (const sql of SCHEMA_PATCHES) {
    try {
      await pool.query(sql);
      const col = sql.match(/ADD COLUMN `?(\w+)`?|MODIFY COLUMN `?(\w+)`?/i);
      console.log('Patched:', col?.[1] || col?.[2] || sql.slice(0, 40));
    } catch (err) {
      if (err.code === 'ER_DUP_FIELDNAME') continue;
      if (err.code === 'ER_BAD_FIELD_ERROR' && sql.includes('MODIFY')) continue;
      console.warn('Patch skipped:', err.message);
    }
  }

  for (const sql of DATA_PATCHES || []) {
    try {
      const [result] = await pool.query(sql);
      if (result?.affectedRows) {
        console.log('Data patch applied:', result.affectedRows, 'row(s)');
      }
    } catch (err) {
      console.warn('Data patch skipped:', err.message);
    }
  }
}
