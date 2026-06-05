import { randomUUID } from 'node:crypto';

export async function seedSystemSettings(pool) {
  const [rows] = await pool.query('SELECT id FROM system_settings LIMIT 1');
  if (rows.length) {
    console.log('system_settings: already seeded');
    return;
  }

  await pool.query(
    `INSERT INTO system_settings (
      id, developed_by, copyright_text, price_per_share,
      total_shares_available, total_sold_admin_override, currency
    ) VALUES (?, ?, ?, ?, ?, ?, ?)`,
    [
      randomUUID(),
      'Alpha Bridge Technologies Ltd',
      '© Alpha Bridge Technologies Ltd',
      1000,
      100,
      0,
      'USD',
    ]
  );
  console.log('system_settings: default row created');
}
