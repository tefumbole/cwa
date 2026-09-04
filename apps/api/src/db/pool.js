import mysql from 'mysql2/promise';

let pool;

function assertIsolatedDatabase() {
  const name = process.env.DB_NAME || '';
  if (/beyondworld|beyondtech/i.test(name)) {
    throw new Error(
      `CWACAM must not use a BeyondTechWorld database (DB_NAME=${name}). ` +
        'Set DB_NAME to cwacam (local) or the dedicated Hostinger CWACAM database.'
    );
  }
}

export function getPool() {
  if (!pool) {
    assertIsolatedDatabase();
    pool = mysql.createPool({
      host: process.env.DB_HOST || 'localhost',
      port: Number(process.env.DB_PORT || 3306),
      user: process.env.DB_USER || 'root',
      password: process.env.DB_PASSWORD || '',
      database: process.env.DB_NAME,
      waitForConnections: true,
      connectionLimit: 10,
      enableKeepAlive: true,
    });
  }
  return pool;
}

export async function checkDatabaseConnection() {
  const connection = await getPool().getConnection();
  try {
    await connection.ping();
    console.log('[MySQL] Connection OK:', process.env.DB_NAME);
  } finally {
    connection.release();
  }
}
