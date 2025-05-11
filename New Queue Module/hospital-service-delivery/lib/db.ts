// lib/db.ts
import mysql from 'mysql2/promise';

let pool: mysql.Pool | null = null;

// Ensure this function is EXPORTED
export function getPool(): mysql.Pool { // Return type mysql.Pool
  if (!pool) {
    // Ensure your environment variables are loaded correctly here
    // If process.env values are undefined, createPool will fail or use defaults
    if (!process.env.MYSQL_HOST || !process.env.MYSQL_USER || !process.env.MYSQL_DATABASE) {
        console.error("FATAL: MySQL environment variables (MYSQL_HOST, MYSQL_USER, MYSQL_DATABASE) are not defined.");
        // In a real app, you might throw an error or handle this more gracefully
        // For now, this will cause createPool to likely fail with a clear message if it proceeds
    }

    pool = mysql.createPool({
      host: process.env.MYSQL_HOST,
      user: process.env.MYSQL_USER,
      password: process.env.MYSQL_PASSWORD, // Password can be empty if user has no password
      database: process.env.MYSQL_DATABASE,
      waitForConnections: true,
      connectionLimit: 10, // Or your preferred limit
      queueLimit: 0,
      namedPlaceholders: true, // It's good practice
      charset: 'utf8mb4',     // Recommended for modern character sets
    });

    // Optional: Test the pool immediately (good for debugging connection issues)
    // pool.getConnection()
    //   .then(conn => {
    //     console.log("Successfully connected to MySQL pool.");
    //     conn.release();
    //   })
    //   .catch(err => {
    //     console.error("FATAL: Failed to connect to MySQL pool:", err);
    //     // Consider exiting or preventing app start if DB connection is critical
    //     // process.exit(1);
    //   });
  }
  return pool;
}

// This query function uses the pool to get a connection and release it
export async function query(sql: string, params?: any[]) {
  const connection = await getPool().getConnection();
  try {
    const [results] = await connection.execute(sql, params);
    return results;
  } finally {
    connection.release();
  }
}

// This execute function is similar to query but perhaps more generically named
// You might only need one of them (query or execute) based on your preference
export async function execute<T>(sql: string, params?: any[]): Promise<T> {
  const connection = await getPool().getConnection();
  try {
    const [results] = await connection.execute(sql, params);
    return results as T;
  } finally {
    connection.release();
  }
}